
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

		$this->addToolBar();

		parent::display($tpl);
	}

	protected function addToolBar()
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		JToolBarHelper::title('Manage AC List Options', 'mue');
		// Built the actions for new and existing records.
		JToolBarHelper::apply('aclist.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('aclist.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::cancel('aclist.cancel', 'JTOOLBAR_CLOSE');

	}
}

