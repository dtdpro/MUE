<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUGroups extends JModelList
{
	
	public function __construct($config = array())
	{
	if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'ordering', 'ug.ordering',
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		$accessId = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mue');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('ug.ordering', 'asc');
	}
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('ug.*');

		// From the hello table
		$query->from('#__mue_ugroups as ug');
		
		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = ug.access');
		
		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('ug.access = '.(int) $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('ug.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(ug.published IN (0, 1))');
		}
		
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}
}
