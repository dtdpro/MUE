<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MUEViewUsersubs extends JViewLegacy
{
	function display($tpl = 'csv') 
	{
		$this->state		= $this->get('State');
		// Get data from the model
		$items = $this->get('ItemsCSV');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		// Assign data to the view
		$this->items = $items;

		// Display the template
		parent::display($tpl);

	}

}
