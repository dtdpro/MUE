<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MUEModelUserSub extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'usrsub_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mue.usersub.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	public function getTable($type = 'UserSub', $prefix = 'MUETable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
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
	
	public function getScript() 
	{
		return 'administrator/components/com_mue/models/forms/usersub.js';
	}
	
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
	
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
	}
	
	public function save($data) {
		$cfg = MUEHelper::getConfig();
		$db = $this->getDbo();
		if ($cfg->subgroup <= 2) return parent::save($data);
		if (in_array($data['usrsub_status'],array("completed","verified","accepted"))) {
			$query = $db->getQuery(true);
			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
			$query->where($db->quoteName('user_id') . ' = ' . (int) $data['usrsub_user']);
			$db->setQuery($query);
			$hasgroup = $db->loadResult();
			if (!$hasgroup) {
				$query->clear();
				$query->insert($db->quoteName('#__user_usergroup_map'));
				$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
				$query->values((int) $data['usrsub_user'] . ',' . $cfg->subgroup);
				$db->setQuery($query);
				$db->query();
			}
		} else {
			$query = $db->getQuery(true);
			
			// Remove user from the sub group
			$query->delete($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('user_id') . ' = ' . (int) $data['usrsub_user']);
			$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
			$db->setQuery($query);
			$db->query();

		}
		return parent::save($data);
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
