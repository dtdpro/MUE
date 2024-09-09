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
		$this->document = JFactory::getDocument();


		$canAccess = $this->model->checkAccessRequirement();
		if (!$canAccess) {
			$this->app->enqueueMessage("Subscription Requried");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
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
		if (!$mid) {
			$this->app->enqueueMessage("Invalid Message ID");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$this->message = $this->model->getUserMessage($mid);
		if (!$this->message) {
			$this->app->enqueueMessage("Invalid Message ID");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$this->document->setTitle( $this->message->msg_subject.' - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function viewSentMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) {
			$this->app->enqueueMessage("Invalid Message ID");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$this->message = $this->model->getUserSentMessage($mid);
		if (!$this->message) {
			$this->app->enqueueMessage("Invalid Message ID");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$this->document->setTitle( $this->message->msg_subject.' - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function trashMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		$this->model->trashMessage($mid);
		$this->app->enqueueMessage("Message Trashed");
		$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
	}

	private function spamMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		$this->model->spamMessage($mid);
		$this->app->enqueueMessage("Message Reported as SPAM");
		$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
	}

	private function editMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) {
			$this->app->enqueueMessage("Could not create message");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$this->message = $this->model->editMessage($mid);
		if (!$this->message) {
			$this->app->enqueueMessage("Could not create message");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$this->document->setTitle( 'Create Message - ' . $this->app->getCfg( 'sitename' ) );
	}

	private function saveMessage() {
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		$msub=$input->get('msg_subject',0,'STRING');
		$mbody=$input->get('msg_body',0,'STRING');
		if (!$mid) {
			$this->app->enqueueMessage("Could not create message");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$sent = $this->model->saveMessage($mid,$msub,$mbody);
		if (!$sent) {
			$this->app->enqueueMessage("Could not create message");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=editmessage&mid='.$mid));
		}
		$this->app->enqueueMessage("Message Sent");
		$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
	}

	private function createMessageUD() {
		if (!$this->model->checkRecentSent()) {
			$this->app->enqueueMessage("Message send limit reached");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$input = $this->app->input;
		$udid=$input->get('udid',0,'INT');
		if (!$udid) {
			$this->app->enqueueMessage("Invalid Request");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		if ($newMsgId = $this->model->createMessageFromUDID($udid)) {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=editmessage&mid='.$newMsgId));
		} else {
			$this->app->enqueueMessage("Invalid Request");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
	}

	private function createMessageMsg() {
		if (!$this->model->checkRecentSent()) {
			$this->app->enqueueMessage("Message send limit reached");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		$input = $this->app->input;
		$mid=$input->get('mid',0,'INT');
		if (!$mid) {
			$this->app->enqueueMessage("Invalid Request");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
		if ($newMsgId = $this->model->createMessageFromMessage($mid)) {
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=editmessage&mid='.$newMsgId));
		} else {
			$this->app->enqueueMessage("Invalid Request");
			$this->app->redirect(JRoute::_('index.php?option=com_mue&view=pm&layout=messages'));
		}
	}

}
?>
