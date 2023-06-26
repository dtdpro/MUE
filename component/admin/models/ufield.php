<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

use Joomla\Utilities\ArrayHelper;

class MUEModelUfield extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'uf_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mue.ufield.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	public function getTable($type = 'Ufield', $prefix = 'MUETable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mue.ufield', 'ufield', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	public function getScript() 
	{
		return 'administrator/components/com_mue/models/forms/ufield.js';
	}
	
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mue.edit.ufield.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('ufield.uf_id') == 0) {
				$app = JFactory::getApplication();
			}
		}
		return $data;
	}
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if (empty($table->uf_id)) {
			// Set the values

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mue_ufields');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
		}
	}

	protected function getReorderConditions($table)
	{
		$condition = array();
		return $condition;
	}
	
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();
		
		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);
			
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
		
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = ArrayHelper::toObject($properties, 'JObject');
		
		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry();
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		if ($item->uf_type == "aclist")
		{
			$item->uf_default = json_decode($item->uf_default);
		}
		
		if ($pk > 0) {
			$q = 'SELECT uguf_group FROM #__mue_uguf WHERE uguf_field = '.$item->uf_id;
			$this->_db->setQuery($q);
			$item->fieldgroups=$this->_db->loadColumn();
		}
		return $item;
	}
	
	public function copy(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$pks = (array) $pks;
		$table = $this->getTable();
		$otable=$this->getTable("Uopt","MUETable");
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
		
			if ($table->load($pk))
			{
				$table->uf_id=0;
				$table->uf_sname=$table->uf_sname.'_copy';
				$table->ordering=$table->getNextOrder();
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				} else {
					if ($table->uf_type == 'multi' || $table->uf_type == 'mcbox' || $table->uf_type == 'dropdown') {
						$newid = $table->uf_id;
						$qoq='SELECT * FROM #__mue_ufields_opts WHERE opt_field = '.$pk;
						$this->_db->setQuery($qoq);
						$qos = $this->_db->loadObjectList();
						foreach($qos as $qo) {
							if ($otable->load($qo->opt_id)) {
								$otable->opt_id=0;
								$otable->opt_field=$newid;
								if (!$otable->store()) {
									$this->setError($otable->getError());
									return false;
								}
							}

						}
					}
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
	
	
	public function save($data)
	{
		// Initialise variables;
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		
		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
			
			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Prepare the row for saving
			$this->prepareTable($table);
			
			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Clean the cache.
			$this->cleanCache();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
		
		$pkName = $table->getKeyName();
		
		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);
		
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->delete();
		$query->from('#__mue_uguf');
		$query->where('uguf_field = '.(int)$table->uf_id);
		$db->setQuery((string)$query);
		$db->execute();
		
		if (!empty($data['fieldgroups'])) {
			foreach ($data['fieldgroups'] as $cc) {
				$qc = 'INSERT INTO #__mue_uguf (uguf_field,uguf_group) VALUES ('.(int)$table->uf_id.','.(int)$cc.')';
				$db->setQuery($qc);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			} 
		}
		
		return true;
	}
	
	public function getUserGroups() {
		$query = 'SELECT ug_id, ug_name' .
				' FROM #__mue_ugroups' .
				' ORDER BY ug_name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
