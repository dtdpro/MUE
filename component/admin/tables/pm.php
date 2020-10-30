<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETablePM extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_messages', 'msg_id', $db);
	}
	
	
	
}