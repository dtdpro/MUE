<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETableUfield extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_ufields', 'uf_id', $db);
	}
	
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			// Convert the params field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($array['params']);
			$array['params'] = (string)$parameter;
		}
		return parent::bind($array, $ignore);
	}
	
	public function store($updateNulls = false)
	{
		// Verify that the alias is unique
		if ($this->uf_id && !$this->uf_default && ($this->uf_type=="mailchimp" || $this->uf_type=="cmlist")) {
			$this->setError(JText::_('COM_MUE_ERROR_LISTID'));
			return false;
		}
		// Attempt to store the user data.
		return parent::store($updateNulls);
	}


}