<?php

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

// Load helper
require_once('components/com_mue/helpers/mue.php');


$params->def('greeting', 1);

$type	= modMUELoginHelper::getType();
$return	= modMUELoginHelper::getReturnURL($params, $type);
$user	= JFactory::getUser();

require JModuleHelper::getLayoutPath('mod_muelogin', $params->get('layout', 'default'));
