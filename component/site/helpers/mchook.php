<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );
define( 'DS', DIRECTORY_SEPARATOR );

require_once(JPATH_BASE.DS.'includes'.DS.'defines.php' );
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php' );
require_once('mue.php');

$app =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$cfg=MUEHelper::getConfig();
$date = new JDate('now');

$action = JRequest::getVar('type');

if ($action == "unsubscribe") {
	$data = JRequest::getVar('data', array(), 'post', 'array');
	$q = 'SELECT id FROM #__users WHERE email = "'.$data['email'].'"';
	$db->setQuery($q);
	if ($user = $db->loadResult()) {
		$q2 = 'SELECT uf_id FROM #__mue_ufields WHERE uf_default = "'.$data['list_id'].'"';
		$db->setQuery($q2);
		if ($fid = $db->loadResult()) {
			$q3 = 'UPDATE #__mue_users SET usr_data = 0 WHERE usr_user = '.$user.' && usr_field = '.$fid;
			$db->setQuery($q3);
			if ($db->query()) {
				$usernotes = $date->toSql(true)." MailChimp Unsubscribe from List #".$data['list_id']."\r\n";
				$q4 = 'UPDATE #__mue_usergroup SET userg_notes = CONCAT(userg_notes,"'.$usernotes.'") WHERE userg_user = '.$user;
				$db->setQuery($q4);
				$db->query();
			}
		}
	}
}

if ($action == "subscribe") {
	$data = JRequest::getVar('data', array(), 'post', 'array');
	$q = 'SELECT id FROM #__users WHERE email = "'.$data['email'].'"';
	$db->setQuery($q);
	if ($user = $db->loadResult()) {
		$q2 = 'SELECT uf_id FROM #__mue_ufields WHERE uf_default = "'.$data['list_id'].'"';
		$db->setQuery($q2);
		if ($fid = $db->loadResult()) {
			$q3 = 'UPDATE #__mue_users SET usr_data = 1 WHERE usr_user = '.$user.' && usr_field = '.$fid;
			$db->setQuery($q3);
			if ($db->query()) {
				$usernotes = $date->toSql(true)." MailChimp Subscribe to List #".$data['list_id']."\r\n";
				$q4 = 'UPDATE #__mue_usergroup SET userg_notes = CONCAT(userg_notes,"'.$usernotes.'") WHERE userg_user = '.$user;
				$db->setQuery($q4);
				$db->query();
			}
		}
	}
}


