<?php

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

// Load helper
require_once('components/com_mue'.DS.'helpers'.DS.'mue.php');


$params->def('greeting', 1);

$type	= modMUELoginHelper::getType();
$return	= modMUELoginHelper::getReturnURL($params, $type);
$user	= JFactory::getUser();

require JModuleHelper::getLayoutPath('mod_muelogin', $params->get('layout', 'default'));
