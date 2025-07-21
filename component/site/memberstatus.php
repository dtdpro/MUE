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


$db  = JFactory::getDBO();
$cfg=MUEHelper::getConfig();

$key = "AzvECarAmnR8ogaAT14xOqkBYy9Ry8ktnr1gh7wbGKj4NE7s8Ctnr1gh7wbGKj4N";

if ($key == getBearerToken()) {
    if ($email = getEmail()) {
        $is_member = false;

        // Find user by email
        $query = $db->getQuery(true);
        $query->select('u.*');
        $query->from('#__users as u');
        $query->where('u.email = "'.$db->escape($email).'"');
        $db->setQuery($query);
        $user = $db->loadObject();

        if ($user) {
            $userid = $user->id;

            // First Name
            $query = $db->getQuery(true);
            $query->select('ud.usr_data');
            $query->from('#__mue_users as ud');
            $query->where('ud.usr_user = ' . $userid);
            $query->where('ud.usr_field = 1');
            $db->setQuery($query);
            $firstName = $db->loadResult();

            // Last Name
            $query = $db->getQuery(true);
            $query->select('ud.usr_data');
            $query->from('#__mue_users as ud');
            $query->where('ud.usr_user = ' . $userid);
            $query->where('ud.usr_field = 2');
            $db->setQuery($query);
            $lastName = $db->loadResult();

            $db = JFactory::getDBO();
            $query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
            $query .= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
            $query .= 'WHERE s.usrsub_status IN ("completed","accepted") && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="' . $userid . '" ';
            $query .= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
            $db->setQuery($query);
            $sub = $db->loadObject();

            if ($sub) {
                $is_member = true;
            }

            $data = [];
            $data['is_member'] = $is_member;
            $data['first_name'] = $firstName;
            $data['last_name'] = $lastName;
            $data['id'] = $user->id;

            // HTTP Headers
            $app = JFactory::getApplication();
            $app->clearHeaders();
            $app->setHeader("Pragma", "public");
            $app->setHeader('Cache-Control', 'no-cache, must-revalidate', true);
            $app->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true);
            $app->setHeader('Content-Type', 'application/json', true);
            $app->setHeader('Content-Transfer-Encoding', 'binary', true);
            $app->sendHeaders();

            // content
            echo json_encode($data);

            // stop
            $app->close();
        } else {
            // no user found
            http_response_code(404);
        }
    } else {
        // no valid email provided
        http_response_code(400);
    }
} else {
    // no key
    http_response_code(403);
}



function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function getEmail() {
    $email = $_GET["email"];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return $email;
}
