<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../../..' );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );

$mainframe =& JFactory::getApplication('site');
$db  =& JFactory::getDBO();
$user = &JFactory::getUser();

$code = JRequest::getVar('discountcode'); 

if ($code) {
	$qc = 'SELECT * FROM #__mue_coupons WHERE published = 1 && access IN ('.implode(",",$user->getAuthorisedViewLevels()).') && cu_code = "'.$db->escape($code).'"';
	$qc .= ' && ((cu_start <= NOW() && cu_end >= NOW()) || cu_start = "0000-00-00")';
	$db->setQuery( $qc );
	$codeinfo = $db->loadObject();
	if (!$codeinfo) {
		echo 'false';
	} else {
		echo 'true';
	} 
} else { echo 'true'; }


