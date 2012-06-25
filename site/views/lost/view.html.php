<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class MUEViewLost extends JView
{
	protected $params;
	protected $user;
	protected $redirurl;

	/**
	 * Method to display the view.
	 *
	 * @param	string	The template file to include
	 * @since	1.20
	 */
	public function display($tpl = null) {
		// Get the view data.
		$this->user		= JFactory::getUser();
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		$layout = $this->getLayout();
		
		switch($layout) {
			case "default": 
				$this->lostInfo();
				break;
			case "infosent": 
				$this->infoSent();
				break;
			case "sendinfo": 
				$this->sendInfo();
				break;
		}
		parent::display($tpl);
	}

	protected function lostInfo() {
		
	}
	

	protected function sendInfo() {
		$model =& $this->getModel();
		$app=Jfactory::getApplication();
		if ($model->sendInfo()) {
			$app->redirect('index.php?option=com_mue&view=lost',"Information Sent to provided email address");
		} else {
			$app->redirect('index.php?option=com_mue&view=lost',$model->getError());
		}
	}
	
	protected function infoSent() {
		
		
	}
}
