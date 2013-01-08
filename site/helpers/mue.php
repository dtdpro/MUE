<?php
defined('_JEXEC') or die('Restricted access');

class MUEHelper {

	function getConfig() {
		$config = JComponentHelper::getParams('com_mue'); 
		$cfg = $config->toObject();
		return $cfg;
	}
	
	function getUserInfo($useids = false) {
		$cfg = MUEHelper::getConfig();
		$user =& JFactory::getUser();
		$userid = $user->id;
		$db =& JFactory::getDBO();
		$query = 'SELECT ug.userg_group AS userGroupID, ug.userg_update AS lastUpdated, g.ug_name AS userGroupName FROM #__mue_usergroup as ug ';
		$query.= 'RIGHT JOIN #__mue_ugroups AS g ON ug.userg_group = g.ug_id ';
		$query.= 'WHERE ug.userg_user="'.$userid.'"';
		$db->setQuery($query); $groupdata=$db->loadObject();
		$user->userGroupID=$groupdata->userGroupID;
		$user->userGroupName=$groupdata->userGroupName;
		$user->lastUpdated=$groupdata->lastUpdated;
		$qd = 'SELECT f.uf_sname,f.uf_type,u.usr_data,f.uf_change FROM #__mue_uguf as g';
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
							$user->$fn = implode(", ",$db->loadResultArray());
						} else {
							$user->$fn = "";
						}
					}
				} else if ($u->uf_type == 'cbox' || $u->uf_type == 'yesno') {
					if ($useids && $u->uf_change) $user->$fn=$u->usr_data;
					else $user->$fn = ($u->usr_data == "1") ? "Yes" : "No";
				} else if ($u->uf_type == 'mailchimp') {
					include_once 'components/com_mue/lib/mailchimp.php';
					$mc = new MailChimp($cfg->mckey,$cfg->mclist);
					$mcresult = $mc->subStatus($user->email);
					if ($mcresult) $onlist=true;
					else $onlist=false;
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
	
	function getUserGroup($userid = 0) {
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
	
	function getUserSubs() {
		$user =& JFactory::getUser();
		$userid = $user->id;
		$db =& JFactory::getDBO();
		$query = 'SELECT s.*, p.*, DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status != "notyetstarted" && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC';
		$db->setQuery($query);
		$usersubs = $db->loadObjectList();
		return $usersubs;
	}
	
	function getActiveSub() {
		$user =& JFactory::getUser();
		$userid = $user->id;
		$db =& JFactory::getDBO();
		$query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status != "notyetstarted" && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
		$db->setQuery($query); 
		$sub = $db->loadObject();
		if ($sub) {
			return $sub;
		} else {
			return false;
		}
	}
}
