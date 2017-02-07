<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewCouponcode extends JViewLegacy
{
	
	protected $state;
	protected $item;
	protected $form;
	
	public function display($tpl = null) 
	{
		// get the Data
		$this->state = $this->get('State');
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		$this->addToolBar();

		parent::display($tpl);
	}
	
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->cu_id == 0;
		JToolBarHelper::title($isNew ? JText::_('COM_MUE_MANAGER_COUPONCODE_NEW') : JText::_('COM_MUE_MANAGER_COUPONCODE_EDIT'), 'mue');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			JToolBarHelper::apply('couponcode.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('couponcode.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('couponcode.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::cancel('couponcode.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::apply('couponcode.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('couponcode.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('couponcode.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::custom('couponcode.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			JToolBarHelper::cancel('couponcode.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
