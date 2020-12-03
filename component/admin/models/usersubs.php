<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUsersubs extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'subtype','startmonth','startyear','endmonth','endyear'
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

		$subtype = $this->getUserStateFromRequest($this->context.'.filter.subtype', 'filter_subtype');
		$this->setState('filter.subtype', $subtype);

		$startyear = $this->getUserStateFromRequest($this->context.'.filter.startmonth', 'filter_startmonth');
		$this->setState('filter.startmonth', $startyear);

		$startyear = $this->getUserStateFromRequest($this->context.'.filter.startyear', 'filter_startyear');
		$this->setState('filter.startyear', $startyear);

		$endmonth = $this->getUserStateFromRequest($this->context.'.filter.endmonth', 'filter_endmonth');
		$this->setState('filter.endmonth', $endmonth);

		$endyear = $this->getUserStateFromRequest($this->context.'.filter.endyear', 'filter_endyear');
		$this->setState('filter.endyear', $endyear);

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
		
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );
			$query->where( '(u.username LIKE ' . $search . ' OR u.name LIKE ' . $search . ' OR u.email LIKE ' . $search . ')' );
		}

		// Filter by sub type
		$stype = $this->getState('filter.subtype');
		if ($stype) {
			$query->where('s.usrsub_type = "'.$db->escape($stype).'"');
		}

		// Filter by start year
		$startyear = $this->getState('filter.startyear');
		if ($startyear) {
			$query->where("YEAR(s.usrsub_start) = ".$db->escape($startyear));
		}

		// Filter by start month
		$startmonth = $this->getState('filter.startmonth');
		if ($startmonth) {
			$query->where("MONTH(s.usrsub_start) = ".$db->escape($startmonth));
		}

		// Filter by end year
		$endyear = $this->getState('filter.endyear');
		if ($endyear) {
			$query->where("YEAR(s.usrsub_end) = ".$db->escape($endyear));
		}

		// Filter by end month
		$endmonth = $this->getState('filter.endmonth');
		if ($endmonth) {
			$query->where("MONTH(s.usrsub_end) = ".$db->escape($endmonth));
		}
		
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
				
		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}

	public function getItemsCSV() {
		$cfg = MUEHelper::getConfig();

		$query=$this->getListQuery();
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$items = $db->loadObjectList();
		return $items;
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
