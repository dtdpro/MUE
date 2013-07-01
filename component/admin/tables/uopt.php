<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETableUOpt extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_ufields_opts', 'opt_id', $db);
	}
	
}