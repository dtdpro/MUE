<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUsers extends JModelList
{
	
	public function __construct($config = array())
	{
		$cfg = MUEHelper::getConfig();
		
		if ($cfg->subscribe) {
			if (empty($config['filter_fields'])) {
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
							'userg_subsince', 'ug.userg_subsince',
							'userg_subexp','ug.userg_subexp'
			);
			}
		} else {
			if (empty($config['filter_fields'])) {
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
							'userg_update', 'ug.userg_update'
			);
			}
			
		}
		
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		$cfg = MUEHelper::getConfig();
		
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
		$cfg = MUEHelper::getConfig();
		
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
	
		//Get Subscription Information if enabled
		if ($cfg->subscribe) {
			$items=$this->getSubStatus($items);
		}
		
		//Get Joomla Groups
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
		$cfg = MUEHelper::getConfig();

		// Select some fields
		$query->select('u.*');
		$query->from('#__users as u');
		
		// Join over the users.
		$query->select('ug.userg_update as lastUpdate,ug.userg_notes,ug.userg_siteurl,ug.userg_subsince,ug.userg_subexp,ug.userg_group');
		$query->join('LEFT', '#__mue_usergroup AS ug ON u.id = ug.userg_user');
		//$query->select('g.ug_name');
		//$query->join('LEFT', '#__mue_ugroups AS g ON ug.userg_group = g.ug_id');
		
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
		$db =& JFactory::getDBO();
		foreach ($items as &$i) {
			//Days Left
			$query = $db->getQuery(true);
			$query->select('s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft');
			$query->from('#__mue_usersubs as s');
			$query->join('LEFT','#__mue_subs AS p ON s.usrsub_sub = p.sub_id');
			$query->where('s.usrsub_status != "notyetstarted"');
			$query->where('s.usrsub_user="'.$i->id.'"');
			$query->order('daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC');
			$db->setQuery($query,0,1);
			$i->sub = $db->loadObject();
			
			//Member Since
			$query->clear();
			$query->select('s.usrsub_start');
			$query->from('#__mue_usersubs as s');
			$query->where('s.usrsub_status IN ("completed","accepted")');
			$query->where('s.usrsub_user="'.$i->id.'"');
			$query->order('s.usrsub_start ASC');
			$db->setQuery($query,0,1);
			$i->member_since = $db->loadResult();
		}
		return $items;
	}
	
	public function syncSubs() {
		$db =& JFactory::getDBO();
		$items = $this->getUsers();
		$items = $this->getSubStatus($items);
		foreach ($items as $i) {
			if ($i->sub) {
				switch ($i->sub->usrsub_status) {
					case "completed": 
					case "accepted":
						$subend = $i->sub->usrsub_end;
						break;
					default:
						$subend = "0000-00-00";
						break;
				}
			}
							
			$qud = $db->getQuery(true);
			$qud->update('#__mue_usergroup');
			if ($i->sub) {
				$qud->set('userg_subexp = "'.$subend.'"');
				$qud->set('userg_lastpaidvia = "'.$i->sub->usrsub_type.'"');
			}
			else $qud->set('userg_subexp = "0000-00-00"');
			if ($i->member_since) $qud->set('userg_subsince = "'.$i->member_since.'"');
			else $qud->set('userg_subsince = "0000-00-00"');
			$qud->where('userg_user = '.$i->id);
			$db->setQuery($qud);
			if (!$db->query()) return false;
		}
		return true;
	}
	
	protected function getUsers() {
		$db =& JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__users');
		$db->setQuery($query);
		return $db->loadObjectList();
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
		$query = $this->_db->getQuery(true);
		$query->select('ug_id AS value, ug_name AS text');
		$query->from('#__mue_ugroups');
		$query->order('ug_name ASC');
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	public function getItemsCSV() {
		$cfg = MUEHelper::getConfig();
		$query=$this->getListQuery();
		$db = JFactory::getDBO();
		$db->setQuery($query); 
		$items = $db->loadObjectList();
		if ($cfg->subscribe) $items=$this->getSubStatus($items);
		return $items;
		
	}
	
	public function getItemsCSVEml() {
		$db = JFactory::getDBO();
		$cfg = MUEHelper::getConfig();
		$q = $db->getQuery(true);
		$q->select('usr_user');
		$q->from('#__mue_users');
		$q->where('usr_field = '.$cfg->on_list_field);
		$q->where('usr_data = "1"');
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
		$q2 = $db->getQuery(true);
		$q2->select('*');
		$q2->from('#__mue_ufields');
		$q2->where('uf_cms = 0');
		$q2->where('published >= 1');
		$q2->where('uf_type NOT IN ("message","captcha","password")');
		$q2->order('ordering');
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
				$q2 = $db->getQuery(true);
				$q2->select('usr_user,usr_data');
				$q2->from('#__mue_users');
				$q2->where('usr_field = '.$fid);
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
		$q2 = $db->getQuery(true);
		$q2->select('*');
		$q2->from('#__mue_ufields_opts');
		$q2->where('opt_field IN ('.implode(",",$fids).')');
		$q2->where('published >= 1');
		$db->setQuery($q2);
		$opts = $db->loadObjectList();
		$fod = Array();
		foreach ($opts as $o) {
			$fod[$o->opt_id] = $o->opt_text;
		}
		return $fod;
	}

}
