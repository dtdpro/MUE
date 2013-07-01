<?php


jimport('joomla.application.component.controller');


class MUEController extends JControllerLegacy
{

	function display()
	{
		// Set the submenu
		parent::display();
		MUEHelper::addSubmenu(JRequest::getVar('view'));
	}

}
?>
