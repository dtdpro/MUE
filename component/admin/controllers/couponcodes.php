<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

class MUEControllerCouponcodes extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_COUPONCODE";
	
	public function getModel($name = 'Couponcode', $prefix = 'MUEModel', $config = [])
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}