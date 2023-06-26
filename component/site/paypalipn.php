<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../..' );
define('JPATH_CORE', JPATH_BASE );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );

include_once 'lib/campaignmonitor.php';
include_once 'lib/mailchimp.php';

require_once('helpers/mue.php');
require_once('helpers/paypal.php');

use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

// Load up Joomla
if (JVersion::MAJOR_VERSION == 3) {
	$app = JFactory::getApplication( 'site' );
} else {
	if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_ROOT . '/media/vendor'))
	{
		echo file_get_contents(JPATH_ROOT . '/templates/system/build_incomplete.html');

		exit;
	}

	// Boot the DI container
	$container = \Joomla\CMS\Factory::getContainer();

	$container->alias('session.web', 'session.web.site')
	          ->alias('session', 'session.web.site')
	          ->alias('JSession', 'session.web.site')
	          ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
	          ->alias(\Joomla\Session\Session::class, 'session.web.site')
	          ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

	// Instantiate the application.
	$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);

	// Set the application as global app
	\Joomla\CMS\Factory::$application = $app;
}

$db  = JFactory::getDBO();
$cfg=MUEHelper::getConfig();
$paypal = new PayPalAPI($cfg->paypal_mode,$cfg->paypal_username,$cfg->paypal_password,$cfg->paypal_signature);
if ($userid = $paypal->ipnResponse()) {
	MUEHelper::updateUserSub($userid);
}





