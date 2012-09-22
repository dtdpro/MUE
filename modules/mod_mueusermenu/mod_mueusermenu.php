<?php

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once

$user	= JFactory::getUser();

require JModuleHelper::getLayoutPath('mod_mueusermenu', $params->get('layout', 'default'));
