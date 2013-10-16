<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUsersubs extends JModelList
{
	
	public function __construct($config = array())
	{
		
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$cId = $this->getUserStateFromRequest($this->context.'.filter.plan', 'filter_plan', '');
		$this->setState('filter.plan', $cId);
		$ps = $this->getUserStateFromRequest($this->context.'.filter.paystatus', 'filter_paystatus', '');
		$this->setState('filter.paystatus', $ps);
		$sd = $this->getUserStateFromRequest($this->context.'.filter.start', 'filter_start', date("Y-m-d",strtotime("-1 years")));
		$this->setState('filter.start', $sd);
		$ed = $this->getUserStateFromRequest($this->context.'.filter.end', 'filter_end', date("Y-m-d"));
		$this->setState('filter.end', $ed);
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mue');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('s.usrsub_time', 'desc');
	}
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('s.*');

		// From the hello table
		$query->from('#__mue_usersubs as s');
		
		// Join over the users.
		$query->select('u.name AS user_name, u.username, u.email as user_email');
		$query->join('LEFT', '#__users AS u ON u.id = s.usrsub_user');
		
		// Join over the courses.
		$query->select('p.*');
		$query->join('LEFT', '#__mue_subs AS p ON p.sub_id = s.usrsub_sub');
		
		// Set Date range
		$startdate = $this->getState('filter.start');
		$enddate = $this->getState('filter.end');
		$query->where('date(s.usrsub_time) BETWEEN "'.$startdate.'" AND "'.$enddate.'"');
		
		// Filter by plan.
		$ps = $this->getState('filter.paystatus');
		if ($ps) {
			$query->where('s.usrsub_status = "'.$ps.'"');
		}
		
		// Filter by pay status.
		$cId = $this->getState('filter.plan');
		if (is_numeric($cId)) {
			$query->where('s.usrsub_sub = '.(int) $cId);
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
	
	public function getPlans() {
		$app = JFactory::getApplication('administrator');
		$query = $this->_db->getQuery(true);
		$query->select('sub_id AS value, sub_inttitle AS text');
		$query->from('#__mue_subs');
		$query->order('sub_inttitle');
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	public function getPayStatuses() {
		$query = $this->_db->getQuery(true);
		$query->select('usrsub_status AS value, CONCAT(UCASE(LEFT(usrsub_status,1)),SUBSTRING(usrsub_status,2)) AS text');
		$query->from('#__mue_usersubs');
		$query->group('usrsub_status');
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
