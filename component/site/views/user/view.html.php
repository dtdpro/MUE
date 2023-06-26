<?php

jimport( 'joomla.application.component.view');

use Joomla\CMS\Session\Session;
class MUEViewUser extends JViewLegacy
{
	public function display($tpl = null)
	{
		$cfg = MUEHelper::getConfig();
		$layout = $this->getLayout();
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		$this->input = JFactory::getApplication()->input;
		
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
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$newemail = $this->input->get('newemail');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			if (!$model->saveEmail($newemail)) {
				$app=Jfactory::getApplication();
				$app->enqueueMessage($model->getError(), 'error');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
			} else {
				$app=Jfactory::getApplication();
				$app->enqueueMessage("Email Address Changed");
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
			}
		}
	
	}
	
	protected function changeEmail() {
		$model = $this->getModel();
		$print = $this->input->get('print');
	}
	
	protected function saveGroup() {
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$newgroup = $this->input->get('newgroup');
		$user = JFactory::getUser();
		$userid = $user->id;
		if (count($model->getGroups()) == 1) {
			$app=Jfactory::getApplication();
			$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
		}
		if ($userid != 0) {
			if (!$model->saveGroup($newgroup)) {
				$app=Jfactory::getApplication();
				$app->enqueueMessage('Could not change group', 'error');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
			} else {
				$app=Jfactory::getApplication();
				$app->enqueueMessage("User Group Changed");
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
			}
		}
		
	}
	
	protected function changeGroup() {
		$model = $this->getModel();
		$print = $this->input->get('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if (count($model->getGroups()) == 1) {
			$app=Jfactory::getApplication();
			$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
		}
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo(true);
			$groups=$model->getGroups();
			$this->currentgroup=$userinfo->userGroupID;
			$this->groups=$groups;
		}
		
	}
	
	protected function userCerts() {
		$model = $this->getModel();
		$print = $this->input->get('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userrecs=$model->getUserCERecords();
			$this->userrecs=$userrecs;
		}
		
	}
	
	protected function userSubs() {
		$model = $this->getModel();
		$print = $this->input->get('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$usersubs=MUEHelper::getUserSubs();
			$this->usersubs=$usersubs;
		}
		
	}
	
	protected function userProfile() {
		$model = $this->getModel();
		$print = $this->input->get('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo();
			$userfields=$model->getUserFields($userinfo->userGroupID);
			if (count($model->getGroups()) == 1) $this->one_group = true;
			else $this->one_group=false;
			$this->userinfo=$userinfo;
			$this->userfields=$userfields;
		}
		
	}
	
	protected function userEdit() {
		$model = $this->getModel();
		$print = $this->input->get('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			$userinfo=MUEHelper::getUserInfo(true);
			$userfields=$model->getUserFields($userinfo->userGroupID,false,true);
			if (count($model->getGroups()) == 1) $this->one_group = true;
			else $this->one_group=false;
			$this->userinfo=$userinfo;
			$this->userfields=$userfields;
			$this->defaultTimezone = JFactory::getConfig()->get( 'offset' );
		}
		
	}
	
	protected function saveUser() {
		$model = $this->getModel();
		$print = $this->input->get('print');
		$user = JFactory::getUser();
		$userid = $user->id;
		if ($userid != 0) {
			if (!$model->save()) {
				$app=Jfactory::getApplication();
				$app->enqueueMessage($model->getError(), 'error');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=proedit'));
			} else {
				$app=Jfactory::getApplication();
				$app->enqueueMessage("Profile Saved");
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
			}
		}
		
	}
	
	protected function cancelUserSub() {
		$model = $this->getModel();
		$sub = $this->input->get('sub');
		$user = JFactory::getUser();
		$userid = $user->id;
		$muecfg = MUEHelper::getConfig();
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if ($userid != 0) {
			if (!$paypal->cancelSub($sub)) {
				$app=Jfactory::getApplication();
				$app->enqueueMessage($paypal->error,'error');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'));
			} else {
				$app=Jfactory::getApplication();
				$app->enqueueMessage("Recurring Subscription Cancellation Request Submitted");
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'));
			}
		}
	
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}
}
?>
