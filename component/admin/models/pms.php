<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class MUEModelPMs extends JModelList
{
	
	public function __construct($config = array())
	{
		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'm.msg_status','msg_status'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		$published = $this->getUserStateFromRequest($this->context.'.filter.status', 'filter_status', '', 'string');
		$this->setState('filter.status', $published);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mue');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('m.msg_date', 'desc');
	}
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('m.*');

		// From the table
		$query->from('#__mue_messages as m');
		
		// Filter by published state
		$status = $this->getState('filter.status');
		if ($status) {
			$query->where('m.msg_status = "'.$db->escape($status).'"');
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
				
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}
		try
		{
			// Load the list items and add the items to the internal cache.
			$this->cache[$store] = $this->_getList($this->_getListQuery(), $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$db = JFactory::getDBO();
		$usersQuery = $db->getQuery(true);
		$usersQuery->select('u.id, u.name, u.email');
		$usersQuery->from('#__users as u');
		$db->setQuery($usersQuery);
		$users = $db->loadObjectList();

		$usersById = [];
		foreach ($users as $u) {
			$usersById[$u->id] = $u->name." (".$u->email.')';
		}

		// Create a string of the access levels available
		foreach ($this->cache[$store] as &$i) {
			$i->toName = $usersById[$i->msg_to];
			$i->fromName = $usersById[$i->msg_from];
		}

		return $this->cache[$store];
	}
}
