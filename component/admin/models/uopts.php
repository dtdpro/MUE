<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MUEModelUopts extends JModelList
{
	
	public function __construct($config = array())
	{
		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'ordering', 'o.ordering',
                'published'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $qId = $this->getUserStateFromRequest($this->context.'.filter.field', 'filter_field', '');
		$this->setState('filter.field', $qId);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mue');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('o.ordering', 'asc');
	}
		
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('o.*');

		// From the hello table
		$query->from('#__mue_ufields_opts as o');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('o.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(o.published IN (0, 1))');
		}

		// Filter by field.
		$qId = $this->getState('filter.field');
		if (is_numeric($qId)) {
			$query->where('o.opt_field = '.(int) $qId);
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('o.opt_id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(o.opt_text LIKE '.$search.' )');
            }
        }
				
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}
	
	public function getField() {
		$app = JFactory::getApplication('administrator');
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$this->getState('filter.field'));
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}
}
