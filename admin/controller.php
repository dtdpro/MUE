<?php


jimport('joomla.application.component.controller');


class MUEController extends JController
{

	function display()
	{
		// Set the submenu
		parent::display();
		MUEHelper::addSubmenu(JRequest::getVar('view'));
	}

}
?>
