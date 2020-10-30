<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewPM extends JViewLegacy
{
	
	protected $state;
	protected $item;
	protected $form;
	
	public function display($tpl = null) 
	{

		$app=Jfactory::getApplication();
		$msgid = $app->input->get('msg_id');
		$model = $this->getModel();
		// get the Data
		$this->item = $model->getMessage($msgid);
		
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
		JToolBarHelper::title("View Message");

		$tbar = JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','cancel','JTOOLBAR_CLOSE','index.php?option=com_mue&view=pms');
	}
}
