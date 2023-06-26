<?php
defined('_JEXEC') or die;

class MUEControllerAclist extends JControllerLegacy
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
		$model	= $this->getModel('Aclist');
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
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			$app->enqueueMessage($message, 'error');
			$this->setRedirect('index.php?option=com_mue&view=aclist&field='.$field);
			return false;
		}

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('COM_MUE_ACLIST_SAVE_SUCCESS');
				$app->enqueueMessage($message);
				$this->setRedirect('index.php?option=com_mue&view=aclist&field='.$field);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_mue&view=ufields');
				break;
		}

		return true;
	}
}
