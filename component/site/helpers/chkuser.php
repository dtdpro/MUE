<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$mainframe =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$user = &JFactory::getUser();

$data = JRequest::getVar('jform', array(), 'post', 'array'); 
$username = strtolower($db->getEscaped($data['username']));
$qn = 'SELECT username FROM #__users WHERE username="'.$username.'"';
$db->setQuery($qn); $hasuser = $db->loadResult();
if ($hasuser) {
	echo 'false';
} else {
	echo 'true';
}


