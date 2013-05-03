<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MUETableUField extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mue_ufields', 'uf_id', $db);
	}
	
	public function store($updateNulls = false)
	{
		// Verify that the alias is unique
		if (!$this->uf_default && ($this->uf_type=="mailchimp" || $this->uf_type=="cmlist")) {
			$this->setError(JText::_('COM_MUE_ERROR_LISTID'));
			return false;
		}
		// Attempt to store the user data.
		return parent::store($updateNulls);
	}
	
	

}