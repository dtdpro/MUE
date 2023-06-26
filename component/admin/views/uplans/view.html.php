<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUplans extends JViewLegacy
{
	function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$this->state		= $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;

        // Set the submenu
		if (JVersion::MAJOR_VERSION == 3) MUEHelper::addSubmenu($jinput->getVar('view'));
        $this->sidebar = JHtmlSidebar::render();

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
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_UPLANS'), 'mue');
		JToolBarHelper::addNew('uplan.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('uplan.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::divider();
		JToolBarHelper::custom('uplans.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('uplans.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'uplans.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else  {
			JToolBarHelper::trash('uplans.trash');
		}
	}
	
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_MUE_MANAGER_UPLANS'));
	}
    protected function getSortFields()
    {
        return array(
            'p.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
            'p.access'     => JText::_('JGRID_HEADING_ACESS')
        );
    }
}
