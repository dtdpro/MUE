<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport('joomla.utilities.date');

class MUEModelUserReg extends JModel
{

	function getUserGroups($id=0) {
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$aid = $user->getAuthorisedViewLevels();
		$qd = 'SELECT ug.* FROM #__mue_ugroups as ug WHERE ug.access IN ('.implode(",",$aid).')';
		if ($id) $qd .= " && ug.ug_id = ".$id;
		$qd.= ' ORDER BY ug.ordering';
		$db->setQuery( $qd ); 
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
		if ($all) {
			foreach ($ufields as &$f) {
				switch ($f->uf_type) {
					case 'multi':
					case 'dropdown':
					case 'mlist':
					case 'mcbox':
						$qo = 'SELECT opt_id as value, opt_text as text FROM #__mue_ufields_opts WHERE opt_field='.$f->uf_id.' && published > 0 ORDER BY ordering';
						$this->_db->setQuery($qo);
						$f->options = $this->_db->loadObjectList();
						break;
				}
			}
		
			foreach ($ufields as &$u) {
				$fn=$u->uf_sname;
				if ($u->uf_type == 'multi' || $u->uf_type == 'dropdown' || $u->uf_type == 'mcbox' || $u->uf_type == 'mlist') {
					$u->value=explode(" ",$app->getUserState('mue.userreg.'.$fn,$u->uf_default)); 
				} else if ($u->uf_type == 'cbox' || $u->uf_type == 'yesno') {
					$u->value=$app->getUserState('mue.userreg.'.$fn,$u->uf_default);
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
		JPluginHelper::importPlugin('content');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$fids = array();
			$optfs = array();
			$flist = $this->getUserFields($data['userGroupID'],false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'mailchimp') {
					$mclist=$fieldname;
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
				if ($d->uf_type=="mcbox" || $d->uf_type=="mlist" || $d->uf_type=="multi" || $d->uf_type=="dropdown") $optfs[]=$d->uf_sname;
				if ($d->uf_type != 'captcha') $fids[]=$d->uf_id;
				if ($d->uf_type != 'captcha' || $d->uf_type != 'password') $app->setUserState('mue.userreg.'.$fieldname, $item->$fieldname);
			}
			$item->site_url = JURI::base();
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
			
			//MailChimp List
			if ($mclist) {
				if ($data[$mclist])  {
					include_once 'components/com_mue/lib/mailchimp.php';
					$mc = new MailChimp($cfg->mckey,$cfg->mclist);
					$mcdata = array('FNAME'=>$item->fname, 'LNAME'=>$item->lname, 'OPTIN_IP'=>$_SERVER['REMOTE_ADDR'], 'OPTIN_TIME'=>$date->toSql(true));
					$othervars=explode(",",$cfg->mcvars);
					foreach ($othervars as $ov) {
						list($mue, $mcv) = explode(":",$ov,2);
						if (in_array($mue,$optfs)) $mcdata[$mcv] = $optionsdata[$item->$mue];
						else if (in_array($mue,$moptfs)) {
							$mcdata[$mcv] = "";
							foreach (explode(" ",$item->$mue) as $mfo) {
								$mcdata[$mcv] .= $optionsdata[$mfo]." ";
							}
						}
						else $mcdata[$mcv] = $item->$mue;					}
					$mcresult = $mc->subscribeUser($item->email,$mcdata,false,"html");
					if ($mcresult) { $item->$mclist; $usernotes .= $date->toSql(true)." Subscribed to MailChimp List #".$cfg->mclist."\r\n"; }
					else { $item->$mclist; $usernotes .= $date->toSql(true)." Could not subscribe to MailChimp List #".$cfg->mclist." Error: ".$mc->error."\r\n"; }
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
			$qud = 'INSERT INTO #__mue_usergroup (userg_user,userg_group,userg_update,userg_notes,userg_siteurl) VALUES ('.$user->id.','.$data['userGroupID'].',"'.$date->toSql(true).'","'.$usernotes.'","'.$item->site_url.'")';
			$db->setQuery($qud);
			if (!$db->query()) {
				$this->setError('Could not update user group');
				return false;
			}
			
			//Setup Welcome email
			$groupinfo = $this->getUserGroups($data['userGroupID']);
			$emailtoaddress = $item->email;
			$emailtoname = $item->fname." ".$item->lname;
			$emailfromaddress = $cfg->FROM_EMAIL;
			$emailfromname = $cfg->FROM_NAME;
			$emailsubject = $cfg->WELCOME_SUBJECT;
			
			$emailmsg = $groupinfo[0]->ug_welcome_email;
			$emailmsg = str_replace("{fullname}",$item->fname." ".$item->lname,$emailmsg);
			$emailmsg = str_replace("{username}",(strtolower($item->username)) ? strtolower($item->username) : strtolower($item->email),$emailmsg);
			$emailmsg = str_replace("{password}",$item->password,$emailmsg);
			$emailmsg = str_replace("{site_url}",$item->site_url,$emailmsg);
			
			
			//remove joomla user info from item
			unset($item->password); 
			unset($item->cpassword);
			unset($item->email); $app->setUserState('mue.userreg.email', "");
			unset($item->username); $app->setUserState('mue.userreg.username', "");
			
			
			// Save ContinuED user info
			$flist = $this->getUserFields($data['userGroupID'],false);
			foreach ($flist as $fl) {
				$fieldname = $fl->uf_sname;
				if (!$fl->uf_cms && $fl->uf_type != "captcha") {
					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$item->$fieldname.'")';
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
			
			//Send Welcome Email
			$mail = &JFactory::getMailer();
			$mail->IsHTML(true);
			$mail->addRecipient($emailtoaddress,$emailtoname);
			$mail->setSender($emailfromaddress,$emailfromname);
			$mail->setSubject($emailsubject);
			$mail->setBody( $emailmsg );
			$sent = $mail->Send();
			
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
