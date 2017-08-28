<?php

jimport( 'joomla.application.component.view');


class MUEViewUserreg extends JViewLegacy
{
	var $return="";
	
	public function display($tpl = null)
	{

		$config=MUEHelper::getConfig();
		if ($config->rc_config == "visible" || $config->rc_config == "invisible") {
			$doc = &JFactory::getDocument();
			$doc->addScript('https://www.google.com/recaptcha/api.js');
		}
		$layout = $this->getLayout();
		$this->return = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
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
		$model =& $this->getModel();
		$groups=$model->getUserGroups();
		$this->assignRef('groups',$groups);
		if (count($groups) == 1) {
			$app->setUserState('mue.userreg.groupid',$groups[0]->ug_id);
			$app->setUserState('mue.userreg.return',$this->return);
			$app->redirect('index.php?option=com_mue&view=userreg&layout=regform&onegroup=1');
		}
	}
	
	protected function setGroup() {
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app=Jfactory::getApplication();
		$app->setUserState('mue.userreg.groupid',JRequest::getInt('groupid')); 
		$app->setUserState('mue.userreg.return',$this->return); 
		$app->redirect('index.php?option=com_mue&view=userreg&layout=regform&tmpl=raw');
		
	}
	
	protected function showForm() {
		$model =& $this->getModel();
		$app=Jfactory::getApplication();
		$groupid = $app->getUserState('mue.userreg.groupid');
		if (!$this->return) $this->return = $app->getUserState('mue.userreg.return');
		if ($groupid) {
			$groupinfo = $model->getUserGroups($groupid);
			$userfields=$model->getUserFields($groupid);
			if (count($model->getUserGroups()) == 1) {
				$this->show_header = true;
			} else {
				$this->show_header = false;
			}
			$this->assignRef('groupinfo',$groupinfo);
			$this->assignRef('groupid',$groupid);
			$this->assignRef('userfields',$userfields);
			$this->assignRef('retry',JRequest::getInt('retry'));
		} else {
			$app->redirect('index.php?option=com_mue&view=userreg');
		}
	}
	
	protected function addUser() {
		$model =& $this->getModel();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$groupid = $data['userGroupID'];
		if (!$model->save()) {
			$app->setUserState('mue.userreg.groupid',$groupid); 
			$app->redirect('index.php?option=com_mue&view=userreg&layout=regform&retry=1&groupid='.$groupid,$model->getError(),'error');
		} else {
			$redir = $this->return;
			if (!$redir) $redir='index.php?option=com_mue&view=user&layout=profile';
			if ($muecfg->subscribe) $redir='index.php?option=com_mue&view=subscribe';
			$app->redirect($redir);
		}		
	}
}
?>
