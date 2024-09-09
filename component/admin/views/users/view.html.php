<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUsers extends JViewLegacy
{
	function display($tpl = null) 
	{
		$jinput = JFactory::getApplication()->input;

		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->ugroups = $this->get('UGroups');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		$this->usergroups = array();
		foreach ($this->ugroups as $u) {
			$this->usergroups[$u->value] = $u->text;
		}

        // Set the submenu
		if (JVersion::MAJOR_VERSION == 3) MUEHelper::addSubmenu($jinput->getVar('view'));

        $this->sidebar = JHtmlSidebar::render();

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$state	= $this->get('State');
		$cfg=MUEHelper::getConfig();
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_USERS'), 'mue');
		JToolBarHelper::addNew('user.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('user.edit', 'JTOOLBAR_EDIT');	
		JToolBarHelper::divider();	
		JToolBarHelper::unpublish('users.block', 'COM_MUE_TOOLBAR_BLOCK', true);
		JToolBarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_MUE_TOOLBAR_UNBLOCK', true);
		JToolBarHelper::divider();
		$tbar = JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','export','Export CSV','index.php?option=com_mue&view=users&format=csv');
		if ($cfg->subscribe) JToolBarHelper::custom('users.syncsubs', 'refresh.png', 'refresh_f2.png', 'COM_MUE_TOOLBAR_SYNCSUB', false);
		if ($cfg->userdir) JToolBarHelper::custom('users.syncmemberdb', 'refresh.png', 'refresh_f2.png', 'COM_MUE_TOOLBAR_SYNCMEMBERDB', false);
		JToolBarHelper::divider();
		
		JHtml::_('bootstrap.modal', 'collapseModal');
		$title = JText::_('JTOOLBAR_BATCH');
		// Instantiate a new JLayoutFile instance and render the batch button
		$layout = new JLayoutFile('joomla.toolbar.batch');
		$dhtml = $layout->render(array('title' => $title));
		
		JToolBarHelper::preferences('com_mue');
	}

	protected function getSortFields()
	{
		return array(
				'u.name' => JText::_('COM_USERS_HEADING_NAME'),
				'u.username' => JText::_('JGLOBAL_USERNAME'),
				'u.block' => JText::_('COM_MUE_USER_HEADING_ENABLED'),
				'g.ug_name' => JText::_('COM_MUE_USER_HEADING_GROUP'),
				'g.userg_siteurl' => JText::_('COM_MUE_USER_HEADING_JOINSITE'),
				'u.email' => JText::_('JGLOBAL_EMAIL'),
				'u.lastvisitDate' => JText::_('COM_USERS_HEADING_LAST_VISIT_DATE'),
				'u.registerDate' => JText::_('COM_USERS_HEADING_REGISTRATION_DATE'),
				'ug.userg_update' => JText::_('COM_MUE_USER_HEADING_UPDATE'),
				'u.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
