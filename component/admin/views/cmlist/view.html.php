
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


		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addToolBar()
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		JToolBarHelper::title('Manage Campaign Monitor List Options: '.$this->list->list_info->name, 'mue');
		// Built the actions for new and existing records.
		JToolBarHelper::apply('cmlist.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('cmlist.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::cancel('cmlist.cancel', 'JTOOLBAR_CLOSE');

	}
}

