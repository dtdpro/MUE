<?php

jimport( 'joomla.application.component.view');


class MUEViewUser extends JViewLegacy
{
	public function display($tpl = null)
	{
		$cfg = MUEHelper::getConfig();
		$layout = $this->getLayout();
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		
		switch($layout) {
			case "cerecords": 
				$this->userCerts();
				break;
			case "subs": 
				$this->userSubs();
				break;
			case "profile": 
				$this->userProfile();
				break;
			case "proedit": 
				$this->userEdit();
				break;
			case "chggroup": 
				$this->changeGroup();
				break;
			case "chgemail": 
				$this->changeEmail();
				break;
			case "savegroup": 
				$this->saveGroup();
				break;
			case "saveemail": 
				$this->saveEmail();
				break;
			case "saveuser": 
				$this->saveUser();
				break;
			case "cancelsub": 
				$this->cancelUserSub();
				break;
		}
		parent::display($tpl);
	}
	
	protected function saveEmail() {
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$newemail = JRequest::getVar('newemail');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			if (!$model->saveEmail($newemail)) {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),'Could not change Email Address');
			} else {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),"Email Address Changed");
			}
		}
	
	}
	
	protected function changeEmail() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
	}
	
	protected function saveGroup() {
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$newgroup = JRequest::getVar('newgroup');
		$user =& JFactory::getUser();
		$userid = $user->id;
		if (count($model->getGroups()) == 1) {
			$app=Jfactory::getApplication();
			$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
		}
		if ($userid != 0) {
			if (!$model->saveGroup($newgroup)) {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),'Could not change group');
			} else {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),"User Group Changed");
			}
		}
		
	}
	
	protected function changeGroup() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if (count($model->getGroups()) == 1) {
			$app=Jfactory::getApplication();
			$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
		}
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo(true);
			$groups=$model->getGroups();
			$this->assignRef('currentgroup',$userinfo->userGroupID);
			$this->assignRef('groups',$groups);
		}
		
	}
	
	protected function userCerts() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userrecs=$model->getUserCERecords();
			$this->assignRef('userrecs',$userrecs);
		}
		
	}
	
	protected function userSubs() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$usersubs=MUEHelper::getUserSubs();
			$this->assignRef('usersubs',$usersubs);
		}
		
	}
	
	protected function userProfile() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo();
			$userfields=$model->getUserFields($userinfo->userGroupID);
			if (count($model->getGroups()) == 1) $this->one_group = true;
			$this->assignRef('userinfo',$userinfo);
			$this->assignRef('userfields',$userfields);
		}
		
	}
	
	protected function userEdit() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo(true);
			$userfields=$model->getUserFields($userinfo->userGroupID,false,true);
			if (count($model->getGroups()) == 1) $this->one_group = true;
			$this->assignRef('userinfo',$userinfo);
			$this->assignRef('userfields',$userfields);
		}
		
	}
	
	protected function saveUser() {
		$model = $this->getModel();
		$print = JRequest::getVar('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			if (!$model->save()) {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=proedit'),$model->getError());
			} else {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),"Profile Saved");
			}
		}
		
	}
	
	protected function cancelUserSub() {
		$model = $this->getModel();
		$sub = JRequest::getInt('sub');
		$user = JFactory::getUser();
		$userid = $user->id;
		$muecfg = MUEHelper::getConfig();
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if ($userid != 0) {
			if (!$paypal->cancelSub($sub)) {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'),$paypal->error,'error');
			} else {
				$app=Jfactory::getApplication();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'),"Recurring Subscription Cancellation Request Submitted");
			}
		}
	
	}
}
?>
