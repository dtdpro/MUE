<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUserSubs extends JModelList
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
		$sd = $this->getUserStateFromRequest($this->context.'.filter.start', 'filter_start', date("Y-m-d",strtotime("-1 months")));
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
		
		// Filter by course.
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
		
		
		
		$query->order($db->getEscaped($orderCol.' '.$orderDirn));
				
		return $query;
	}
	
	public function getPlans() {
		$app = JFactory::getApplication('administrator');
		$query = 'SELECT sub_id AS value, sub_inttitle AS text' .
				' FROM #__mue_subs' .
				' ORDER BY sub_inttitle';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
