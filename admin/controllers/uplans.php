<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controlleradmin' );

class MUEControllerUPlans extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_UPlan";
	
	public function getModel($name = 'UPlan', $prefix = 'MUEModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}
?>
