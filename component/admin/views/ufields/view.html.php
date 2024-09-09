<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUfields extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

	function display($tpl = null) 
	{
		$jinput = JFactory::getApplication()->input;
		// Get data from the model
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
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
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_UFIELDS'), 'mue');
		JToolBarHelper::addNew('ufield.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('ufield.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::custom('ufields.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('ufields.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('ufields.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		if ($state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'ufields.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else  {
			JToolBarHelper::trash('ufields.trash');
		}		
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_mue');
	}


    protected function getSortFields()
    {
        return array(
            'f.ordering'     => JText::_('JGRID_HEADING_ORDERING')
        );
    }
}
