<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

class MUEControllerPMs extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_PM";
	
	public function getModel($name = 'PM', $prefix = 'MUEModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function trashmsgs() {
		// Check for request forgeries
		$this->checkToken();

		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');

		if (empty($cid)) {
			\JLog::add(\JText::_("No Messages selected"), \JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			$model->trashMessage($cid);

			$this->setMessage('Messages trashed');
		}

		$this->setRedirect(\JRoute::_('index.php?option=com_mue&view=pms', false));
	}

	public function deletemsgs() {
		// Check for request forgeries
		$this->checkToken();

		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');

		if (empty($cid)) {
			\JLog::add(\JText::_("No Messages selected"), \JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			$model->deleteMessage($cid);

			$this->setMessage('Messages deleted permanently');
		}

		$this->setRedirect(\JRoute::_('index.php?option=com_mue&view=pms', false));
	}
}