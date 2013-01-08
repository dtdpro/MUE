<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MUEModelUserSub extends JModelAdmin
{
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'usrsub_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mue.usersub.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'UserSub', $prefix = 'MUETable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mue.usersub', 'usersub', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_mue/models/forms/usersub.js';
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mue.edit.usersub.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('usersub.id') == 0) {
				$app = JFactory::getApplication();
				
			}
		}
		return $data;
	}
	
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
	}

	public function copy(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$pks = (array) $pks;
		$table = $this->getTable();
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
		
			if ($table->load($pk))
			{
			
				$table->usrsub_id=0;
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				} 
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}
	
	public function getHistory($usid) {
		$query = 'SELECT *' .
				' FROM #__mue_usersubs_log' .
				' WHERE usl_usid = '.$usid .
				' ORDER BY usl_time DESC';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
