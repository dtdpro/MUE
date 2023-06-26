<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

class MUEControllerUser extends JControllerForm
{
	protected $text_prefix = "COM_MUE_USER";

	public function cancel($key = null)
	{
		$this->checkToken();

		// Initialise variables.
		$app		= JFactory::getApplication();
		$input      = JFactory::getApplication()->input;
		$model		= $this->getModel();
		$context	= "$this->option.edit.$this->context";

		if (empty($key)) {
			$key = 'usr_user';
		}

		$recordId	= $input->get($key);

		// Clean the session data and redirect.
		$this->releaseEditId($context, $recordId);
		$app->setUserState($context.'.data',	null);
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

		return true;
	}

	public function edit($key = null, $urlVar = null)
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$input      = JFactory::getApplication()->input;
		$model		= $this->getModel();
		$cid		= $input->get('cid', array(), 'post', 'array');
		$context	= "$this->option.edit.$this->context";
		$append		= '';

		// Determine the name of the primary key for the data.
		if (empty($key)) {
			$key = 'usr_user';
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar)) {
			$urlVar = 'id';
		}
		// Get the previous record id (if any) and the current record id.
		$recordId	= (int) (count($cid) ? $cid[0] : $input->get($urlVar));
		
	
		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key)) {
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		$this->holdEditId($context, $recordId); 
		$app->setUserState($context.'.data', null);
		$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, $urlVar));

		return true;
	}
	
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app		= JFactory::getApplication();
		$lang		= JFactory::getLanguage();
		$input      = JFactory::getApplication()->input;
		$model		= $this->getModel();
		$data		= $input->get('jform', array(), 'post', 'array');
		$context	= "$this->option.edit.$this->context";
		$task		= $this->getTask();

		// set the name of the primary key for the data.
		$key = 'usr_user';
		

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar)) {
			$urlVar = $key;
		}

		$recordId	= $input->get($urlVar);

		$session	= JFactory::getSession();
		$registry	= $session->get('registry');

		if (!$this->checkEditId($context, $recordId)) {
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// Access check.
		if (!$this->allowSave($data, $key)) {
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate(null, $data);

		// Check for validation errors.
		if ($validData === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context.'.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, "id"), false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($validData)) {
			// Save the data in the session.
			$app->setUserState($context.'.data', $validData);

			// Redirect back to the edit screen.
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, "id"), false));

			return false;
		}

		$this->setMessage(JText::_(($lang->hasKey($this->text_prefix.($recordId==0 && $app->isSite() ? '_SUBMIT' : '').'_SAVE_SUCCESS') ? $this->text_prefix : 'JLIB_APPLICATION') . ($recordId==0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context.'.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context.'.data', null);
				

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, "id"), false));
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context.'.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend(null, "id"), false));
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context.'.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
				break;
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}

	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		// Set the model
		$model = $this->getModel('User', '', array());
	
		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_mue&view=users' . $this->getRedirectToListAppend(), false));
	
		return parent::batch($model);
	}


	
}
