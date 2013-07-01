<?php
/**
 * MUE User Extension Component
 * (C) 2008-2011 DtD Productions
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once (JPATH_COMPONENT.'/controller.php');

// Require specific controller if requested
if($controller = JRequest::getVar('controller')) {
	require_once (JPATH_COMPONENT.'/controllers/'.$controller.'.php');
}

// Load helper
require_once(JPATH_COMPONENT.'/helpers/mue.php');

// Load StyleSheet for template, based on config
$doc = &JFactory::getDocument();
//jQuery
if (version_compare(JVERSION, '3.0.0', '>=')) {
	JHtml::_('jquery.framework');
} else if (!JFactory::getApplication()->get('jquery')) {
	JFactory::getApplication()->set('jquery', true);
	$doc->addScript('media/com_mue/scripts/jquery.js');	
}
$doc->addScript('media/com_mue/scripts/jquery.validate.js');
$doc->addScript('media/com_mue/scripts/additional-methods.js');
$doc->addScript('media/com_mue/scripts/jquery.simplemodal.js');


// Create the controller
$classname	= 'MUEController'.$controller;
$controller = new $classname( );
JPluginHelper::importPlugin('mue');

// Perform the Request task
$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
?>

