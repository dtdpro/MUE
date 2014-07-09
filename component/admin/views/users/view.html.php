<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUsers extends JViewLegacy
{
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$this->state		= $this->get('State');
		$ugroups = $this->get('UGroups');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;
		$this->ugroups = $ugroups;
		$this->usergroups = array();
		foreach ($this->ugroups as $u) {
			$this->usergroups[$u->value] = $u->text;
		}
		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
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
		$tbar =& JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','export','Export CSV','index.php?option=com_mue&view=users&format=csv');
		if ($cfg->subscribe) JToolBarHelper::custom('users.syncsubs', 'refresh.png', 'refresh_f2.png', 'COM_MUE_TOOLBAR_SYNCSUB', false);
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_mue');
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_MUE_MANAGER_USERS'));
	}
}
