<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

use Joomla\CMS\Session\Session;

class MUEModelUser extends JModelLegacy
{
	function getGroups() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$aid = $user->getAuthorisedViewLevels();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_ugroups');
		$query->where('published = 1');
		$query->where('access IN ('.implode(",",$aid).')');
		$query->order('ordering');
		$db->setQuery($query);
		$ugroups = $db->loadObjectList();
		return $ugroups;
	}
	
	function saveEmail($newemail) {
		$db = JFactory::getDBO();
		$user=JFactory::getUser();
		$cfg = MUEHelper::getConfig();
		$date = new JDate('now');
		$oldemail = $user->email;
		
		//Update Joomla User Info
		$udata['email']=$newemail;
		$udata['block']=$user->block;

		if (!$user->bind($udata)) {
			$this->setError($user->getError());
			return false;
		};
		if (!$user->save(true)) {
			$this->setError($user->getError());
			return false;
		}
		
		
		$usernotes = $date->toSql(true)." User Email Changed from ".$oldemail." to ".$user->email."\r\n";
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'" WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->execute()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Active Campaign Integration
		$aclists = $this->getUserFields(1,false,false,false,"aclist");
		if (count($aclists) > 0) {
			// Load up AC connector
			require_once( JPATH_ROOT . '/components/com_mue/lib/activecampaign.php' );
			$acClient = new ActiveCampaign( $cfg->ackey, $cfg->acurl );

			// get contact
			$contact = $acClient->getContact($oldemail);

			if ($contact) {
				$acClient->updateContactEmail($contact['id'],$newemail);
			}
		}

		//CM Update
		$cmlists = $this->getUserFields(1,false,false,false,"cmlist");
		foreach ($cmlists as $cmlist) {
			include_once 'components/com_mue/lib/campaignmonitor.php';
			$cmuf=$cmlist->uf_sname;
			$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
			$cmdata = array('Name'=>$user->name, 'EmailAddress'=>$user->email, 'Resubscribe'=>'true');
			$cmd=print_r($cmdata,true);
			if ($cm->getSubscriberDetails($cmlist->uf_default,$oldemail)) {
				$cmresult = $cm->updateSubscriber($cmlist->uf_default,$oldemail,$cmdata);
				if ($cmresult) { $usernotes .= $date->toSql(true)." EMail Address Updated on Campaign Monitor List #".$cmlist->uf_default.' '.$cmd."\r\n"; }
				else { $usernotes .= $date->toSql(true)." Could not update EMail Address on Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
			}
			
		}
	
		//Update usernotes
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->execute()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
	
	
		return true;
	}
	
	function saveGroup($groupid) {
		$db = JFactory::getDBO();
		$user=JFactory::getUser();
		$cfg = MUEHelper::getConfig();
		$date = new JDate('now');
		$usernotes = $date->toSql(true)." User Group Changed\r\n";
		$qud = 'UPDATE #__mue_usergroup SET userg_group = '.$groupid.', userg_update = "'.$date->toSql(true).'" WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->execute()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		$ginfo=MUEHelper::getGroupInfo($groupid);

		// Active Campaign Integration
		$aclists = $this->getUserFields(1,false,false,false,"aclist");
		if (count($aclists) > 0) {
			// Load up AC connector
			require_once( JPATH_ROOT . '/components/com_mue/lib/activecampaign.php' );
			$acClient = new ActiveCampaign( $cfg->ackey, $cfg->acurl );

			//get first list
			$aclistFirst = $aclists[0];

			// get contact
			$contact = $acClient->getContact($user->email);

			// update group fields
			if ($contact) {
				if ( $aclistFirst->params->acvars) {
					// User Fields
					foreach ( $aclistFirst->params->acvars as $acFieldId => $mueVar ) {
						if ( $mueVar == "user_group" ) {
							$acClient->updateContactUserGroup($contact['id'],$acFieldId,$ginfo->ug_name);
						}
					}
				}
			}
		}
		
		//CM Update
		$cmlists = $this->getUserFields($groupid,false,false,false,"cmlist");
		foreach ($cmlists as $cmlist) {
			include_once 'components/com_mue/lib/campaignmonitor.php';
			$cmuf=$cmlist->uf_sname;
			$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
			$cmdata = array('Name'=>$user->name, 'EmailAddress'=>$user->email, 'Resubscribe'=>'true');
			$customfields = array();
			if ($cmlist->params->cmfields) {
				$othervars=$cmlist->params->cmfields;
				foreach ($othervars as $cmf=>$mue) {
					if ($mue == "user_group") {
						$newcmf=array();
						$newcmf['Key']=$cmf;
						$newcmf['Value'] = $ginfo->ug_name;
						$customfields[]=$newcmf;
					}
				}
				$cmdata['CustomFields']=$customfields;
				$cmd=print_r($cmdata,true);
				if ($cm->getSubscriberDetails($cmlist->uf_default,$user->email)) {
					$cmresult = $cm->updateSubscriber($cmlist->uf_default,$user->email,$cmdata);
					if ($cmresult) { $usernotes .= $date->toSql(true)." User Group Updated on Campaign Monitor List #".$cmlist->uf_default.' '.$cmd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update User Group on Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
				} 
			}
		}
		
		//Update usernotes
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->execute()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		
		return true;
	}
	
	function getUserCERecords() {
		$db = JFactory::getDBO();
		$user=JFactory::getUser();
		
		$qci=$db->getQuery(true);
		$qci->select('*');
		$qci->from('#__mcme_sessions as s');
		$qci->join('RIGHT','#__mcme_courses AS c ON s.sess_course = c.course_id');
		$qci->join('LEFT','#__mcme_certsissued AS ci ON ci.ci_sess = s.sess_id');
		$qci->where('s.sess_user = '.$user->id);
		$qci->where('c.published >= 1');
		$qci->order('s.sess_start DESC');
		$db->setQuery($qci);
		$items = $db->loadObjectList();

		// Check if certificate available
		foreach ($items as &$item) {
			if (!$item->course_altcert) {
				$usergroupid = MUEHelper::getUserGroup($user->id)->userGroupID;
				$qe = $db->getQuery(true);
				$qe->select('cg_group');
				$qe->from('#__mcme_coursegroups');
				$qe->where('cg_course = '.$item->course_id);
				$db->setQuery($qe);
				$coursegroups=$db->loadColumn();
				if (in_array($usergroupid,$coursegroups)) {
					$item->givecert = true;
				} else {
					$item->givecert = false;
				}

			} else {
				$item->givecert = true;
			}

            // Check Credits
            if ($item->ci_credits == 0) $item->ci_credits = $item->course_credits;
		}

		return $items;
	}
	
	function getUserFields($group,$showhidden=false,$all=false,$changable=false,$type="") {
		$db = JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_uguf as g';
		$qd.= ' RIGHT JOIN #__mue_ufields as f ON g.uguf_field = f.uf_id';
		$qd.= ' WHERE f.published = 1 && g.uguf_group='.$group;
		if (!$showhidden) $qd.=" && f.uf_hidden = 0";
		if ($changable) $qd.=" && f.uf_change = 1";
		if ($type) $qd .= ' && f.uf_type = "'.$type.'"';
		$qd .= ' && f.uf_type != "captcha"';
		$qd .=" && f.uf_profile = 1";
		$qd.= ' ORDER BY f.ordering';
		$db->setQuery( $qd ); 
		$ufields = $db->loadObjectList();
		foreach ($ufields as &$u) {
			$registry = new JRegistry();
			$registry->loadString($u->params);
			$u->params = $registry->toObject();
			
			if ($all) {
				switch ($u->uf_type) {
					case 'multi':
					case 'dropdown':
					case 'mlist':
					case 'mcbox':
						$qo = 'SELECT opt_id as value, opt_text as text FROM #__mue_ufields_opts WHERE opt_field='.$u->uf_id.' && published > 0 ORDER BY ordering';
						$this->_db->setQuery($qo);
						$u->options = $this->_db->loadObjectList();
						break;
				}
			}
		}
		return $ufields;
	}
	
	public function save()
	{
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$input = JFactory::getApplication()->input;
		// Initialise variables;
		$user=JFactory::getUser();
		$data = $input->get('jform', [], 'post', 'array');
		$isNew = true;
		$db	= $this->getDbo();
		$ugroup = $data['userGroupID'];
		$ginfo = MUEHelper::getGroupInfo($ugroup);
		$uginfo = $this->getUserGroupInfo($user->id);
		$date = new JDate('now');
		$usernotes = $date->toSql(true)." User Profile Updated"."\r\n";
		$cfg = MUEHelper::getConfig();
		$substatus=MUEHelper::getActiveSub();
		$hasTZField = false;
		
		JPluginHelper::importPlugin('user');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$item = new stdClass();
			$fids = array();
			$optfs = array();
			$moptfs = array();
			$cmlists = array();
            $aclists = array();
			$flist = $this->getUserFields($ugroup,false,false,false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'timezone') {
					$hasTZField = true;
					$timezone=$db->escape($data[$fieldname]);
				} else if ($d->uf_type == 'aclist') {
					$aclists[]=$d;
				} else if ($d->uf_type == 'cmlist') {
					$cmlists[]=$d;
				} else if ($d->uf_type=='birthday') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.$fday;
				} else if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
				} else if ($d->uf_type=='cbox') { 
					$item->$fieldname = ($data[$fieldname]=='on') ? "1" : "0";
				} else $item->$fieldname = $data[$fieldname];
				if ($d->uf_type=="multi" || $d->uf_type=="dropdown") $optfs[]=$d->uf_sname;
				if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") $moptfs[]=$d->uf_sname;
				$fids[]=$d->uf_id;
			}
			$item->site_url = JURI::base();
			$item->user_group = $ginfo->ug_name;
			
			$odsql = "SELECT * FROM #__mue_ufields_opts";
			$db->setQuery($odsql);
			$optionsdata = array();
			$optres = $db->loadObjectList();
			foreach ($optres as $o) {
				$optionsdata[$o->opt_id]=$o->opt_text;
			}

			// Active Campaign Integration
			if (count($aclists) > 0) {
				// Load up AC connector
				require_once(JPATH_ROOT.'/components/com_mue/lib/activecampaign.php');
				$acClient = new ActiveCampaign($cfg->ackey,$cfg->acurl);

				//get first list
				$aclistFirst = $aclists[0];

				//setup field data
				$fieldData = [];
				if ( $aclistFirst->params->acvars) {
					// User Fields
					foreach ($aclistFirst->params->acvars as $acFieldId => $mueVar) {
						if ($mueVar) {
							$fieldVal = '';
							if ( in_array( $mueVar, $optfs ) ) {
								$fieldVal = $optionsdata[ $item->$mueVar ];
							} else if ( in_array( $mueVar, $moptfs ) ) {
								$fv = '';
								foreach ( explode( " ", $item->$mueVar ) as $mfo ) {
									$fv .= $optionsdata[ $mfo ] . " ";
								}
								$fieldVal= $fv;
							} else {
								$fieldVal = $item->$mueVar;
							}
							$fieldDataEntry = [];
							$fieldDataEntry['field'] = $acFieldId;
							$fieldDataEntry['value'] = $fieldVal;
							$fieldData[] = $fieldDataEntry;
						}
					}
					// Subscription Status
					if ($aclistFirst->params->acsubstatus) {
						$fieldVal = '';
						if (!$substatus) { $fieldVal=$aclistFirst->params->acsubtextno; }
						else { $fieldVal=$aclistFirst->params->acsubtextyes; }
						$fieldDataEntry = [];
						$fieldDataEntry['field'] = $aclistFirst->params->acsubstatus;
						$fieldDataEntry['value'] = $fieldVal;
						$fieldData[] = $fieldDataEntry;
					}
				}

				// sync contact
				$acClient->syncContact($item->email,$item->fname, $item->lname,$fieldData);

				// get contact
				$contact = $acClient->getContact($item->email);

				// pause
				sleep(1);

				if ($contact) {
					// Gather List Ids and set status
					$linkedLists = [];
					foreach ( $aclists as $aclist ) {
						$subtolist = true;
						if ( ! $data[ $aclist->uf_sname ] ) {
							$subtolist = false;
						}
						$listsForField = json_decode( $aclist->uf_default, true );
						foreach ( $listsForField as $lf ) {
							$linkedLists[ $lf ] = $subtolist;
						}
					}

					// list update
					if ( count( $linkedLists ) ) {
						foreach ( $linkedLists as $lId => $lStatus ) {
							$acClient->changeListSub( $lId, $contact['id'], $lStatus );
						}
					}
				}
			}

			//Campaign Monitor Integration
			foreach ($cmlists as $cmlist) {
				include_once 'components/com_mue/lib/campaignmonitor.php';
				if ($data[$cmlist->uf_sname]) {
					$cmuf=$cmlist->uf_sname;
					$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
					$cmdata = array('Name'=>$item->fname.' '.$item->lname, 'EmailAddress'=>$item->email, 'Resubscribe'=>'true');
					$customfields = array();
					if ($cmlist->params->cmfields) {
						$othervars=$cmlist->params->cmfields;
						foreach ($othervars as $cmf=>$mue) {
							if ($cmlist->params->cmfieldtypes->$cmf == "MultiSelectMany") {
								if (in_array($mue,$moptfs)) {
									foreach (explode(" ",$item->$mue) as $mfo) {
										$newcmf=array();
										$newcmf['Key']=$cmf;
										$newcmf['Value'] = $optionsdata[$mfo];
										$customfields[]=$newcmf;
									}
								} else {
									$newcmf=array();
									$newcmf['Key']=$cmf;
									$newcmf['Value'] == "";
									$newcmf['Clear']='true';
									$customfields[]=$newcmf;
								}
							} else {
								if ($mue) {
									$newcmf=array();
									$newcmf['Key']=$cmf;
									if (in_array($mue,$optfs)) $newcmf['Value'] = $optionsdata[$item->$mue];
									else if (in_array($mue,$moptfs)) {
										$newcmf['Value'] = "";
										foreach (explode(" ",$item->$mue) as $mfo) {
											$newcmf['Value'] .= $optionsdata[$mfo]." ";
										}
									}
									else $newcmf['Value'] = $item->$mue;
								}
								if (!$mue || $newcmf['Value'] == "") $newcmf['Clear']='true';
								$customfields[]=$newcmf;
							}
						}
					}
					if ($cmlist->params->msgroup->field) {
						$newcmf=array(); 
						$newcmf['Key']=$cmlist->params->msgroup->field; 
						if (!$substatus) { $newcmf['Value']=$cmlist->params->msgroup->reg; }
						else { $newcmf['Value']=$cmlist->params->msgroup->sub; }
						$customfields[]=$newcmf;
					}
					$cmdata['CustomFields']=$customfields;
					$cmdata['ConsentToTrack'] = 'yes';
					$cmd=print_r($cmdata,true);
					if ($cm->getSubscriberDetails($cmlist->uf_default,$item->email)) {
						$cmresult = $cm->updateSubscriber($cmlist->uf_default,$item->email,$cmdata);
						if ($cmresult) { $item->$cmuf=1; $usernotes .= $date->toSql(true)." EMail Subscription Updated on Campaign Monitor List #".$cmlist->uf_default.' '.$cmd."\r\n"; }
						else { $item->$cmuf=1; $usernotes .= $date->toSql(true)." Could not update EMail subscription on Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
					} else {
						$cmresult = $cm->addSubscriber($cmlist->uf_default,$cmdata);
						if ($cmresult) { $item->$cmuf=1; $usernotes .= $date->toSql(true)." EMail Subscribed to Campaign Monitor List #".$cmlist->uf_default.' '.$cmd."\r\n"; }
						else { $item->$cmuf=0; $usernotes .= $date->toSql(true)." Could not subscribe EMail to Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
					}
						
				} else {
					$cmf=$cmlist->uf_sname;
					$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
					$cmresult = $cm->removeSubscriber($cmlist->uf_default,$item->email);
					if ($cmresult) { $item->$cmf=0; $usernotes .= $date->toSql(true)." EMail Unsubscribed from Campaign Monitor List #".$cmlist->uf_default."\r\n"; }
					else { $item->$cmf=0; $usernotes .= $date->toSql(true)." Could not unsubscribe EMail from Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error."\r\n"; }
				}
			}
			
			//User Directory
			$udf=$cfg->on_userdir_field;
			if ($cfg->userdir && $item->$udf) {
				$dudsql = "DELETE FROM #__mue_userdir WHERE ud_user = ".$user->id;
				$db->setQuery($dudsql);
				$db->execute();
				$af=explode(",",$cfg->userdir_mapfields);
				$uf=explode(",",$cfg->userdir_userinfo);
				$sf=explode(",",$cfg->userdir_searchinfo);
				$tf=explode(",",$cfg->userdir_usertags);
				$address="";
				$dbuserinfo="";
				$dbsearchinfo="";
				$dbusertags="";
				foreach ($af as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$address .= $optionsdata[$item->$f]." ";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$address .= $optionsdata[$mfo]." ";
							}
						} else {
							$address .= $item->$f." ";
						}
					}
				}
				foreach ($uf as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$dbuserinfo .= $optionsdata[$item->$f]."<br />";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$dbuserinfo .= $optionsdata[$mfo]."<br />";
							}
						} else {
							$dbuserinfo .= $item->$f."<br />";
						}
					}
				}
				foreach ($sf as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$dbsearchinfo .= $optionsdata[$item->$f]." ";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$dbsearchinfo .= $optionsdata[$mfo]." ";
							}
						} else {
							$dbsearchinfo .= $item->$f." ";
						}
					}
				}
				foreach ($tf as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$dbusertags .= '<span class="uk-badge badge mue-field-'.$f.'">'.$optionsdata[$item->$f]."</span>&nbsp;";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$dbusertags .= '<span class="uk-badge badge mue-field-'.$f.'">'.$optionsdata[$mfo]."</span>&nbsp;";
							}
						} else {
							$dbusertags .= '<span class="uk-badge badge mue-field-'.$f.'">'.$item->$f."</span>&nbsp;";
						}
					}
				}
				if ($dbusertags != "") $dbusertags .= "<br />";
				$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($address).'&key='.$cfg->gm_api_key;
				//$gdata_json = file_get_contents($url);
				$gdata_json = $this->curl_file_get_contents($url);
				$gdata = json_decode($gdata_json);
				if ($gdata->status == 'OK') {
					$udsql = 'INSERT INTO #__mue_userdir (ud_user,ud_lat,ud_lon,ud_userinfo,ud_searchinfo,ud_usertags) VALUES  ("'.$user->id.'","'.$gdata->results[0]->geometry->location->lat.'","'.$gdata->results[0]->geometry->location->lng.'","'.$dbuserinfo.'","'.$dbsearchinfo.'","'.$db->escape($dbusertags).'")';
					$db->setQuery($udsql);
					$db->execute();
					$usernotes .= $date->toSql(true)." Added to/Updated in User Directory\r\n";
				} else {
					$item->$udf = 0;
					$usernotes .= $date->toSql(true)." Could not add to/update User Directory\r\n";
				}
			} else if ($cfg->userdir && !$item->$udf) {
				$dudsql = "DELETE FROM #__mue_userdir WHERE ud_user = ".$user->id;
				$db->setQuery($dudsql);
				$db->execute();
				$usernotes .= $date->toSql(true)." Removed from User Directory\r\n";
			}
				
				
			
			//Update Joomla User Info
			$udata['name']=$item->fname." ".$item->lname;
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;
			$udata['block']=$user->block;

			if ($hasTZField) {
				$userParams = json_decode( $user->params, true );
				$userParams['timezone'] = $timezone;
				$udata['params'] = $userParams;
			}

			if (!$user->bind($udata)) {
				$this->setError($user->getError());
				return false;
			};
			if (!$user->save(true)) {
				$this->setError($user->getError());
				return false;
			}
			
			//remove joomla user info from item
			unset($item->password);
			unset($item->cpassword);
			
			
			//Save ContinuEd Userinfo
			$query	= $db->getQuery(true);
			$query->delete();
			$query->from('#__mue_users');
			$query->where('usr_user = '.$user->id);
			$query->where('usr_field IN ('.implode(",",$fids).')');
			$db->setQuery((string)$query);
			$db->execute();
			
			$flist = $this->getUserFields($ugroup,false,false,true);
			foreach ($flist as $fl) {
				$fieldname = $fl->uf_sname;
				if (!$fl->uf_cms) {

					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$db->escape($item->$fieldname).'")';
					$db->setQuery($qf);
					if (!$db->execute()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
			
			//Update update date
			$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
			$db->setQuery($qud);
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
		
		return true;
	}

	private function curl_file_get_contents($URL){
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);
	
		if ($contents) return $contents;
		else return FALSE;
	}
	
	public function getUserGroupInfo($user) {
		$db		= $this->getDbo();
		$query = 'SELECT * FROM #__mue_usergroup as ug ';
		$query.= 'WHERE ug.userg_user="'.$user.'"';
		$db->setQuery($query);
		return $db->loadObject();
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}
	

}
