<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

use Joomla\CMS\Session\Session;


class MUEModelLogin extends JModelLegacy
{
	function loginUser() {
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app=Jfactory::getApplication();
		$input = $app->input;
		//Login User
		$credentials['username']  = $input->get('login_user', '', 'USERNAME');
		$credentials['password']  = $input->get('login_pass', '', 'RAW');
		$options = array();
		$options['remember'] = true;
		if ($app->login($credentials, $options)) return true;
		else return false;
	}
	
	function getUserGroups() {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$aid = $user->getAuthorisedViewLevels();
		$qd = 'SELECT ug.* FROM #__mue_ugroups as ug WHERE ug.access IN ('.implode(",",$aid).')';
		$qd.= ' ORDER BY ug.ordering';
		$db->setQuery( $qd ); 
		$ugroups = $db->loadObjectList();
		return $ugroups;
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}


}
