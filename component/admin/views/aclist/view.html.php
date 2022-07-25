
<?php
defined('_JEXEC') or die;

class MUEViewAclist extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		$this->list	= $this->get('List');
		$this->ufields	= $this->get('UFields');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->document->setTitle("AC List Options");

		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);
	}
}

