<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUsers extends JView
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
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_USERS'), 'continued');
		JToolBarHelper::addNew('user.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('user.edit', 'JTOOLBAR_EDIT');	
		JToolBarHelper::divider();	
		JToolBarHelper::unpublish('users.block', 'COM_MUE_TOOLBAR_BLOCK', true);
		JToolBarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_MUE_TOOLBAR_UNBLOCK', true);
		JToolBarHelper::divider();
		$tbar =& JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','export','Export CSV','index.php?option=com_mue&view=users&format=csv" target="_blank');
		$tbar->appendButton('Link','send','Email List','index.php?option=com_mue&view=users&format=csveml" target="_blank');
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
