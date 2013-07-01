<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../..' );
define( 'DS', DIRECTORY_SEPARATOR );

require_once(JPATH_BASE.DS.'includes'.DS.'defines.php' );
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php' );

$app =& JFactory::getApplication('site');
$planid = JRequest::getVar( 'plan' );

if ($planid)
{
	$app->redirect(JURI::base( true ).'/index.php?option=com_mue&view=subscribe&layout=cartops&plan='.$planid.'&tmpl=raw');
} else {
	echo "Choose a Plan";
}





