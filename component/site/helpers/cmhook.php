<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );

require_once(JPATH_BASE.'/includes/defines.php' );
require_once(JPATH_BASE.'/includes/framework.php' );
require_once('mue.php');

$app =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$cfg=MUEHelper::getConfig();
$date = new JDate('now');

$body = json_decode(@file_get_contents('php://input')); 
$list = $body->ListID;
$events = $body->Events;

foreach ($events as $data) {
	if ($data->Type == "Deactivate" && $list) {
		$q = 'SELECT id FROM #__users WHERE email = "'.$db->escape($data->EmailAddress).'"';
		$db->setQuery($q);
		if ($user = $db->loadResult()) {
			$q2 = 'SELECT uf_id FROM #__mue_ufields WHERE uf_default = "'.$db->escape($list).'"';
			$db->setQuery($q2);
			if ($fid = $db->loadResult()) {
				$q3 = 'UPDATE #__mue_users SET usr_data = 0 WHERE usr_user = '.$user.' && usr_field = '.$fid;
				$db->setQuery($q3);
				if ($db->query()) {
					$usernotes = $date->toSql(true)." Campaign Monitor Unsubscribe from List #".$db->espcae($list)."\r\n";
					$q4 = 'UPDATE #__mue_usergroup SET userg_notes = CONCAT(userg_notes,"'.$usernotes.'") WHERE userg_user = '.$user;
					$db->setQuery($q4);
					$db->query();
				}
			}
		}
	}
	
	if ($data->Type == "Subscribe" && $list) {
		$q = 'SELECT id FROM #__users WHERE email = "'.$db->escape($data->EmailAddress).'"';
		$db->setQuery($q);
		if ($user = $db->loadResult()) {
			$q2 = 'SELECT uf_id FROM #__mue_ufields WHERE uf_default = "'.$db->escape($list).'"';
			$db->setQuery($q2);
			if ($fid = $db->loadResult()) {
				$q3 = 'UPDATE #__mue_users SET usr_data = 1 WHERE usr_user = '.$user.' && usr_field = '.$fid;
				$db->setQuery($q3);
				if ($db->query()) {
					$usernotes = $date->toSql(true)." Campaign Monitor Subscribe to List #".$db->espcae($list)."\r\n";
					$q4 = 'UPDATE #__mue_usergroup SET userg_notes = CONCAT(userg_notes,"'.$usernotes.'") WHERE userg_user = '.$user;
					$db->setQuery($q4);
					$db->query();
				}
			}
		}
	}
}


