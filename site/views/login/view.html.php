<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class MUEViewLogin extends JView
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
		$this->redirurl = base64_decode(JRequest::getVar('return', $this->params->get('login_redirect_url')));
	}
	

	protected function userLogOut() {
		$app=Jfactory::getApplication();
		$app->logout();
		
		$redir = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
		if (!$redir) $redir="/";
		
		$app->redirect($redir);
	}
	
	protected function logInUser() {
		
		$model =& $this->getModel();
		$app=Jfactory::getApplication();
		
		$redir = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
		if (!$redir) $redir='index.php?option=com_mue&view=user&layout=profile';
		
		if ($model->loginUser()) {
			$app->redirect($redir);
		} else {
			$app->redirect('index.php?option=com_mue&view=login&layout=login',$model->getError());
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
