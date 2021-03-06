<?php
defined('_JEXEC') or die('Restricted access');

class MUEHelper {

	public static function getConfig() {
		$config = JComponentHelper::getParams('com_mue'); 
		$cfg = $config->toObject();
		return $cfg;
	}

	public static function getDaysSinceLastUpdate() {
		$user =& JFactory::getUser();
		$userid = $user->id;
		$db =& JFactory::getDBO();
		$query = 'SELECT DATE(userg_update) FROM #__mue_usergroup ';
		$query.= 'WHERE userg_user="'.$userid.'"';
		$db->setQuery($query); 
		$lu = $db->loadResult();
		if ($lu != "0000-00-00") {
			$datetime1 = new DateTime($lu);
			$datetime2 = new DateTime('now');
			$interval = $datetime1->diff($datetime2);
			return (int)$interval->format('%a');
		} else {
			return -1;
		}
	}

	public static function getUserInfo($useids = false) {
		$cfg = MUEHelper::getConfig();
		$user = JFactory::getUser();
		$userid = $user->id;
		$db = JFactory::getDBO();
		$query = 'SELECT ug.userg_group AS userGroupID, ug.userg_update AS lastUpdated, g.ug_name AS userGroupName FROM #__mue_usergroup as ug ';
		$query.= 'RIGHT JOIN #__mue_ugroups AS g ON ug.userg_group = g.ug_id ';
		$query.= 'WHERE ug.userg_user="'.$userid.'"';
		$db->setQuery($query); $groupdata=$db->loadObject();
		$user->userGroupID=$groupdata->userGroupID;
		$user->userGroupName=$groupdata->userGroupName;
		$user->lastUpdated=$groupdata->lastUpdated;
		$qd = 'SELECT f.*,u.usr_data FROM #__mue_uguf as g';
		$qd.= ' RIGHT JOIN #__mue_ufields as f ON g.uguf_field = f.uf_id';
		$qd.= ' RIGHT JOIN #__mue_users as u ON u.usr_field = f.uf_id && usr_user = '.$userid;
		$qd.= ' WHERE g.uguf_group='.$user->userGroupID;
		$db->setQuery( $qd ); 
		$udata = $db->loadObjectList();
		foreach ($udata as $u) {
			if (!$u->uf_cms) {
				$fn=$u->uf_sname;
				if ($u->uf_type == 'multi' || $u->uf_type == 'dropdown' || $u->uf_type == 'mcbox' || $u->uf_type == 'mlist') {
					if ($useids && $u->uf_change) {
						$user->$fn=explode(" ",$u->usr_data);
					} else { 
						if ($u->usr_data) {
							$ansarr=explode(" ",$u->usr_data);
							$q = 'SELECT opt_text FROM #__mue_ufields_opts WHERE opt_id IN('.implode(",",$ansarr).')';
							$db->setQuery($q);
							$user->$fn = implode(", ",$db->loadColumn());
						} else {
							$user->$fn = "";
						}
					}
				} else if ($u->uf_type == 'cbox' || $u->uf_type == 'yesno') {
					if ($useids && $u->uf_change) $user->$fn=$u->usr_data;
					else $user->$fn = ($u->usr_data == "1") ? "Yes" : "No";
				} else if ($u->uf_type == 'mailchimp') {
					include_once JPATH_BASE.'/components/com_mue/lib/mailchimp.php';

					if (strstr($u->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$u->uf_default,2);	}
					else { $mc_key = $cfg->mckey; $mc_list = $u->uf_default; }
					$mc = new MailChimpHelper($mc_key,$mc_list);
					$mcresult = $mc->subStatus($user->email);
					if ($mcresult) $onlist=true;
					else $onlist=false;
					if ($useids && $u->uf_change) $user->$fn=$onlist;
					else $user->$fn = ($onlist) ? "Yes" : "No";
				} else if ($u->uf_type == 'cmlist') {
					include_once JPATH_BASE.'/components/com_mue/lib/campaignmonitor.php';
					$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
					$cmresult = $cm->getSubscriberDetails($u->uf_default,$user->email);
					if ($cmresult){ if ($cmresult->State=="Active")  { $onlist=true;} else { $onlist=false; } }
					else $onlist=false;
					if ($useids && $u->uf_change) $user->$fn=$onlist;
					else $user->$fn = ($onlist) ? "Yes" : "No";
				} else if ($u->uf_type == 'brlist') {
                    $token = $cfg->brkey;
                    $bronto = new Bronto_Api();
                    $bronto->setToken($token);
                    try {
                    	$bronto->login();
	                    $contactObject = $bronto->getContactObject();
	                    $contact = $contactObject->createRow();
	                    $contact->email = $user->email;
	                    $contact->read();
	                    $onlist = false;
	                    if ($contact->status == 'active' || $contact->status == 'onboarding') {
		                    if (!$contact->listIds || !is_array($contact->listIds)) {
			                    $onlist = false;
		                    } else if (in_array($u->uf_default,$contact->listIds)) {
			                    $onlist = true;
		                    }
		                    else {
			                    $onlist = false;
		                    }
	                    } else {
		                    $onlist = false;
	                    }
                    } catch (Exception $e) {
	                    $onlist = false;
                    }
                    if ($useids && $u->uf_change) $user->$fn=$onlist;
                    else $user->$fn = ($onlist) ? "Yes" : "No";
                } else if ($u->uf_type == 'birthday') {
					if ($useids && $u->uf_change) $user->$fn=$u->usr_data;
					else $user->$fn = date("F j",strtotime('2000-'.substr($u->usr_data,0,2)."-".substr($u->usr_data,2,2).''));
				} else{
					$user->$fn=$u->usr_data;
				}
			}
		}
		return $user;
	}

	public static function getUserGroup($userid = 0) {
		if (!$userid) {
			$user =& JFactory::getUser();
			$userid = $user->id;
		}
		$db =& JFactory::getDBO();
		$query = 'SELECT ug.userg_group AS userGroupID, ug.userg_update AS lastUpdated, g.* FROM #__mue_usergroup as ug ';
		$query.= 'RIGHT JOIN #__mue_ugroups AS g ON ug.userg_group = g.ug_id ';
		$query.= 'WHERE ug.userg_user="'.$userid.'"';
		$db->setQuery($query);
		$usergroup = $db->loadObject();
		return $usergroup;
	}

	public static function getGroupInfo($groupid) {
		if (!$groupid) {
			return false;
		}
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mue_ugroups ';
		$query.= 'WHERE ug_id="'.$groupid.'"';
		$db->setQuery($query);
		$usergroup = $db->loadObject();
		return $usergroup;
	}

	public static function getUserSubs() {
		$user = JFactory::getUser();
		$userid = $user->id;
		$db = JFactory::getDBO();
		$query = 'SELECT s.*, p.*, DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status != "notyetstarted" && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC';
		$db->setQuery($query);
		$usersubs = $db->loadObjectList();
		return $usersubs;
	}

	public static function userHadTrial() {
		$user = JFactory::getUser();
		$userid = $user->id;
		$db = JFactory::getDBO();
		$query = 'SELECT s.*, p.* FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_user="'.$userid.'" ';
		$db->setQuery($query);
		$subs = $db->loadObjectList();
		$hadTrial=false;
		foreach ($subs as $s) {
			if ($s->sub_type == "trial") $hadTrial = true;
		}
		return $hadTrial;
	}

	public static function getActiveSub($userid=0) {
		if (!$userid) {
			$user = JFactory::getUser();
			$userid = $user->id;
		}
		$db = JFactory::getDBO();
		$query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status IN ("completed","accepted") && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
		$db->setQuery($query); 
		$sub = $db->loadObject();
		
		//Member Since
		$query = $db->getQuery(true);
		$query->select('s.usrsub_start');
		$query->from('#__mue_usersubs as s');
		$query->where('s.usrsub_status IN ("completed","accepted","verified")');
		$query->where('s.usrsub_user="'.$userid.'"');
		$query->order('s.usrsub_start ASC');
		$db->setQuery($query,0,1);
		$member_since = $db->loadResult();
		
		if ($sub) {
			$qud = $db->getQuery(true);
			$qud->update('#__mue_usergroup');
			$qud->set('userg_subexp = "'.$sub->usrsub_end.'"');
			$qud->set('userg_lastpaidvia = "'.$sub->usrsub_type.'"');
			$qud->set('userg_subendplanname = "'.$sub->sub_exttitle.'"');
			if ($member_since) $qud->set('userg_subsince = "'.$member_since.'"');
			$qud->where('userg_user = '.$userid);
			$db->setQuery($qud);
			$db->query();
			return $sub;
		} else {
			return false;
		}
	}

	public static function updateUserSub($userid) {
		$user = JFactory::getUser($userid);
		$date = new JDate('now');

		$cfg = MUEHelper::getConfig();
		$db = JFactory::getDBO();
		$query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status IN ("completed","accepted") && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
		$db->setQuery($query);
		$sub = $db->loadObject();

		//Member Since
		$query = $db->getQuery(true);
		$query->select('s.usrsub_start');
		$query->from('#__mue_usersubs as s');
		$query->where('s.usrsub_status IN ("completed","accepted","verified")');
		$query->where('s.usrsub_user="'.$userid.'"');
		$query->order('s.usrsub_start ASC');
		$db->setQuery($query,0,1);
		$member_since = $db->loadResult();

		if ($sub) {
			$qud = $db->getQuery(true);
			$qud->update('#__mue_usergroup');
			$qud->set('userg_subexp = "'.$sub->usrsub_end.'"');
			$qud->set('userg_lastpaidvia = "'.$sub->usrsub_type.'"');
			$qud->set('userg_subendplanname = "'.$sub->sub_exttitle.'"');
			if ($member_since) $qud->set('userg_subsince = "'.$member_since.'"');
			$qud->where('userg_user = '.$userid);
			$db->setQuery($qud);
			$db->query();
		}

		$db = JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_ufields as f ';
		$qd.= ' WHERE f.published = 1 ';
		$qd .= ' && f.uf_type IN ("mailchimp","brlist","cmlist")';
		$qd.= ' ORDER BY f.ordering';
		$db->setQuery( $qd );
		$listFields = $db->loadObjectList();

		$ugq = "SELECT * FROM #__mue_usergroup WHERE userg_user = ".$userid;
		$db->setQuery($ugq);
		$ug = $db->loadObject();

		$usernotes = '';
		foreach ($listFields as $f) {
			$registry = new JRegistry();
			$registry->loadString($f->params);
			$f->params = $registry->toObject();

			if ($f->uf_type == "brlist") {
				// Update Subscription Info
				if ($f->params->brsubstatus) {
					$token = $cfg->brkey;
					$bronto = new Bronto_Api();
					$bronto->setToken($token);
					$bronto->login();

					// Get Contact
					$contactObject = $bronto->getContactObject();
					$contact = $contactObject->createRow();
					$contact->email = $user->email;
					$contact->read();

					// Set Member Status
					if ($sub) $contact->setField( $f->params->brsubstatus, $f->params->brsubtextyes );
					else $contact->setField( $f->params->brsubstatus, $f->params->brsubtextno );

					// Set Member Since
					if ( $f->params->brsubsince && $ug->userg_subsince != "0000-00-00" ) {
						$contact->setField( $f->params->brsubsince, $ug->userg_subsince );
					}

					// Set Member Exp
					if ( $f->params->brsubexp && $ug->userg_subexp != '0000-00-00') {
						$contact->setField( $f->params->brsubexp, $ug->userg_subexp );
					}

					// Set Active/End Member Plan
					if ( $f->params->brsubplan ) {
						if ( !$sub ) {
							$contact->setField( $f->params->brsubplan, 'None' );
						} else {
							$contact->setField( $f->params->brsubplan, $ug->userg_subendplanname );
						}
					}

					// Save Contact
					$contact->save();

					$usernotes .= $date->toSql(true)." EMail Contact Updated on Bronto List ID: ".$f->uf_default."\r\n";
				}
			}

			if ($f->uf_type == "cmlist") {
				if ($f->params->msgroup->field) {

					$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
					$cmdata=array();
					$cmdata = array('Name'=>$user->name, 'EmailAddress'=>$user->email, 'Resubscribe'=>'true');
					$customfields = array();
					$newcmf=array();
					$newcmf['Key']=$f->params->msgroup->field;
					if (!$sub) { $newcmf['Value']=$f->params->msgroup->reg; }
					else { $newcmf['Value']=$f->params->msgroup->sub; }
					$customfields[]=$newcmf;
					$cmdata['CustomFields']=$customfields;

					$cmd=print_r($cmdata,true);
					if ($cm->getSubscriberDetails($f->uf_default,$user->email)) {
						$cmresult = $cm->updateSubscriber($f->uf_default,$user->email,$cmdata);
						if ($cmresult) {  $usernotes .= $date->toSql(true)." EMail Subscription Updated on Campaign Monitor List #".$f->uf_default.' '.$cmd."\r\n"; }
						else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on Campaign Monitor List #".$f->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
					}
				}
			}

			if ($f->uf_type == "mailchimp") {
				if ($f->params->mcrgroup) {

					if (strstr($f->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$f->uf_default,2);	}
					else { $mc_key = $cfg->mckey; $mc_list = $f->uf_default; }
					$mc = new MailChimpHelper($mc_key,$mc_list);
					$mcdata=array();

					if (!$sub) $mcdata[$f->params->mcrgroup]=$f->params->mcreggroup;
					else $mcdata[$f->params->mcrgroup]=$f->params->mcsubgroup;

					if ($f->params->mcsubsince) {
						if ($ug->userg_subsince != "0000-00-00")	$mcdata[$f->params->mcsubsince] = $ug->userg_subsince;
						else $mcdata[$f->params->mcsubsince] = "";
					}
					if ($f->params->mcsubexp) {
						if ($ug->userg_subexp != '0000-00-00') $mcdata[$f->params->mcsubexp] = $ug->userg_subexp;
						else $mcdata[$f->params->mcsubexp] = "";
					}
					if ($f->params->mcsubpaytype) $mcdata[$f->params->mcsubpaytype] = $ug->userg_lastpaidvia;

					$mcd=print_r($mcdata,true);
					if ($mc->subStatus($user->email)) {
						$mcresult = $mc->updateUser(array("email"=>$user->email),$mcdata,false,"html");
						if ($mcresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$f->uf_default.' '.$mcd."\r\n"; }
						else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$f->uf_default." Error: ".$mc->error."\r\n"; }
					}
				}
			}
		}

		if ($cfg->subgroup <= 2) return;
		if ($sub) {
			$query = $db->getQuery(true);
			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
			$query->where($db->quoteName('user_id') . ' = ' . (int) $userid);
			$db->setQuery($query);
			$hasgroup = $db->loadResult();
			if (!$hasgroup) {
				$query->clear();
				$query->insert($db->quoteName('#__user_usergroup_map'));
				$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
				$query->values((int) $userid . ',' . $cfg->subgroup);
				$db->setQuery($query);
				$db->query();
			}
			$usernotes .= $date->toSql(true)." Added to Membership Group\r\n";
		} else {
			$query = $db->getQuery(true);

			// Remove user from the sub group
			$query->delete($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('user_id') . ' = ' . (int) $userid);
			$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
			$db->setQuery($query);
			$db->query();
			$usernotes .= $date->toSql(true)." Removed from Membership Group\r\n";
		}

		//Update update date
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		$db->query();
	}

	public static function updateSubJoomlaGroup($userid=0) {
		$cfg = MUEHelper::getConfig();
		if (!$userid) return false;

		$db = JFactory::getDBO();
		$query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status IN ("completed","accepted") && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
		$db->setQuery($query);
		$sub = $db->loadObject();

		//Member Since
		$query = $db->getQuery(true);
		$query->select('s.usrsub_start');
		$query->from('#__mue_usersubs as s');
		$query->where('s.usrsub_status IN ("completed","accepted","verified")');
		$query->where('s.usrsub_user="'.$userid.'"');
		$query->order('s.usrsub_start ASC');
		$db->setQuery($query,0,1);
		$member_since = $db->loadResult();

		if ($sub) {
			$qud = $db->getQuery(true);
			$qud->update('#__mue_usergroup');
			$qud->set('userg_subexp = "'.$sub->usrsub_end.'"');
			$qud->set('userg_lastpaidvia = "'.$sub->usrsub_type.'"');
			$qud->set('userg_subendplanname = "'.$sub->sub_exttitle.'"');
			if ($member_since) $qud->set('userg_subsince = "'.$member_since.'"');
			$qud->where('userg_user = '.$userid);
			$db->setQuery($qud);
			$db->query();
			//return $sub;
		}

		$db = JFactory::getDBO();
		if ($cfg->subgroup <= 2) return;
		if ($sub) {
			$query = $db->getQuery(true);
			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
			$query->where($db->quoteName('user_id') . ' = ' . (int) $userid);
			$db->setQuery($query); 
			$hasgroup = $db->loadResult();
			if (!$hasgroup) {
				$query->clear();
				$query->insert($db->quoteName('#__user_usergroup_map'));
				$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
				$query->values((int) $userid . ',' . $cfg->subgroup);
				$db->setQuery($query);
				$db->query();
			}
		} else {
			$query = $db->getQuery(true);
				
			// Remove user from the sub group
			$query->delete($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('user_id') . ' = ' . (int) $userid);
			$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
			$db->setQuery($query);
			$db->query();
		}
	}
}
