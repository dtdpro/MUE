<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class MUEModelTally extends JModelLegacy
{
	function getFields()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__mue_ufields ';
		$query .= 'WHERE uf_type IN ("message","multi","mcbox","dropdown") && published = 1 ORDER BY ordering ASC';
		$db->setQuery( $query ); 
		$qdata = $db->loadObjectList();
		return $qdata;
	}

}
