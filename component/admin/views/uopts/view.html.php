<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUopts extends JViewLegacy
{
	
	function display($tpl = null) 
	{
		$jinput = JFactory::getApplication()->input;
		// Get data from the model
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
		$this->field = $this->get('Field');
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
		JToolBarHelper::title(JText::_('COM_MUE_MANAGER_UOPTS'), 'mue');
		JToolBarHelper::addNew('uopt.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('uopt.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::custom('uopts.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('uopts.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('uopts.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		if ($this->state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'uopts.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else  {
			JToolBarHelper::trash('uopts.trash');
		}
		JToolbarHelper::link('index.php?option=com_mue&view=ufields','Return to Fields','chevron-left');
	}

    protected function getSortFields()
    {
        return array(
            'o.ordering'     => JText::_('JGRID_HEADING_ORDERING')
        );
    }
}
