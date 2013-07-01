<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUser extends JViewLegacy
{
	public function display($tpl = null) 
	{
		// get the Data
		$fields = $this->get('Fields');
		$usergroups = $this->get('UserGroups');
		$item = $this->get('Item');
		$script = $this->get('Script');
		$model=$this->getModel();
		$this->groups		= $model->getAssignedGroups($item->usr_user);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->usergroups = $usergroups;
		$this->fields = $fields;
		$this->item = $item;
		$this->script = $script;

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		$isNew = $this->item->usr_user == 0;
		JToolBarHelper::title($isNew ? JText::_('COM_MUE_MANAGER_USER_NEW') : JText::_('COM_MUE_MANAGER_USER_EDIT'), 'mue');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			JToolBarHelper::apply('user.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('user.save', 'JTOOLBAR_SAVE');
			ToolBarHelper::custom('user.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::cancel('user.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::apply('user.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('user.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('user.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::cancel('user.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	
	protected function setDocument() 
	{
		$isNew = $this->item->usr_user == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_MUE_USER_CREATING') : JText::_('COM_MUE_USER_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_mue/views/user/submitbutton.js");
		JText::script('COM_MUE_USER_ERROR_UNACCEPTABLE');
	}
}
