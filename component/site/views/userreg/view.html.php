<?php

jimport( 'joomla.application.component.view');

use Joomla\CMS\Session\Session;

class MUEViewUserreg extends JViewLegacy
{
	var $return="";
	
	public function display($tpl = null)
	{

		$config=MUEHelper::getConfig();
		$this->input = JFactory::getApplication()->input;
		if ($config->rc_config == "visible" || $config->rc_config == "invisible") {
			$doc = JFactory::getDocument();
			$doc->addScript('https://www.google.com/recaptcha/api.js');
		}
		$layout = $this->getLayout();
		$this->return = base64_decode(JFactory::getApplication()->input->get('return', '', 'POST', 'BASE64'));
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		switch($layout) {
			case "default": 
				$this->pickGroup();
				break;
			case "groupuser": 
				$this->setGroup();
				break;
			case "regform": 
				$this->showForm();
				break;
			case "reguser": 
				$this->addUser();
				break;
			case "useractivate":
				$this->activateUser();
				break;
			case "adminactivate":
				$this->adminActivateUser();
				break;
		}
		parent::display($tpl);
	}
	
	protected function pickGroup() {
		$app=Jfactory::getApplication();
		$model = $this->getModel();
		$groups=$model->getUserGroups();
		$model->getPresetFields();
		$this->groups=$groups;
		if (count($groups) == 1) {
			$app->setUserState('mue.userreg.groupid',$groups[0]->ug_id);
			$app->setUserState('mue.userreg.return',$this->return);
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg&layout=regform'));
		}
	}
	
	protected function setGroup() {
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app=Jfactory::getApplication();
		$app->setUserState('mue.userreg.groupid',$this->input->get('groupid'));
		$app->setUserState('mue.userreg.return',$this->return); 
		$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg&layout=regform'));
		
	}
	
	protected function showForm() {
		$model = $this->getModel();
		$app=Jfactory::getApplication();
		$groupid = $app->getUserState('mue.userreg.groupid');
		if (!$this->return) $this->return = $app->getUserState('mue.userreg.return');
		if ($groupid) {
			$groupinfo = $model->getUserGroups($groupid);
			$userfields=$model->getUserFields($groupid);
			if (count($model->getUserGroups()) == 1) {
				$this->single_group = true;
			} else {
				$this->single_group = false;
			}
			$this->groupinfo=$groupinfo;
			$this->groupid=$groupid;
			$this->userfields=$userfields;
			$this->retry=$this->input->get('retry');
		} else {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg'));
		}
	}
	
	protected function addUser() {
		$model = $this->getModel();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$data = $this->input->get('jform', [], 'post', 'array');
		$groupid = $data['userGroupID'];
		if (!$status = $model->save()) {
			$app->setUserState('mue.userreg.groupid',$groupid);
			$app->enqueueMessage($model->getError(), 'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg&layout=regform&retry=1&groupid='.$groupid));
		} else {
			if ($status == 'userlink') {
				$this->setLayout('complete');
				$this->completeMessage = JText::_('COM_MUE_REGISTRATION_COMPLETE_ACTIVATE');
			} else if ($status == 'adminlink') {
				$this->setLayout('complete');
				$this->completeMessage = JText::_('COM_MUE_REGISTRATION_COMPLETE_VERIFY');
			} else {
				$redir = $this->return;
				if ( ! $redir ) {
					$redir = JRoute::_( 'index.php?option=com_mue&view=user&layout=profile' );
				}
				if ( $muecfg->subscribe ) {
					$redir = JRoute::_( 'index.php?option=com_mue&view=subscribe' );
					$app->setUserState( 'mue.userreg.return', $this->return );
				}
				$app->redirect($redir);
			}
		}		
	}

	protected function activateUser() {
		$model = $this->getModel();
		$token = $this->input->get('token');
		$app=Jfactory::getApplication();
		if (!$status = $model->activateUser($token)) {
			$app->enqueueMessage(JText::_('COM_MUE_REGISTRATION_ACTIVATE_SUCCESS'));
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg&layout=complete', false));
		} else {
			if ($status == 'active') {
				$app->enqueueMessage($model->getError(), 'error');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=login&layout=login', false));
			} else if ($status == 'adminactivate') {
				$this->setLayout('complete');
				$this->completeMessage = JText::_('COM_MUE_REGISTRATION_VERIFY_SUCCESS');
			}
		}

	}

	protected function adminActivateUser() {
		$model = $this->getModel();
		$token = $this->input->get('token');
		$app=Jfactory::getApplication();
		if (!$status = $model->adminActivateUser($token)) {
			$app->enqueueMessage($model->getError(), 'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg&layout=complete', false),);
		} else {
			$this->setLayout('complete');
			$this->completeMessage = JText::_('COM_MUE_REGISTRATION_ADMINACTIVATE_SUCCESS');
		}

	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}
}
?>
