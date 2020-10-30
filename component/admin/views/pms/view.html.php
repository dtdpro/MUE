<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');


class MUEViewPMs extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null) 
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		

		MUEHelper::addSubmenu(JRequest::getVar('view'));
		
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();	

		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		JToolBarHelper::title("Private Messages", 'mue');
		JToolBarHelper::trash('pms.trashmsgs');
		JToolBarHelper::deleteList("Do you want to permanently delete these messages?",'pms.deletemsgs');
		
		JHtmlSidebar::setAction('index.php?option=com_mue&view=pms');
		
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_PUBLISHED'),'filter_status',JHtml::_('select.options', [['value'=>"new",'text'=>"New"],['value'=>"read",'text'=>"Read"],['value'=>"unsent",'text'=>"Unsent"],['value'=>"trashed",'text'=>"Trashed"],['value'=>"spam",'text'=>"SPAM"]], 'value', 'text', $this->state->get('filter.status'), true));
		
	}
}
