<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );
require_once ('mue.php');

$mainframe =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$user = &JFactory::getUser();
$config=MUEHelper::getConfig();
$numsubs=count(MUEHelper::getUserSubs());
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
	$center_lat = JRequest::getVar('lat');
	$center_lng = JRequest::getVar('lng');
	$radius = JRequest::getInt('radius');
	$limit = JRequest::getInt('limit',20);
	$data = JRequest::getVar('jform', array(), 'post', 'array');
	$sdata = array();
	foreach ($data as $d) {
		if (is_array($d)) {
			foreach ($d as $do) $sdata[] = 'ud.ud_searchinfo LIKE "%'.$db->espcae(trim($do)).'%"'; 
		} else if (trim($d)) {
			$sdata[] = 'ud.ud_searchinfo LIKE "%'.$db->espcae(trim($d)).'%"';
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
	$query .= "ORDER BY distance LIMIT 0 , ".$limit;
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
	}}
	echo $dom->saveXML();
}

