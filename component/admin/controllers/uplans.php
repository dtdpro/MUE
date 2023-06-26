<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controlleradmin' );

class MUEControllerUplans extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_UPLAN";
	
	public function getModel($name = 'Uplan', $prefix = 'MUEModel', $config = [])
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}
?>
