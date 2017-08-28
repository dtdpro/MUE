<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');


class MUEControllerUsers extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_USER";
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->registerTask('block', 'changeBlock');
		$this->registerTask('unblock', 'changeBlock');
	}
	
	public function getModel($name = 'User', $prefix = 'MUEModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function changeBlock()
	{
		// Check for request forgeries.
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('block' => 1, 'unblock' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_MUE_USERS_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Change the state of the records.
			if (!$model->block($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$this->setMessage(JText::plural('COM_MUE_N_USERS_BLOCKED', count($ids)));
				}
				elseif ($value == 0)
				{
					$this->setMessage(JText::plural('COM_MUE_N_USERS_UNBLOCKED', count($ids)));
				}
			}
		}

		$this->setRedirect('index.php?option=com_mue&view=users');
	}
	
	public function syncSubs()
	{
		// Check for request forgeries.
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		
			// Get the model.
			$model = $this->getModel('Users');
			
			// Change the state of the records.
			if (!$model->syncSubs())
			{
				$this->setMessage(JText::_('COM_MUE_USERS_SYNCSUB_FAILED'));
			}
			else
			{
				$this->setMessage(JText::_('COM_MUE_USERS_SYNCSUB_SUCCESS'));
			}
		

		$this->setRedirect('index.php?option=com_mue&view=users');
	}

	public function syncMemberDB()
	{
		// Check for request forgeries.
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));


		// Get the model.
		$model = $this->getModel('Users');

		// Change the state of the records.
		if (!$model->syncMemberDB())
		{
			$this->setMessage(JText::_('COM_MUE_USERS_SYNCMEMBERDB_FAILED'));
		}
		else
		{
			$this->setMessage(JText::_('COM_MUE_USERS_SYNCMEMBERDB_SUCCESS'));
		}


		$this->setRedirect('index.php?option=com_mue&view=users');
	}
}
