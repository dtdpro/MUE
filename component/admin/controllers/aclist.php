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

	function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Aclist');
		$data	= JRequest::getVar('jform', array(), 'post', 'array');
		$field	= JRequest::getInt('field');

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $field))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Attempt to save the configuration.
		$return = $model->save($data,$field);

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_mue&view=aclist&field='.$field.'&tmpl=component', $message, 'error');
			return false;
		}

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('COM_MUE_ACLIST_SAVE_SUCCESS');
				$this->setRedirect('index.php?option=com_mue&view=aclist&field='.$field.'&tmpl=component&refresh=1', $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_config&view=close&tmpl=component');
				break;
		}

		return true;
	}
}
