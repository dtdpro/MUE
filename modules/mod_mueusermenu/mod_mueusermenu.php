<?php

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

// Include the syndicate functions only once
// Load helper
require_once('components/com_mue'.DS.'helpers'.DS.'mue.php');
$user	= JFactory::getUser();

require JModuleHelper::getLayoutPath('mod_mueusermenu', $params->get('layout', 'default'));
