<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport('joomla.utilities.date');

class MUEModelUserreg extends JModelLegacy
{

	function getUserGroups($id=0) {
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$aid = $user->getAuthorisedViewLevels();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_ugroups');
		$query->where('published = 1');
		$query->where('access IN ('.implode(",",$aid).')');
		if ($id) $query->where('ug_id = '.$id);
		$query->order('ordering');
		$db->setQuery($query);
		$ugroups = $db->loadObjectList();
		return $ugroups;
	}
	
	function getUserFields($group,$all=true) {
		$app=Jfactory::getApplication();
		$db =& JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_uguf as g';
		$qd.= ' RIGHT JOIN #__mue_ufields as f ON g.uguf_field = f.uf_id';
		$qd.= ' WHERE f.published = 1 && g.uguf_group='.$group;
		$qd.=" && f.uf_hidden = 0";
		$qd.=" && f.uf_reg = 1";
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
				
				$fn=$u->uf_sname;
				if ($u->uf_type == 'multi' || $u->uf_type == 'dropdown' || $u->uf_type == 'mcbox' || $u->uf_type == 'mlist') {
					$u->value=explode(" ",$app->getUserState('mue.userreg.'.$fn,$u->uf_default));
				} else if ($u->uf_type == 'cbox' || $u->uf_type == 'yesno') {
					$u->value=$app->getUserState('mue.userreg.'.$fn,$u->uf_default);
				} else if ($u->uf_type == 'mailchimp' || $u->uf_type == 'cmlist') {
					$u->value=$app->getUserState('mue.userreg.'.$fn,"1");
				} else if ($u->uf_type == 'birthday') {
					$u->value=$app->getUserState('mue.userreg.'.$fn,$u->uf_default);
				} else if ($u->uf_type != 'captcha') {
					$u->value=$app->getUserState('mue.userreg.'.$fn,$u->uf_default);
				}
			}
		}
		return $ufields;
	}
	
	public function save()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Initialise variables;
		$data		= JRequest::getVar('jform', array(), 'post', 'array'); 
		$dispatcher = JDispatcher::getInstance();
		$isNew = true;
		$db		= $this->getDbo();
		$app=Jfactory::getApplication();
		$session=JFactory::getSession();
		$cfg = MUEHelper::getConfig();
		$date = new JDate('now');
		$usernotes = $date->toSql(true)." Registered"."\r\n";
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('user');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$fids = array();
			$optfs = array();
			$moptfs = array();
			$mclists = array();
			$cmlists = array();
			$brlists = array();
			$flist = $this->getUserFields($data['userGroupID'],false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'brlist') {
					$brlists[]=$d;
				} else if ($d->uf_type == 'mailchimp') {
					$mclists[]=$d;
				} else if ($d->uf_type == 'cmlist') {
					$cmlists[]=$d;
				} else if ($d->uf_type == 'captcha') {
					$capfield=$fieldname;
				} else if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
				} else if ($d->uf_type=='cbox') {
					$item->$fieldname = ($data[$fieldname]=='on') ? "1" : "0";
				} else if ($d->uf_type=='birthday') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.$fday;
				} else {
					$item->$fieldname = $data[$fieldname];
				}
				if ($d->uf_type=="multi" || $d->uf_type=="dropdown") $optfs[]=$d->uf_sname;
				if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") $moptfs[]=$d->uf_sname;
				if ($d->uf_type != 'captcha') $fids[]=$d->uf_id;
				if ($d->uf_type != 'captcha' || $d->uf_type != 'password') $app->setUserState('mue.userreg.'.$fieldname, $item->$fieldname);
			}

			$ginfo=MUEHelper::getGroupInfo($data['userGroupID']);
			$item->user_group = $ginfo->ug_name;
			$item->site_url = JURI::base();

			// reCAPTCHA
			if ($cfg->rc_config == "visible" || $cfg->rc_config == "invisible") {
				$rc_url = 'https://www.google.com/recaptcha/api/siteverify';
				$rc_data = array(
					'secret' => $cfg->rc_api_secret,
					'response' => $_POST["g-recaptcha-response"]
				);
				$rc_options = array(
					'http' => array (
						'method' => 'POST',
						'content' => http_build_query($rc_data)
					)
				);
				$rc_context  = stream_context_create($rc_options);
				$rc_verify = file_get_contents($rc_url, false, $rc_context);
				$rc_captcha_success=json_decode($rc_verify);
				if ($rc_captcha_success->success==false) {
					$this->setError('reCAPTCHA Response Required');
					return false;
				} else if ($rc_captcha_success->success==true) {

				} else {
					$this->setError('reCAPTCHA Error');
					return false;
				}
			}

			if ($capfield) {
				include_once 'components/com_mue/lib/securimage/securimage.php';
				$securimage = new Securimage();
				$securimage->session_name = $session->getName();
				$securimage->case_sensitive  = false; 
				if ($securimage->check($data[$capfield]) == false) {
					$this->setError('Security Code Incorrect');
					return false;
				} 
			}
				
			$odsql = "SELECT * FROM #__mue_ufields_opts";
			$db->setQuery($odsql);
			$optionsdata = array();
			$optres = $db->loadObjectList();
			foreach ($optres as $o) {
				$optionsdata[$o->opt_id]=$o->opt_text;
			}
			
			//Create Joomla User
			$user= new JUser;
			$udata['name']=$item->fname." ".$item->lname;
			$udata['email']=strtolower($item->email);
			$udata['username']=(strtolower($item->username)) ? strtolower($item->username) : strtolower($item->email);
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;
			$udata['block']=0;
			$udata['groups'][]=2;
			if (!$user->bind($udata)) {
				$this->setError('Bind Error: '.$user->getError());
				return false;
			}
			if (!$user->save()) {
				$this->setError('Save Error:'.$user->getError());
				return false;
			}
			
			// Bronto Mail Integration
			foreach ($brlists as $brlist) {
				// Get contact and status
				$token  = $cfg->brkey;
				$bronto = new Bronto_Api();
				$bronto->setToken( $token );
				$bronto->login();
				$contactObject  = $bronto->getContactObject();
				$contact        = $contactObject->createRow();
				$contact->email = $item->email;
				$contact->read();
				$unsubed = false;

				if ($data[$brlist->uf_sname]) {
					if ( $contact->status == 'transactional' || $contact->status == 'unconfirmed' || $contact->status == 'unsub' ) {
						if ( $contact->status == 'unsub' ) {
							$unsubed = true;
						}
						$contact->status = "onboarding";
						$contact->save();
					}
				}


				// Update fields
				if ( $brlist->params->brvars && $data[ $brlist->uf_sname ] ) {
					$othervars = $brlist->params->brvars;
					foreach ( $othervars as $brv => $mue ) {
						if ( $mue ) {
							if ( $brlist->params->brfieldtypes->$brv == "checkbox" ) {
								if ( $item->$mue == "1" ) {
									$contact->setField( $brv, 'true' );
								} else {
									$contact->setField( $brv, 'false' );
								}
							} else if ( in_array( $mue, $optfs ) ) {
								$contact->setField( $brv, $optionsdata[ $item->$mue ] );
							} else if ( in_array( $mue, $moptfs ) ) {
								$mcdata[ $mcv ] = "";
								$fv             = '';
								foreach ( explode( " ", $item->$mue ) as $mfo ) {
									$fv .= $optionsdata[ $mfo ] . " ";
								}
								$contact->setField( $brv, $fv );
							} else {
								$contact->setField( $brv, $item->$mue );
							}
						}
					}
				}

				// Update Subscription Info
				if ( $brlist->params->brsubstatus ) {
					// Set Member Status
					$contact->setField( $brlist->params->brsubstatus, $brlist->params->brsubtextno );
				}

				// Update Lists
				if ( $data[ $brlist->uf_sname ] ) {
					if ( $unsubed ) { //Remove all previous list
						$currentLists = $contact->getLists();
						foreach ( $currentLists as $l ) {
							$contact->removeFromList( $l );
						}
						$contact->save( true );
					}
					$contact->addToList( $brlist->uf_default );
				}

				// Save
				$contact->save( true );

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
						else { $item->$cmuf=0; $usernotes .= $date->toSql(true)." Could not update EMail subscription on Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
					} else {
						$cmresult = $cm->addSubscriber($cmlist->uf_default,$cmdata);
						if ($cmresult) { $item->$cmuf=1; $usernotes .= $date->toSql(true)." EMail Subscribed to Campaign Monitor List #".$cmlist->uf_default.' '.$cmd."\r\n"; }
						else { $item->$cmuf=0; $usernotes .= $date->toSql(true)." Could not subscribe EMail to Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
					}
						
				} else {
					$item->$cmf=0;
				}
			}
			
			//MailChimp List
			foreach ($mclists as $mclist) {
				if ($data[$mclist->uf_sname])  {
					if (strstr($mclist->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$mclist->uf_default,2);	} 
					else { $mc_key = $cfg->mckey; $mc_list = $mclist->uf_default; }
					$mcf=$mclist->uf_sname;
					include_once 'components/com_mue/lib/mailchimp.php';
					$mc = new MailChimpHelper($mc_key,$mc_list);
					$mcdata = array('FNAME'=>$item->fname, 'LNAME'=>$item->lname, 'OPTIN_IP'=>$_SERVER['REMOTE_ADDR'], 'OPTIN_TIME'=>$date->toSql(true));
					if ($mclist->params->mcvars) {
						$othervars=$mclist->params->mcvars;
						foreach ($othervars as $mcv=>$mue) {
							if ($mue) {
								if (in_array($mue,$optfs)) {
									$mcdata[$mcv] = $optionsdata[$item->$mue];
								} else if (in_array($mue,$moptfs)) {
									$mcdata[$mcv] = "";
									foreach (explode(" ",$item->$mue) as $mfo) {
										$mcdata[$mcv] .= $optionsdata[$mfo]." ";
									}
								} else {
									$mcdata[$mcv] = $item->$mue;
								}
							}
						}
					}
					if ($mclist->params->mcrgroup) {
						$mcdata[$mclist->params->mcrgroup]=$mclist->params->mcreggroup;
					}
					if ($mclist->params->mcigroup) {
						$mcdata['groupings']=array(array("name"=>$mclist->params->mcigroup,"groups"=>$mclist->params->mcigroups));
					}
					$mcresult = $mc->subscribeUser(array("email"=>$item->email),$mcdata,false,"html");
					if ($mcresult) { $item->$mcf=1; $usernotes .= $date->toSql(true)." Subscribed to MailChimp List #".$mclist->uf_default."\r\n"; }
					else { $item->$mcf=0; $usernotes .= $date->toSql(true)." Could not subscribe to MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
				} else {
					$mcf=$mclist->uf_sname;
					$item->$mcf=0;
				}
			}
			
			//User Directory
			$udf=$cfg->on_userdir_field;
			if ($cfg->userdir && $item->$udf) {
				$af=explode(",",$cfg->userdir_mapfields);
				$uf=explode(",",$cfg->userdir_userinfo);
				$sf=explode(",",$cfg->userdir_searchinfo);
				$address="";
				$dbuserinfo="";
				$dbsearchinfo="";
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
				$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=".urlencode($address);
				$gdata_json = $this->curl_file_get_contents($url);
				$gdata = json_decode($gdata_json);
				if ($gdata->status == 'OK') {
					$udsql = 'INSERT INTO #__mue_userdir (ud_user,ud_lat,ud_lon,ud_userinfo,ud_searchinfo) VALUES  ("'.$user->id.'","'.$gdata->results[0]->geometry->location->lat.'","'.$gdata->results[0]->geometry->location->lng.'","'.$dbuserinfo.'","'.$dbsearchinfo.'")';
					$db->setQuery($udsql);
					$db->query();
					$usernotes .= $date->toSql(true)." Added to User Directory\r\n";
				} else {
					$item->$udf = 0;
					$usernotes .= $date->toSql(true)." Could not add to User Directory\r\n";
				}
			}
			
			$credentials = array();
			$credentials['username'] = (strtolower($item->username)) ? strtolower($item->username) : strtolower($item->email);
			$credentials['password'] = $item->password;
			
			//Set user group info
			$qud = 'INSERT INTO #__mue_usergroup (userg_user,userg_group,userg_update,userg_notes,userg_siteurl) VALUES ('.$user->id.','.$data['userGroupID'].',"'.$date->toSql(true).'","'.$db->escape($usernotes).'","'.$item->site_url.'")';
			$db->setQuery($qud);
			if (!$db->query()) {
				$this->setError('Could not update user group');
				return false;
			}
			
			//Setup Welcome email
			$groupinfo = $this->getUserGroups($data['userGroupID']);
			if ($groupinfo->ug_send_welcome_email) {
				$emailtoaddress = $item->email;
				$emailtoname = $item->fname . " " . $item->lname;
				$emailfromaddress = $cfg->FROM_EMAIL;
				$emailfromname = $cfg->FROM_NAME;
				$emailsubject = $cfg->WELCOME_SUBJECT;

				$emailmsg = $groupinfo[0]->ug_welcome_email;
				$emailmsg = str_replace( "{fullname}", $item->fname . " " . $item->lname, $emailmsg );
				$emailmsg = str_replace( "{username}",
					( strtolower( $item->username ) ) ? strtolower( $item->username ) : strtolower( $item->email ),
					$emailmsg );
				$emailmsg = str_replace( "{site_url}", $item->site_url, $emailmsg );
			}
			
			//remove joomla user info from item
			unset($item->password); 
			unset($item->cpassword);
			unset($item->email); $app->setUserState('mue.userreg.email', "");
			unset($item->username); $app->setUserState('mue.userreg.username', "");
			
			
			// Save user info
			$flist = $this->getUserFields($data['userGroupID'],false);
			foreach ($flist as $fl) {
				$fieldname = $fl->uf_sname;
				if (!$fl->uf_cms && $fl->uf_type != "captcha") {
					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$db->escape($item->$fieldname).'")';
					$db->setQuery($qf);
					if (!$db->query()) {
						$this->setError("Error saving additional information");
						return false;
					}
					//welcome email fields
					$emailmsg = str_replace("{".$fieldname."}",$item->$fieldname,$emailmsg);
					if ($d->uf_type!='captcha' || $d->uf_type!='password') $app->setUserState('mue.userreg.'.$fieldname, "");
				}
			}

			if ($groupinfo->ug_send_welcome_email) {
				//Send Welcome Email
				$mail = &JFactory::getMailer();
				$mail->IsHTML( true );
				$mail->addRecipient( $emailtoaddress, $emailtoname );
				$mail->setSender( $emailfromaddress, $emailfromname );
				$mail->setSubject( $emailsubject );
				$mail->setBody( $emailmsg );
				$sent = $mail->Send();
			}
			
			//Login User
			$options = array();
			$options['remember'] = true;
			$error = $app->login($credentials, $options);
			
			
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
	

}
