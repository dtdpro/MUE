<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class MUEModelCouponcodes extends JModelList
{
	
	public function __construct($config = array())
	{
		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'c.cu_code','cu_code',
				'ordering', 'p.ordering','published'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mue');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('c.cu_code', 'asc');
	}
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('c.*');

		// From the table
		$query->from('#__mue_coupons as c');
		
		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('c.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(c.published IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('c.cu_id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(c.cu_code LIKE '.$search.' )');
			}
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

		// Get Access levels
		$plist = $this->getSubPlansList();

		// Create a string of the access levels available
		foreach ($this->cache[$store] as &$i) {
			$plans = explode(",",$i->cu_plans);
			$plantext = array();
			foreach ($plans as $p) {
				$plantext[] = $plist[$p];
			}
			$i->cu_plans = implode(", ",$plantext);
			$i->use_count = $this->getUseCount($i->cu_code);
		}

		return $this->cache[$store];
	}

	public function getSubPlansList()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_subs');
		$db->setQuery($query);
		$plans = $db->loadObjectList();

		$plist = array();
		foreach ($plans as $p) {
			$plist[$p->sub_id] = $p->sub_inttitle;
		}
		return $plist;
	}

	public function getUseCount($couponcode)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_usersubs');
		$query->where('usrsub_coupon = "'.$couponcode.'"');
		$db->setQuery($query);
		$subs = $db->loadObjectList();
		return count($subs);
	}
}
