<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MUEViewMUE extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'MUE User Extension' ), 'mue' );
		JToolBarHelper::preferences('com_mue');
		parent::display($tpl);
	}
}
