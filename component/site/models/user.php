<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 *
 */
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
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
        
        // Bronto Mail Integration
        $brlists = $this->getUserFields(1,false,false,false,"brlist");
        foreach ($brlists as $brlist) {
        	try {
	            // Get contact and status
	            $token = $cfg->brkey;
	            $bronto = new Bronto_Api();
	            $bronto->setToken($token);
	            $bronto->login();
	            $contactObject = $bronto->getContactObject();
	            $oldcontact = $contactObject->createRow();
	            $oldcontact->email = $oldemail;
	            $oldcontact->read();
	            $contactid = $oldcontact->id;

	            $contact = $contactObject->createRow();
	            $contact->id = $contactid;
	            $contact->read();

	            if ($contact->status != 'unsub') {
	                $contact->email = $newemail;

	                // Save
	                $contact->save(true);
	            }

	        } catch (Exception $e) {

	        }
        }
        
		//MC Update
		$mclists = $this->getUserFields(1,false,false,false,"mailchimp");
		foreach ($mclists as $mclist) {
			include_once 'components/com_mue/lib/mailchimp.php';
			if (strstr($mclist->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$mclist->uf_default,2);	}
			else { $mc_key = $cfg->mckey; $mc_list = $mclist->uf_default; }
			$mcf=$mclist->uf_sname;
			$mc = new MailChimpHelper($mc_key,$mc_list);
			$mcdata = array();
			$mcdata['new-email']=$user->email;
			$mcd=print_r($mcdata,true);
			if ($mc->subStatus($oldemail)) {
				$mcresult = $mc->updateUser(array("email"=>$oldemail),$mcdata,false,"html");
				if ($mcresult) { $usernotes .= $date->toSql(true)." Email Address Updated on MailChimp List #".$mclist->uf_default.' '.$mcd."\r\n"; }
				else { $usernotes .= $date->toSql(true)." Could not update Email Address on MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
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
		if (!$db->query()) {
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
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		$ginfo=MUEHelper::getGroupInfo($groupid);

        // Bronto Mail Integration
        $brlists = $this->getUserFields($groupid,false,false,false,"brlist");
        foreach ($brlists as $brlist) {
        	try {
		        // Get contact and status
		        $token  = $cfg->brkey;
		        $bronto = new Bronto_Api();
		        $bronto->setToken( $token );
		        $bronto->login();
		        $contactObject  = $bronto->getContactObject();
		        $contact        = $contactObject->createRow();
		        $contact->email = $user->email;
		        $contact->read();

		        if ( $contact->status != 'unsub' ) {
			        // Update field
			        if ( $brlist->params->brvars ) {
				        $othervars = $brlist->params->brvars;
				        foreach ( $othervars as $brv => $mue ) {
					        if ( $mue == "user_group" ) {
						        $contact->setField( $brv, $ginfo->ug_name );
					        }
				        }
			        }

			        // Save
			        $contact->save( true );
		        }
	        } catch (Exception $e) {

	        }
        }
		
		//MC Update
		$mclists = $this->getUserFields($groupid,false,false,false,"mailchimp");
		foreach ($mclists as $mclist) {
			include_once 'components/com_mue/lib/mailchimp.php';
			if (strstr($mclist->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$mclist->uf_default,2);	}
			else { $mc_key = $cfg->mckey; $mc_list = $mclist->uf_default; }
			$mcf=$mclist->uf_sname;
			$mc = new MailChimpHelper($mc_key,$mc_list);
			$mcdata = array();
			if ($mclist->params->mcvars) {
				$othervars=$mclist->params->mcvars;
				foreach ($othervars as $mcv=>$mue) {
					if ($mue == "user_group") {
						$mcdata[$mcv] = $ginfo->ug_name;
					}
				}
				$mcd=print_r($mcdata,true);
				if ($mc->subStatus($user->email)) {
					$mcresult = $mc->updateUser(array("email"=>$user->email),$mcdata,false,"html");
					if ($mcresult) { $usernotes .= $date->toSql(true)." User Group Updated on MailChimp List #".$mclist->uf_default.' '.$mcd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update User Group on MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
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
		if (!$db->query()) {
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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Initialise variables;
		$user=JFactory::getUser();
		$data		= JRequest::getVar('jform', array(), 'post', 'array'); 
		$dispatcher = JDispatcher::getInstance();
		$isNew = true;
		$db		= $this->getDbo();
		$ugroup = $data['userGroupID'];
		$ginfo=MUEHelper::getGroupInfo($ugroup);
		$uginfo = $this->getUserGroupInfo($user->id);
		$date = new JDate('now');
		$usernotes = $date->toSql(true)." User Profile Updated"."\r\n";
		$cfg = MUEHelper::getConfig();
		$substatus=MUEHelper::getActiveSub();
		
		JPluginHelper::importPlugin('user');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$item = new stdClass();
			$fids = array();
			$optfs = array();
			$moptfs = array();
			$mclists = array();
			$cmlists = array();
            $brlists = array();
			$flist = $this->getUserFields($ugroup,false,false,false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'brlist') {
					$brlists[]=$d;
				} else if ($d->uf_type == 'mailchimp') {
                    $mclists[]=$d;
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

            // Bronto Mail Integration
            foreach ($brlists as $brlist) {
            	try {
	                // Get contact and status
	                $token = $cfg->brkey;
	                $bronto = new Bronto_Api();
	                $bronto->setToken($token);
	                $bronto->login();
	                $contactObject = $bronto->getContactObject();
	                $contact = $contactObject->createRow();
	                $contact->email = $user->email;
	                $contact->read();
					$unsubed=false;

	                // Update Status, but only if we are unsubscribed, transactional, or unconfirmed
	                if ($data[$brlist->uf_sname]) {
	                    if ($contact->status == 'transactional' || $contact->status == 'unconfirmed' || $contact->status == 'unsub') {
	                        if ($contact->status == 'unsub') $unsubed=true;
	                        $contact->status = "onboarding";
	                        $contact->save();
	                    }
	                }

	                // Update fields
	                if ($brlist->params->brvars && $data[$brlist->uf_sname]) {
	                    $othervars=$brlist->params->brvars;
	                    foreach ($othervars as $brv=>$mue) {
	                        if ($mue) {
	                            if ($brlist->params->brfieldtypes->$brv == "checkbox") {
	                                if ($item->$mue == "1") $contact->setField($brv,'true');
	                                else $contact->setField($brv,'false');
	                            } else if (in_array($mue,$optfs)) {
	                                $contact->setField($brv,$optionsdata[$item->$mue]);
	                            }
	                            else if (in_array($mue,$moptfs)) {
	                                $fv = '';
	                                foreach (explode(" ",$item->$mue) as $mfo) {
	                                    $fv .= $optionsdata[$mfo]." ";
	                                }
	                                $contact->setField($brv,$fv);
	                            }
	                            else {
	                                $contact->setField($brv,$item->$mue);
	                            }
	                        }
	                    }
	                }

	                // Update Subscription Info
		            if ($brlist->params->brsubstatus) {
			            // Set Member Status
			            if ( $substatus ) {
				            $contact->setField( $brlist->params->brsubstatus, $brlist->params->brsubtextyes );
			            } else {
				            $contact->setField( $brlist->params->brsubstatus, $brlist->params->brsubtextno );
			            }

			            // Set Member Since
			            if ( $brlist->params->brsubsince && $uginfo->userg_subsince != "0000-00-00" ) {
				            $contact->setField( $brlist->params->brsubsince, $uginfo->userg_subsince );
			            }

			            // Set Member Exp
			            if ( $brlist->params->brsubexp  && $uginfo->userg_subexp != '0000-00-00') {
				            $contact->setField( $brlist->params->brsubexp, $uginfo->userg_subexp );
			            }

			            // Set Active/End Member Plan
			            if ( $brlist->params->brsubplan ) {
				            if ( !$substatus ) {
				                $contact->setField( $brlist->params->brsubplan, 'None' );
				            } else {
					            $contact->setField( $brlist->params->brsubplan, $uginfo->userg_subendplanname );
				            }
			            }
		            }

	                // Update Lists
	                if ($data[$brlist->uf_sname]) {
	                    if ($unsubed) { //Remove all previous list
	                        $currentLists = $contact->getLists();
	                        foreach ($currentLists as $l) {
	                            $contact->removeFromList($l);
	                        }
							$contact->save(true);
	                    }
	                    $contact->addToList($brlist->uf_default);
	                } else {
	                    $contact->removeFromList($brlist->uf_default);
	                }

	                // Save
	                $contact->save(true);

	            } catch (Exception $e) {

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
			
			//MailChimp Integration
			foreach ($mclists as $mclist) {
				include_once 'components/com_mue/lib/mailchimp.php';
				if ($data[$mclist->uf_sname]) {
					if (strstr($mclist->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$mclist->uf_default,2);	} 
					else { $mc_key = $cfg->mckey; $mc_list = $mclist->uf_default; }
					$mcf=$mclist->uf_sname;
					$mc = new MailChimpHelper($mc_key,$mc_list);
					$mcdata = array('FNAME'=>$item->fname, 'LNAME'=>$item->lname, 'OPTIN_IP'=>$_SERVER['REMOTE_ADDR'], 'OPTIN_TIME'=>$date->toSql(true));
					if ($mclist->params->mcvars) {
						$othervars=$mclist->params->mcvars;
						foreach ($othervars as $mcv=>$mue) {
							if ($mue) {
								if (in_array($mue,$optfs)) $mcdata[$mcv] = $optionsdata[$item->$mue];
								else if (in_array($mue,$moptfs)) {
									$mcdata[$mcv] = "";
									foreach (explode(" ",$item->$mue) as $mfo) {
										$mcdata[$mcv] .= $optionsdata[$mfo]." ";
									}
								}
								else $mcdata[$mcv] = $item->$mue;
							}
						} 
					}
					if ($mclist->params->mcrgroup) {
						if (!$substatus) $mcdata[$mclist->params->mcrgroup]=$mclist->params->mcreggroup;
						else $mcdata[$mclist->params->mcrgroup]=$mclist->params->mcsubgroup;
						if ($mclist->params->mcsubsince) {
							if ($uginfo->userg_subsince != "0000-00-00")	$mcdata[$mclist->params->mcsubsince] = $uginfo->userg_subsince;
							else $mcdata[$mclist->params->mcsubsince] = "";
						}
						if ($mclist->params->mcsubexp) {
							if ($uginfo->userg_subexp != '0000-00-00') $mcdata[$mclist->params->mcsubexp] = $uginfo->userg_subexp;
							else $mcdata[$mclist->params->mcsubexp] = "";
						}
						if ($mclist->params->mcsubpaytype) $mcdata[$mclist->params->mcsubpaytype] = $uginfo->userg_lastpaidvia;
					}
					if ($mclist->params->mcigroup) {
						$mcdata['groupings']=array(array("name"=>$mclist->params->mcigroup,"groups"=>$mclist->params->mcigroups));
					}
					$mcd=print_r($mcdata,true);
					if ($mc->subStatus($item->email)) {
						$mcresult = $mc->updateUser(array("email"=>$item->email),$mcdata,false,"html");
						if ($mcresult) { $item->$mcf=1; $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$mclist->uf_default.' '.$mcd."\r\n"; }
						else { $item->$mcf=1; $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
					}
					else {
						$mcresult = $mc->subscribeUser(array("email"=>$item->email),$mcdata,false,"html");
						if ($mcresult) { $item->$mcf=1; $usernotes .= $date->toSql(true)." EMail Subscribed to MailChimp List #".$mclist->uf_default.' '.$mcd."\r\n"; }
						else { $item->$mcf=0; $usernotes .= $date->toSql(true)." Could not subscribe EMail to MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
					}
					
					
				} else {
					if (strstr($mclist->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$mclist->uf_default,2);	} 
					else { $mc_key = $cfg->mckey; $mc_list = $mclist->uf_default; }
					$mcf=$mclist->uf_sname;
					$mc = new MailChimpHelper($mc_key,$mc_list);
					if ($mc->subStatus($item->email)) {
						$mcresult = $mc->unsubscribeUser(array("email"=>$item->email));
						if ($mcresult) { $item->$mcf=0; $usernotes .= $date->toSql(true)." EMail Unsubscribed from MailChimp List #".$mclist->uf_default."\r\n"; }
						else { $item->$mcf=0; $usernotes .= $date->toSql(true)." Could not unsubscribe EMail from MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
					}
				}
			}
			
			//User Directory
			$udf=$cfg->on_userdir_field;
			if ($cfg->userdir && $item->$udf) {
				$dudsql = "DELETE FROM #__mue_userdir WHERE ud_user = ".$user->id;
				$db->setQuery($dudsql);
				$db->query();
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
				$db->query();
				$usernotes .= $date->toSql(true)." Removed from User Directory\r\n";
			}
				
				
			
			//Update Joomla User Info
			$udata['name']=$item->fname." ".$item->lname;
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;
			$udata['block']=$user->block;
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
			$db->query();
			
			$flist = $this->getUserFields($ugroup,false,false,true);
			foreach ($flist as $fl) {
				$fieldname = $fl->uf_sname;
				if (!$fl->uf_cms) {

					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$db->escape($item->$fieldname).'")';
					$db->setQuery($qf);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
			
			//Update update date
			$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
			$db->setQuery($qud);
			if (!$db->query()) {
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

	

}
