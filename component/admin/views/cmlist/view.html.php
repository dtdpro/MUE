
<?php
defined('_JEXEC') or die;

class MUEViewCmlist extends JViewLegacy
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


		$this->document->setTitle($this->list->list_info->name);

		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);
	}
}

