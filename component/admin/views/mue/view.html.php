<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MUEViewMue extends JViewLegacy
{
	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'MUE User Extension' ), 'mue' );
		JToolBarHelper::preferences('com_mue');
        // Set the submenu
        MUEHelper::addSubmenu(JRequest::getVar('view'));
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
	}
}
