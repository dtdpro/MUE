<?php

jimport( 'joomla.application.component.view');


class MUEViewUser extends JView
{
	public function display($tpl = null)
	{
		$cfg = MUEHelper::getConfig();
		$layout = $this->getLayout();
		
		switch($layout) {
			case "subs": 
				$this->userSubs();
				break;
			case "profile": 
				$this->userProfile();
				break;
			case "proedit": 
				$this->userEdit();
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
	
	protected function userSubs() {
		$model =& $this->getModel();
		$print = JRequest::getVar('print');
		$user =& JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$usersubs=MUEHelper::getUserSubs();
			$this->assignRef('usersubs',$usersubs);
		}
		
	}
	
	protected function userProfile() {
		$model =& $this->getModel();
		$print = JRequest::getVar('print');
		$user =& JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo();
			$userfields=$model->getUserFields($userinfo->userGroupID);
			$this->assignRef('userinfo',$userinfo);
			$this->assignRef('userfields',$userfields);
		}
		
	}
	
	protected function userEdit() {
		$model =& $this->getModel();
		$print = JRequest::getVar('print');
		$user =& JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo(true);
			$userfields=$model->getUserFields($userinfo->userGroupID,false,true);
			$this->assignRef('userinfo',$userinfo);
			$this->assignRef('userfields',$userfields);
		}
		
	}
	
	protected function saveUser() {
		$model =& $this->getModel();
		$print = JRequest::getVar('print');
		$user =& JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			if (!$model->save()) {
				$app=Jfactory::getApplication();
				$app->redirect('index.php?option=com_mue&view=user&layout=proedit',$model->getError());
			} else {
				$app=Jfactory::getApplication();
				$app->redirect('index.php?option=com_mue&view=user&layout=profile',"Profile Saved");
			}
		}
		
	}
	
	protected function cancelUserSub() {
		$model =& $this->getModel();
		$sub = JRequest::getInt('sub');
		$user =& JFactory::getUser();
		$userid = $user->id;
		$muecfg = MUEHelper::getConfig();
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if ($userid != 0) {
			if (!$paypal->cancelSub($sub)) {
				$app=Jfactory::getApplication();
				$app->redirect('index.php?option=com_mue&view=user&layout=subs',$paypal->error,'error');
			} else {
				$app=Jfactory::getApplication();
				$app->redirect('index.php?option=com_mue&view=user&layout=subs',"Recurring Subscription Cancellation Request Submitted");
			}
		}
	
	}
}
?>
