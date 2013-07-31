<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');


class MUEControllerUgroups extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_UGROUP";
	
	public function getModel($name = 'Ugroup', $prefix = 'MUEModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
