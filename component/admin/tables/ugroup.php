<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETableUGroup extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_ugroups', 'ug_id', $db);
	}
	
}