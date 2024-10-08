<?php
/**
 * MUE entry point file for ContinuEd Admin Component
 * (C) 2012 DtD Productions
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_mue')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// require helper file
JLoader::register('MUEHelper', dirname(__FILE__) . '/helpers/mue.php');

// load composer packages
require JPATH_ROOT.'/components/com_mue/vendor/autoload.php';

//icon
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-48-mue {background-image: url(../media/com_mue/images/mue-48x48.png);}');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by vidrev
$controller = JControllerLegacy::getInstance('mue');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));


// Redirect if set by the controller
$controller->redirect();

?>
