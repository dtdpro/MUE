<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MUEViewTally extends JViewLegacy
{
	function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;
		JToolBarHelper::title(   JText::_( 'MUE User Fields - Results' ), 'mue' );
		$model = $this->getModel('tally');
		// Set the submenu
		if (JVersion::MAJOR_VERSION == 3) MUEHelper::addSubmenu($jinput->getVar('view'));
        $this->sidebar = JHtmlSidebar::render();
        // Get data from the model
        $this->fdata=$model->getFields();
		parent::display($tpl);
	}
}
