<?php

defined('JPATH_BASE') or die;


JFormHelper::loadFieldClass('list');

class JFormFieldUserGroups extends JFormFieldList
{
	protected $type = 'UserGroups';
	protected static $options = array();

	protected function getOptions()
	{
		$hash = md5($this->element);
		$type = strtolower($this->type);
		
		$db = JFactory::getDBO();
		
		// Build the query for the ordering list.
		$query = 'SELECT ug_id AS value, ug_name AS text' .
				' FROM #__mue_ugroups' .
				' ORDER BY ug_name';
		$db->setQuery($query);
		
		static::$options[$type][$hash] = parent::getOptions();		
		static::$options[$type][$hash] = array_merge(static::$options[$type][$hash], $db->loadObjectList());
		
		return static::$options[$type][$hash];
	}
}
