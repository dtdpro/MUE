<?php
// Set flag that this is a parent file

define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );
define('JPATH_CORE', JPATH_BASE);

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );
require_once ('mue.php');

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
$user = JFactory::getUser();
$config=MUEHelper::getConfig();
$numsubs=count(MUEHelper::getUserSubs());
$input = JFactory::getApplication()->input;
$canview=true;		
if ($config->subscribe && $config->usrdir_sub) {
	if ($numsubs) {
		$sub=MUEHelper::getActiveSub();
		if (!$sub) {
			$canview=false;
		}
	} else {
		$canview=false;
	}
}

if ($user->id && $canview) {
	// Get parameters from URL
	$center_lat = $input->get('lat');
	$center_lng = $input->get('lng');
	$radius = $input->get('radius');
	$limit = $input->get('limit',20);
	$data = $input->get('jform', array(), 'post', 'array');
	$sdata = array();
	foreach ($data as $d) {
		if (is_array($d)) {
			foreach ($d as $do) $sdata[] = 'ud.ud_searchinfo LIKE "%'.$db->espcae(trim($do)).'%"'; 
		} else if (trim($d)) {
			$sdata[] = 'ud.ud_searchinfo LIKE "%'.$db->escape(trim($d)).'%"';
		}
	} 
	
	// Start XML file, create parent node
	$dom = new DOMDocument("1.0");
	$node = $dom->createElement("markers");
	$parnode = $dom->appendChild($node);
	
	// Search the rows in the markers table
	$query  = "SELECT ud.*,u.*, ( 3959 * acos( cos( radians('".$center_lat."') ) * cos( radians( ud.ud_lat ) ) * cos( radians( ud.ud_lon ) - radians('".$center_lng."') ) + sin( radians('".$center_lat."') ) * sin( radians( ud.ud_lat ) ) ) ) AS distance FROM #__mue_userdir as ud ";
	$query .= "LEFT JOIN #__users AS u ON ud_user = u.id ";
	if (count($sdata)) {
		$query .= "WHERE ";
		$query .= implode(" && ",$sdata);
		$query .= " ";
	}
	$query .= "HAVING distance < '".$radius."' ";
	$query .= "ORDER BY distance ";
	if ($limit) $query .= "LIMIT 0 , ".$limit;
	$db->setQuery($query); 
	$result = $db->loadObjectList();
	
	header("Content-type: text/xml");
	
	// Iterate through the rows, adding XML nodes for each
	if ($result) { foreach ($result as $row) {
		$node = $dom->createElement("marker");
		$newnode = $parnode->appendChild($node);
		$newnode->setAttribute("name", $row->name);
		$newnode->setAttribute("lat", $row->ud_lat);
		$newnode->setAttribute("lng", $row->ud_lon);
		$newnode->setAttribute("distance", $row->distance);
		$newnode->setAttribute("userinfo", $row->ud_userinfo);
		$newnode->setAttribute("usertags", $row->ud_usertags);
		$newnode->setAttribute("udid", $row->ud_id);
	}}
	echo $dom->saveXML();
}

