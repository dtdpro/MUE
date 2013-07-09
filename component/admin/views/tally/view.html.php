<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MUEViewTally extends JViewLegacy
{
	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'MUE User Fields - Results' ), 'mue' );
		$model = $this->getModel('tally');
		// Get data from the model
		$this->fdata=$model->getFields();		
		parent::display($tpl);
	}
}
