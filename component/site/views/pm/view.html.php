<?php

jimport( 'joomla.application.component.view');


class MUEViewPM extends JViewLegacy
{
	private $model;
	private $app;

	public function display($tpl = null)
	{
		$cfg = MUEHelper::getConfig();
		$this->app=Jfactory::getApplication();
		$layout = $this->getLayout();
		$this->params	= JFactory::getApplication()->getParams('com_mue');
		$this->model = $this->getModel();


		$canAccess = $this->model->checkAccessRequirement();
		if (!$canAccess) {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),"Subscription Requried");
		}
		
		switch($layout) {
			case "messages":
				$this->viewMessages();
				break;
			case "message":
				$this->viewMessage();
				break;
			case "sentmessage":
				$this->viewSentMessage();
				break;
			case "trashmessage":
				$this->trashMessage();
				break;
			case "spammessage":
				$this->spamMessage();
				break;
			case "editmessage":
				$this->editMessage();
				break;
			case "savemessage":
				$this->saveMessage();
				break;
			case "createud":
				$this->createMessageUD();
				break;
			case 'replymessage':
				$this->createMessageMsg();
				break;
		}
		parent::display($tpl);
	}

	private function viewMessages() {
		$this->messages = $this->model->getUserMessages();
		$this->sentMessages = $this->model->getUserMessages(true);
		$this->document->setTitle( 'User Messages - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function viewMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Message ID");
		$this->message = $this->model->getUserMessage($mid);
		if (!$this->message) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Message ID");
		$this->document->setTitle( $this->message->msg_subject.' - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function viewSentMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Message ID");
		$this->message = $this->model->getUserSentMessage($mid);
		if (!$this->message) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Message ID");
		$this->document->setTitle( $this->message->msg_subject.' - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function trashMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		$this->model->trashMessage($mid);
		$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Message Trashed");
	}

	private function spamMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		$this->model->spamMessage($mid);
		$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Message Reported as SPAM");
	}

	private function editMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Could not create message");
		$this->message = $this->model->editMessage($mid);
		if (!$this->message) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Could not create message");
		$this->document->setTitle( 'Create Message - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function saveMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		$msub=$input->get('msg_subject',0,'STRING');
		$mbody=$input->get('msg_body',0,'STRING');
		if (!$mid) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Could not create message");
		$sent = $this->model->saveMessage($mid,$msub,$mbody);
		if (!$sent) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=editmessage&mid='.$mid),"Could not send message");
		$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Message Sent");
	}

	private function createMessageUD() {
		if (!$this->model->checkRecentSent()) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Message send limit reached");
		$input = $this->app->input;
		$udid=$input->get('udid',0,'INT');
		if (!$udid) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Request");
		if ($newMsgId = $this->model->createMessageFromUDID($udid)) {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=editmessage&mid='.$newMsgId));
		} else {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Request");
		}
	}

	private function createMessageMsg() {
		if (!$this->model->checkRecentSent()) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Message send limit reached");
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) $this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Request");
		if ($newMsgId = $this->model->createMessageFromMessage($mid)) {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=editmessage&mid='.$newMsgId));
		} else {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'),"Invalid Request");
		}
	}

}
?>
