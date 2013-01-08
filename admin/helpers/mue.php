<?php

// No direct access to this file
defined('_JEXEC') or die;

abstract class MUEHelper
{
	public static function addSubmenu($submenu) 
	{
		JSubMenuHelper::addEntry(JText::_('COM_MUE_SUBMENU_MUE'), 'index.php?option=com_mue', $submenu == 'mue');
		JSubMenuHelper::addEntry(JText::_('COM_MUE_SUBMENU_UGROUPS'), 'index.php?option=com_mue&view=ugroups', $submenu == 'ugroups');
		JSubMenuHelper::addEntry(JText::_('COM_MUE_SUBMENU_UFIELDS'), 'index.php?option=com_mue&view=ufields', $submenu == 'ufields');
		JSubMenuHelper::addEntry(JText::_('COM_MUE_SUBMENU_USERS'), 'index.php?option=com_mue&view=users', $submenu == 'users');
		JSubMenuHelper::addEntry(JText::_('COM_MUE_SUBMENU_USERSUBS'), 'index.php?option=com_mue&view=usersubs', $submenu == 'usersubs');
	}
	
	static function getStateOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JENABLED'));
		$options[] = JHtml::_('select.option', '1', JText::_('JDISABLED'));
		
		return $options;
	}
	
	function getConfig() {
		$config = JComponentHelper::getParams('com_mue'); 
		$cfg = $config->toObject();
		return $cfg;
	}
}
