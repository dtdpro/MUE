<?php

jimport( 'joomla.application.component.view');


class MUEViewUser extends JView
{
	public function display($tpl = null)
	{
		$cfg = MUEHelper::getConfig();
		$layout = $this->getLayout();
		
		switch($layout) {
			case "profile": 
				$this->userProfile();
				break;
			case "proedit": 
				$this->userEdit();
				break;
			case "saveuser": 
				$this->saveUser();
				break;
		}
		parent::display($tpl);
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
}
?>
