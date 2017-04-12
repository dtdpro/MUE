<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../..' );
define( 'DS', DIRECTORY_SEPARATOR );

require_once(JPATH_BASE.DS.'includes'.DS.'defines.php' );
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php' );

//Load Bronto
JLoader::registerNamespace('Bronto_Api', 'lib/bronto/src');
JLoader::registerNamespace('Bronto_SoapClient', 'lib/bronto/src');
include_once 'lib/campaignmonitor.php';
include_once 'lib/mailchimp.php';

require_once('helpers'.DS.'mue.php');
require_once('helpers'.DS.'paypal.php');

$app =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$cfg=MUEHelper::getConfig();
$paypal = new PayPalAPI($cfg->paypal_mode,$cfg->paypal_username,$cfg->paypal_password,$cfg->paypal_signature);
if ($userid = $paypal->ipnResponse()) {
	MUEHelper::updateUserSub($userid);
}





