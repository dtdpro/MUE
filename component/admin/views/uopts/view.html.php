<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUOpts extends JViewLegacy
{
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$this->state		= $this->get('State');
		$flist = $this->get('Fields');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;
		$this->flist = $flist;
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
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_UOPTS'), 'mue');
		JToolBarHelper::addNew('uopt.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('uopt.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::custom('uopts.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('uopts.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('uopts.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'uopts.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else  {
			JToolBarHelper::trash('uopts.trash');
		}
		JToolBarHelper::divider();
		JToolBarHelper::back('COM_MUE_TOOLBAR_FIELDS','index.php?option=com_mue&view=ufields');
	}
	
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_MUE_MANAGER_UOPTS'));
	}
}
