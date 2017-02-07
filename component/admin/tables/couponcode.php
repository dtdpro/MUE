<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETableCouponcode extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_coupons', 'cu_id', $db);
	}
	
	
	
}