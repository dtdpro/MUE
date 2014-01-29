<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );

$mainframe =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$user = &JFactory::getUser();

$data = JRequest::getVar('jform', array(), 'post', 'array'); 
$email = strtolower($db->escape($data['email']));
if (!$email) $email = $db->escape(strtolower(JRequest::getVar("newemail")));
$qn = 'SELECT username FROM #__users WHERE email="'.$email.'"';
$db->setQuery($qn); $hasuser = $db->loadResult();
if ($hasuser) {
	echo 'false';
} else {
	echo 'true';
}


