<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class MUEViewLogin extends JViewLegacy
{
	protected $params;
	protected $user;
	protected $redirurl;
	protected $glist;

	public function display($tpl = null) {
		// Get the view data.
		$this->user		= JFactory::getUser();
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		$layout = $this->getLayout();
		
		switch($layout) {
			case "login": 
				$this->userLogIn();
				break;
			case "logout": 
				$this->userLogOut();
				break;
			case "logmein": 
				$this->logInUser();
				break;
		}
		parent::display($tpl);
	}

	protected function userLogIn() {
		$app=Jfactory::getApplication();
		$ufdata=$app->getUserState( 'users.login.form.data',array());
		$redir = base64_decode(JFactory::getApplication()->input->get('return', null));
		if (!$redir && isset($ufdata['return'])) $redir = $ufdata['return'];
		if (!$redir) $redir = $this->params->get('login_redirect_url');
		$this->redirurl = $redir;
	}
	

	protected function userLogOut() {
		$app=Jfactory::getApplication();
		$app->logout();
		
		$redir = base64_decode(JFactory::getApplication()->input->get('return', '', 'POST', 'BASE64'));
		if (!$redir) $redir="/";
		
		$app->redirect($redir);
	}
	
	protected function logInUser() {
		
		$model = $this->getModel();
		$app=Jfactory::getApplication();
		
		$redir = base64_decode(JFactory::getApplication()->input->get('return', '', 'POST', 'BASE64'));
		if (!$redir) $redir='index.php?option=com_mue&view=user&layout=profile';
		
		if ($model->loginUser()) {
			$user=MUEHelper::getUserInfo();
			if ($user->lastUpdated == "0000-00-00 00:00:00") {
				$app->enqueueMessage('Please update your <a href="'.JRoute::_("index.php?option=com_mue&view=user&layout=profile").'">profile</a>', 'error');
				$app->redirect($redir);
			} else {
				$app->redirect($redir);
			}
		} else {
			$app->redirect('index.php?option=com_mue&view=login&layout=login');
		}
	}
	
	public function gen_uuid($len=8) {
		$hex = md5("in_the_beginning_users_had_no_passwords" . uniqid("", true));
		$pack = pack('H*', $hex);
		$uid = base64_encode($pack); // max 22 chars
		$nuid = preg_replace("/[^a-zA-Z0-9]/", "",$uid); // uppercase only
		if ($len<4) $len=4;
		if ($len>128) $len=128; // prevent silliness, can remove
		while (strlen($nuid)<$len)
		$nuid = $nuid . gen_uuid(22); // append until length achieved
		return substr($nuid, 0, $len);
	}
}
