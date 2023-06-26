<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

use Joomla\Utilities\ArrayHelper;

class MUEModelCouponcode extends JModelAdmin
{
	public function getTable($type = 'Couponcode', $prefix = 'MUETable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mue.couponcode', 'couponcode', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mue.edit.couponcode.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('couponcode.cu_id') == 0) {
				
			}
		}
		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  JObject|boolean  Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{

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
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		if ($pk > 0) {
			$item->cu_plans = explode(",",$item->cu_plans);
		} else {
			$item->cu_plans=array();
		}
		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
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

			//Set Featured access level list as comma separated string
			if (!empty($data['cu_plans'])) {
				$data['cu_plans'] = implode(",",$data['cu_plans']);
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

			/// Store the data.
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

		return true;
	}
	
}
