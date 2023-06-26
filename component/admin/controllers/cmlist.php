<?php
defined('_JEXEC') or die;

class MUEControllerCmlist extends JControllerLegacy
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

	public function cancel($key = null) {
		$this->checkToken();
		$this->setRedirect('index.php?option=com_mue&view=ufields');
		return true;
	}

	function save()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Cmlist');
		$data	= $this->input->get('jform', array(), 'post', 'array');
		$field	= $this->input->get('field');

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $field))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Attempt to save the configuration.
		$return = $model->save($data,$field);

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('COM_MUE_CMLIST_SAVE_FAILED', $model->getError());
			$app->enqueueMessage($message, 'error');
			$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
			return false;
		}

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('COM_MUE_CMLIST_SAVE_SUCCESS');
				$app->enqueueMessage($message);
				$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
				break;

			case 'save':
			default:
			$this->setRedirect('index.php?option=com_mue&view=ufields');
				break;
		}

		return true;
	}

	function syncField()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Cmlist');
		$field	= $this->input->get('field');

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $field))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Attempt to save the configuration.
		$return = $model->syncField($field);

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('COM_MUE_CMLIST_SYNC_FAILED', $model->getError());
			$app->enqueueMessage($message,'error');
			$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
			return false;
		}

		
		$message = JText::_('COM_MUE_CMLIST_SYNC_SUCCESS');
		if ($return['users']) $message .= '<br>Users Processed: '.$return['users'];
		if ($return['members']) $message .= '<br>List Memebrs: '.$return['members'];
		$app->enqueueMessage($message);
		$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
				

		return true;
	}

	function syncList()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Cmlist');
		$field	= $this->input->get('field');

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $field))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Attempt to save the configuration.
		$return = $model->syncList($field);

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('COM_MUE_CMLIST_SYNC_FAILED', $model->getError());
			$app->enqueueMessage($message,'error');
			$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
			return false;
		}

		
		$message = JText::_('COM_MUE_CMLIST_SYNC_SUCCESS');
		if ($return['total']) $message .= '<br>Users Processed: '.$return['total'];
		if ($return['add_count']) $message .= '<br>added: '.$return['add_count'];
		if ($return['update_count']) $message .= '<br>Updated: '.$return['update_count'];
		if ($return['error_count']) $message .= '<br>Errors: '.$return['error_counr'];
		if (count($return['errors'])) $message .= '<br>Errors: <pre>'.print_r($return,true).'</pre>';

		$app->enqueueMessage($message);
		$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
				
		return true; 
	}

	function addWebhook()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Cmlist');
		$field	= $this->input->get('field');

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $field))
		{
			$app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Attempt to save the configuration.
		if (!$model->addWebhook($field)) {
			$message = JText::sprintf('COM_MUE_CMLIST_WHADD_FAILED', $model->getError());
			$app->enqueueMessage($message,'error');
			$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
			return false;
		} 
		
		$message = JText::_('COM_MUE_CMLIST_WHADD_SUCCESS');
		$app->enqueueMessage($message);
		$this->setRedirect('index.php?option=com_mue&view=cmlist&field='.$field);
		return true; 
	}
}
