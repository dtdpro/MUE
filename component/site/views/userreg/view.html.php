<?php

jimport( 'joomla.application.component.view');


class MUEViewUserreg extends JViewLegacy
{
	var $return="";
	
	public function display($tpl = null)
	{

		$config=MUEHelper::getConfig();
		if ($config->rc_config == "visible" || $config->rc_config == "invisible") {
			$doc = JFactory::getDocument();
			$doc->addScript('https://www.google.com/recaptcha/api.js');
		}
		$layout = $this->getLayout();
		//$this->return = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
		$this->return = base64_decode(JRequest::getVar('return', null));
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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app=Jfactory::getApplication();
		$app->setUserState('mue.userreg.groupid',JRequest::getInt('groupid')); 
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
			$this->retry=JRequest::getInt('retry');
		} else {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg'));
		}
	}
	
	protected function addUser() {
		$model = $this->getModel();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$groupid = $data['userGroupID'];
		if (!$model->save()) {
			$app->setUserState('mue.userreg.groupid',$groupid); 
			$app->redirect(JRoute::_('index.php?option=com_mue&view=userreg&layout=regform&retry=1&groupid='.$groupid),$model->getError(),'error');
		} else {
			$redir = $this->return;
			if (!$redir) $redir=JRoute::_('index.php?option=com_mue&view=user&layout=profile');
			if ($muecfg->subscribe) {
				$redir=JRoute::_('index.php?option=com_mue&view=subscribe');
				$app->setUserState('mue.userreg.return',$this->return);
			}
			$app->redirect($redir);
		}		
	}
}
?>
