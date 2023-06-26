<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MUEViewMue extends JViewLegacy
{
	function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;

		JToolBarHelper::title(   JText::_( 'MUE User Extension' ), 'mue' );
		JToolBarHelper::preferences('com_mue');
        // Set the submenu
		if (JVersion::MAJOR_VERSION == 3) MUEHelper::addSubmenu($jinput->getVar('view'));

		$this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
	}
}
