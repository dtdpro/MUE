<?php

jimport( 'joomla.application.component.view');


class MUEViewActivation extends JViewLegacy
{
	var $return="";
	
	public function display($tpl = null)
	{

		$layout = $this->getLayout();
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		switch($layout) {
			case "useractivate":
				$this->activateUser();
				break;
			case "adminactivate":
				$this->adminActivateUser();
				break;
			case "complete":
				$this->setLayout("deault");
				break;
		}
		parent::display($tpl);
	}

	protected function activateUser() {
		$model = $this->getModel();
		$token = JRequest::getVar('token');
		$app=Jfactory::getApplication();
		if (!$status = $model->activateUser($token)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=activation&layout=complete', false),$this->getError(),'error');
		} else {
			if ($status == 'active') {
				$app->redirect(JRoute::_('index.php?option=com_mue&view=login&layout=login', false),JText::_('COM_MUE_REGISTRATION_ACTIVATE_SUCCESS'));
			} else if ($status == 'adminactivate') {
				$this->setLayout('default');
				$this->completeMessage = JText::_('COM_MUE_REGISTRATION_VERIFY_SUCCESS');
			}
		}

	}

	protected function adminActivateUser() {
		$model = $this->getModel();
		$token = JRequest::getVar('token');
		$app=Jfactory::getApplication();
		if (!$status = $model->adminActivateUser($token)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=activation&layout=complete', false),$this->getError(),'error');
		} else {
			$this->setLayout('default');
			$this->completeMessage = JText::_('COM_MUE_REGISTRATION_ADMINACTIVATE_SUCCESS');
		}

	}
}
?>
