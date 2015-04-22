<?php

// No direct access to this file
defined('_JEXEC') or die;

abstract class MUEHelper
{
	public static function addSubmenu($submenu,$title='')
	{
		if ($submenu == 'uopts') {
            JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_FIELDSRETURN'),'index.php?option=com_mue&view=ufields',$submenu == 'ufields');
            JHtmlSidebar::addEntry('<span class="nav-header">'.$title.'</span>');
        }
        JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_MUE'), 'index.php?option=com_mue', $submenu == 'mue');
		JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_UGROUPS'), 'index.php?option=com_mue&view=ugroups', $submenu == 'ugroups');
		JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_UFIELDS'), 'index.php?option=com_mue&view=ufields', $submenu == 'ufields');
		JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_TALLY'), 'index.php?option=com_mue&view=tally', $submenu == 'tally');
		JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_UPLANS'), 'index.php?option=com_mue&view=uplans', $submenu == 'uplans');
		JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_USERS'), 'index.php?option=com_mue&view=users', $submenu == 'users');
		JHtmlSidebar::addEntry(JText::_('COM_MUE_SUBMENU_USERSUBS'), 'index.php?option=com_mue&view=usersubs', $submenu == 'usersubs');
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
