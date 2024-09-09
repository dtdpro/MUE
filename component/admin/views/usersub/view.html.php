<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUsersub extends JViewLegacy
{
	/**
	 * display method of view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
		$model = $this->getModel();

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

		if ($this->item->usrsub_id) {
			$this->history = $model->getHistory($this->item->usrsub_id);
		}
		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->usrsub_id == 0;
		JToolBarHelper::title($isNew ? JText::_('COM_MUE_MANAGER_USERSUB_NEW') : JText::_('COM_MUE_MANAGER_USERSUB_EDIT'), 'mue');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			JToolBarHelper::apply('usersub.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('usersub.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('usersub.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::cancel('usersub.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::apply('usersub.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('usersub.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('usersub.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::custom('usersub.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			JToolBarHelper::cancel('usersub.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
