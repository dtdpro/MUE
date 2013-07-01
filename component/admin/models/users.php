<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUsers extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'id', 'a.id',
			'name', 'u.name',
			'username', 'u.username',
			'email', 'u.email',
			'block', 'u.block',
			'ug_name', 'g.ug_name',
			'userg_siteurl', 'ug.userg_siteurl',
			'registerDate', 'u.registerDate',
			'lastvisitDate', 'u.lastvisitDate',
			'userg_update', 'ug.userg_update',
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		
		// Load the filter state.
		$groupId = $this->getUserStateFromRequest($this->context.'.filter.ugroup', 'filter_ugroup');
		$this->setState('filter.ugroup', $groupId);
		
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
		$this->setState('filter.state', $state);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mue');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('u.name', 'asc');
	}
	
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();
	
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}
	
		// Load the list items.
		$query = $this->_getListQuery();
		$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
	
		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	
		$items=$this->getSubStatus($items);
		$items=$this->getJoomlaGroups($items);
		// Add the items to the internal cache.
		$this->cache[$store] = $items;
	
		return $this->cache[$store];
	}
	
	
	
	protected function getListQuery($ulist = Array()) 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('u.*');

		// From the hello table
		$query->from('#__users as u');
		// Join over the users.
		$query->select('ug.userg_update as lastUpdate,ug.userg_notes,ug.userg_siteurl');
		$query->join('LEFT', '#__mue_usergroup AS ug ON u.id = ug.userg_user');
		$query->select('g.ug_name');
		$query->join('LEFT', '#__mue_ugroups AS g ON ug.userg_group = g.ug_id');
		
		// Filter by userids.
		if (count($ulist)) {
			$query->where('u.id IN ('.implode(',',$ulist).')');
		}
		
		// Filter by group.
		$groupId = $this->getState('filter.ugroup');
		if (is_numeric($groupId)) {
			$query->where('g.ug_id = '.(int) $groupId);
		}
		
		// Filter by state.
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('u.block = '.(int) $state);
		}
		
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('(u.username LIKE '.$search.' OR u.name LIKE '.$search.' OR u.email LIKE '.$search.')');
		}
		
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		
		
		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}
	
	protected function getSubStatus($items) {
		foreach ($items as &$i) {
			$db =& JFactory::getDBO();
			$query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
			$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
			$query.= 'WHERE s.usrsub_status != "notyetstarted" && s.usrsub_user="'.$i->id.'" ';
			$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
			$db->setQuery($query);
			$sub = $db->loadObject();
			$i->sub = $sub;
		}
		return $items;
	}
	
	protected function getJoomlaGroups($items) {
		foreach ($items as &$i) {
			$db =& JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('g2.title')
				->from('#__user_usergroup_map AS map')
				->where('map.user_id = '.$i->id)
				->join('LEFT', '#__usergroups AS g2 ON g2.id = map.group_id');
			$db->setQuery($query);
			$jgroups = $db->loadColumn();
			$i->jgroups = $jgroups;
		}
		return $items;
	}
	
	public function getUGroups() {
		$query = 'SELECT ug_id AS value, ug_name AS text' .
				' FROM #__mue_ugroups' .
				' ORDER BY ug_name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	public function getItemsCSV() {
		$query=$this->getListQuery();
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$items=$this->getSubStatus($items);
		return $items;
		
	}
	
	public function getItemsCSVEml() {
		$db = JFactory::getDBO();
		$cfg = MUEHelper::getConfig();
		$q = 'SELECT usr_user FROM #__mue_users WHERE usr_field = '.$cfg->on_list_field.' && usr_data = "1"';
		$db->setQuery($q);
		$ulist = $db->loadColumn();
		$query=$this->getListQuery($ulist);
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$items=$this->getSubStatus($items);
		return $items;
		
	}
	
	public function getFields() {
		$db =& JFactory::getDBO();
		$q2  = 'SELECT * FROM #__mue_ufields ';
		$q2 .= 'WHERE uf_cms = 0 && published >= 1  ';
		$q2 .= 'ORDER BY ordering';
		$db->setQuery($q2);
		$fields = $db->loadObjectList();
		return $fields;
	}
	
	public function getUserData($fdata) {
		$db =& JFactory::getDBO();
		foreach ($fdata as $f) {
			if (!$f->uf_cms) { 
				$sname = $f->uf_sname;
				$ud = Array();
				$fid=$f->uf_id;
				$q2  = 'SELECT usr_user,usr_data FROM #__mue_users ';
				$q2 .= 'WHERE usr_field = '.$fid;
				$db->setQuery($q2);
				$opts = $db->loadObjectList();
				foreach ($opts as $o) {
					$uid = $o->usr_user;
					$ud[$uid] = $o->usr_data;
				}
				$udata->$sname = $ud;
			}

		}
		return $udata;
	}
	
	public function getAnswers($fdata) {
		$db =& JFactory::getDBO();
		$fids = Array();
		foreach ($fdata as $f) {
			if (!$f->uf_cms) $fids[]=$f->uf_id;
		}
		$q2  = 'SELECT * FROM #__mue_ufields_opts ';
		$q2 .= 'WHERE opt_field IN ('.implode(",",$fids).') && published >= 1  ';
		$db->setQuery($q2);
		$opts = $db->loadObjectList();
		$fod = Array();
		foreach ($opts as $o) {
			$fod[$o->opt_id] = $o->opt_text;
		}
		return $fod;
	}

}
