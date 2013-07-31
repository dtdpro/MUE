<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETableUplan extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_subs', 'sub_id', $db);
	}
	
}