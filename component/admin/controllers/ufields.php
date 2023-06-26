<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

class MUEControllerUfields extends JControllerAdmin
{

	protected $text_prefix = "COM_MUE_UFIELD";
	
	public function getModel($name = 'Ufield', $prefix = 'MUEModel', $config = [])
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function copy()
	{
		// Check for request forgeries
		$this->checkToken();
		
		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), '', 'array');
		
		if (!is_array($cid) || count($cid) < 1)
		{
			return false;
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
			
			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			ArrayHelper::toInteger($cid);
			
			// Remove the items.
			if ($model->copy($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_COPIED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}
		
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
}
