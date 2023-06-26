<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport('joomla.utilities.date');

use Joomla\CMS\Session\Session;

class MUEModelUserreg extends JModelLegacy
{

	function getUserGroups($id=0) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
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
		$db = JFactory::getDBO();
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

	function getPresetFields() {
		$input = JFactory::getApplication()->input;
		$app=Jfactory::getApplication();
		$preset_fields = $input->get('mue_pre', [], 'get', 'array');
		foreach ($preset_fields as $pre_field=>$prevalue) {
			$app->setUserState('mue.userreg.'.$pre_field, $prevalue);
		}

	}
	
	public function save()
	{
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		// Initialise variables;
		$input = JFactory::getApplication()->input;
		$data		= $input->get('jform', [], 'post', 'array');
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
			$optfs = array(); // single option field
			$moptfs = array(); // multi option field
			$cmlists = array();
			$aclists = array();
			$flist = $this->getUserFields($data['userGroupID'],false);
			$item = new stdClass();
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'aclist') {
					$aclists[]=$d;
				} else if ($d->uf_type == 'cmlist') {
					$cmlists[]=$d;
				} else if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
				} else if ($d->uf_type=='cbox') {
					if (isset($data[$fieldname])) {
						$item->$fieldname = ( $data[ $fieldname ] == 'on' ) ? "1" : "0";
					} else {
						$item->$fieldname = 0;
					}
				} else if ($d->uf_type=='birthday') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.$fday;
				} else if ($d->uf_type != 'message') {
					$item->$fieldname = $data[$fieldname];
				}
				if ($d->uf_type=="multi" || $d->uf_type=="dropdown") $optfs[]=$d->uf_sname;
				if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") $moptfs[]=$d->uf_sname;
				$fids[]=$d->uf_id;
				if ($d->uf_type != 'password') {
					if (property_exists($item,$fieldname)) $app->setUserState('mue.userreg.'.$fieldname, $item->$fieldname);
				}
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
				
			$odsql = "SELECT * FROM #__mue_ufields_opts";
			$db->setQuery($odsql);
			$optionsdata = array();
			$optres = $db->loadObjectList();
			foreach ($optres as $o) {
				$optionsdata[$o->opt_id]=$o->opt_text;
			}

			// Get Joomla Config
			$config = JFactory::getConfig();

			//Get Joomla User Config Params
			$userParams = JComponentHelper::getParams('com_users');
			$useractivation = $userParams->get('useractivation');

			//Create Joomla User
			$user= new JUser;
			$udata['name']=$item->fname." ".$item->lname;
			$udata['email']=strtolower($item->email);
			$udata['username']=(strtolower($item->username)) ? strtolower($item->username) : strtolower($item->email);
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;

			// Check if the user needs to activate their account.
			if (($useractivation == 1) || ($useractivation == 2))
			{
				$udata['activation'] = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
				$udata['block'] = 1;
			} else {
				$udata['block']=0;
			}

			$udata['groups'][]=2;

			if (!$user->bind($udata)) {
				$this->setError('Bind Error: '.$user->getError());
				return false;
			}
			if (!$user->save()) {
				$this->setError('Save Error:'.$user->getError());
				return false;
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
							if ( $mueVar == "user_group" ) {
								$fieldVal = $ginfo->ug_name;
							} else if ( in_array( $mueVar, $optfs ) ) {
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
						$fieldVal=$aclistFirst->params->acsubtextyes;
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
					// Gather List Ids and set status to subscribed, only if checked, unchecked does nothing
					$linkedLists = [];
					foreach ( $aclists as $aclist ) {
						$acFieldName = $aclist->uf_sname;
						if ( isset($data[ $aclist->uf_sname ]) && $data[ $aclist->uf_sname ] ) {
							$item->$acFieldName=1;
							$subtolist = true;
							$listsForField = json_decode( $aclist->uf_default, true );
							foreach ( $listsForField as $lf ) {
								$linkedLists[ $lf ] = $subtolist;
							}
						} else {
							$item->$acFieldName=0;
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
				$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($address).'&key='.$cfg->gm_api_key;
				$gdata_json = $this->curl_file_get_contents($url);
				$gdata = json_decode($gdata_json);
				if ($gdata->status == 'OK') {
					$udsql = 'INSERT INTO #__mue_userdir (ud_user,ud_lat,ud_lon,ud_userinfo,ud_searchinfo) VALUES  ("'.$user->id.'","'.$gdata->results[0]->geometry->location->lat.'","'.$gdata->results[0]->geometry->location->lng.'","'.$dbuserinfo.'","'.$dbsearchinfo.'")';
					$db->setQuery($udsql);
					$db->execute();
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
			if (!$db->execute()) {
				$this->setError('Could not update user group');
				return false;
			}
			
			//Setup Welcome email
			$groupinfo = $this->getUserGroups($data['userGroupID'])[0];
			if ($groupinfo->ug_send_welcome_email) {
				$emailtoaddress = $item->email;
				$emailtoname = $item->fname . " " . $item->lname;
				$emailfromaddress = $cfg->FROM_EMAIL;
				$emailfromname = $cfg->FROM_NAME;
				$emailsubject = $cfg->WELCOME_SUBJECT;

				$emailmsg = $groupinfo->ug_welcome_email;
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
					if (property_exists($item,$fieldname)) {
						$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("' . $user->id . '","' . $fl->uf_id . '","' . $db->escape( $item->$fieldname ) . '")';
						$db->setQuery( $qf );
						if ( ! $db->execute() ) {
							$this->setError( "Error saving additional information" );
							return false;
						}
						if ( $groupinfo->ug_send_welcome_email ) {
							//welcome email fields
							$emailmsg = str_replace( "{" . $fieldname . "}", $item->$fieldname, $emailmsg );
						}
						if ( $d->uf_type != 'password' ) {
							$app->setUserState( 'mue.userreg.' . $fieldname, "" );
						}
					}
				}
			}

			// Handle account activation/confirmation emails.
			if ($useractivation == 2 || $useractivation == 1)
			{
				// Set the link to confirm the user email.
				$actLink = JRoute::link( 'site', 'index.php?option=com_mue&view=activation&layout=useractivate&token=' . $udata['activation'], false, 0, true );
				$emailmsg = str_replace( "{actlink}", $actLink, $emailmsg );
			}

			//Send Welcome Email
			if ($groupinfo->ug_send_welcome_email) {
				/*$mail = JFactory::getMailer();
				$mail->IsHTML( true );
				$mail->addRecipient( $emailtoaddress, $emailtoname );
				$mail->setSender( $emailfromaddress, $emailfromname );
				$mail->setSubject( $emailsubject );
				$mail->setBody( $emailmsg );
				$sent = $mail->Send();*/
				/*if ($sent !== true) {
					$this->setError($sent->getError());
					return false;
				}*/


				$mail = JFactory::getMailer();

				$emllist = Array();
				$emllist[] = $emailtoaddress;

				$sent = $mail->sendMail($emailfromaddress, $emailfromname, $emllist, $emailsubject, $emailmsg, true, null, null, null);
			}

			//Login User if activation not required
			if (($useractivation == 0)) {
				$options = array();
				$options['remember'] = true;
				$error = $app->login( $credentials, $options );
			}
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		if ($useractivation == 1) {
			return 'userlink';
		} else if ($useractivation == 2) {
			return 'adminlink';
		} else {
			return 'activated';
		}
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

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}
	

}
