<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUgroup extends JViewLegacy
{
	public function display($tpl = null) 
	{
		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	protected function addToolBar() 
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->ug_id == 0;
		JToolBarHelper::title($isNew ? JText::_('COM_MUE_MANAGER_UGROUP_NEW') : JText::_('COM_MUE_MANAGER_UGROUP_EDIT'), 'mue');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			JToolBarHelper::apply('ugroup.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('ugroup.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('ugroup.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::cancel('ugroup.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::apply('ugroup.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('ugroup.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('ugroup.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::custom('ugroup.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			JToolBarHelper::cancel('ugroup.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	
	protected function setDocument() 
	{
		$isNew = $this->item->ug_id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_MUE_UGROUP_CREATING') : JText::_('COM_MUE_UGROUP_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_mue/views/ugroup/submitbutton.js");
		JText::script('COM_MUE_UGROUP_ERROR_UNACCEPTABLE');
	}
}
