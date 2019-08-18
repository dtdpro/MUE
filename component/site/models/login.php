<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

class MUEModelLogin extends JModelLegacy
{
	function loginUser() {
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app=Jfactory::getApplication();
		//Login User
		$options = array();
		$credentials['username'] = JRequest::getVar("login_user");
		$credentials['password'] = JRequest::getVar("login_pass");
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

}
