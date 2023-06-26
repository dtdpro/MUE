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
				break;
		}
		parent::display($tpl);
	}

	protected function activateUser() {
		$model = $this->getModel();
		$token = JFactory::getApplication()->input->get('token');
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		if (!$status = $model->activateUser($token)) {
			$app->enqueueMessage($model->getError(),'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=login&layout=login', false));
		} else {
			if ($status == 'active') {
				$return = "";
				if ( $muecfg->subscribe ) {
					$return = JRoute::_( 'index.php?option=com_mue&view=subscribe' );
				}
				$app->enqueueMessage(JText::_('COM_MUE_REGISTRATION_ACTIVATE_SUCCESS'));
				$redirectUrl = 'index.php?option=com_mue&view=login&layout=login';
				if ($return) {
					$redirectUrl = $redirectUrl.'&return='.base64_encode($return);
				}
				$app->redirect(JRoute::_($redirectUrl, false));
			} else if ($status == 'adminactivate') {
				$this->completeMessage = JText::_('COM_MUE_REGISTRATION_VERIFY_SUCCESS');
			}
		}

	}

	protected function adminActivateUser() {
		$model = $this->getModel();
		$token = JFactory::getApplication()->input->get('token');
		$app=Jfactory::getApplication();
		if (!$status = $model->adminActivateUser($token)) {
			$app->enqueueMessage($model->getError(),'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=activation&layout=complete', false));
		} else {
			$this->completeMessage = JText::_('COM_MUE_REGISTRATION_ADMINACTIVATE_SUCCESS');
		}

	}
}
?>
