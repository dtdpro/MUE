<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUsersubs extends JViewLegacy
{
	function display($tpl = null) 
	{
		$jinput = JFactory::getApplication()->input;

		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->plist = $this->get('Plans');
		$this->paystatuses = $this->get('PayStatuses');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Set the toolbar
		$this->addToolBar();

        // Set the submenu
        if (JVersion::MAJOR_VERSION == 3) MUEHelper::addSubmenu($jinput->getVar('view'));
        $this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$state	= $this->get('State');
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_USERSUBS'), 'mue');
		JToolBarHelper::addNew('usersub.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('usersub.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'usersubs.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		$tbar = JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','export','Export CSV','index.php?option=com_mue&view=usersubs&format=csv');

		JHtmlSidebar::setAction('index.php?option=com_mue&view=usersubs');


	}
}
