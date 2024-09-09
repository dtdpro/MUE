<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

use Joomla\Utilities\ArrayHelper;

class MUEModelPM extends JModelAdmin
{
	public function getTable($type = 'PM', $prefix = 'MUETable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		/*// Get the form.
		$form = $this->loadForm('com_mue.couponcode', 'couponcode', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;*/
		return null;
	}

	public function getMessage($msgid)
	{
		$table = $this->getTable();
		if ($msgid > 0)
		{
			// Attempt to load the row.
			$return = $table->load($msgid);
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = ArrayHelper::toObject($properties, 'JObject');

		$db = JFactory::getDBO();
		$fromQuery = $db->getQuery(true);
		$fromQuery->select('u.id, u.name, u.email');
		$fromQuery->from('#__users as u');
		$fromQuery->where('id = '.$item->msg_from);
		$db->setQuery($fromQuery);
		$fromUser = $db->loadObject();

		$item->fromName = $fromUser->name." (".$fromUser->email.')';

		$db = JFactory::getDBO();
		$toQuery = $db->getQuery(true);
		$toQuery->select('u.id, u.name, u.email');
		$toQuery->from('#__users as u');
		$toQuery->where('id = '.$item->msg_to);
		$db->setQuery($toQuery);
		$toUser = $db->loadObject();

		$item->toName = $toUser->name." (".$toUser->email.')';

		return $item;
	}

	public function trashMessage(&$pks)
	{
		$pks = (array) $pks;
		$db = JFactory::getDBO();
		foreach ($pks as $pk)
		{
			$trashQuery = $db->getQuery(true);
			$trashQuery->update('#__mue_messages');
			$trashQuery->set('msg_status="trashed"');
			$trashQuery->where('msg_id = '.$pk);
			$db->setQuery($trashQuery);
			$db->execute();
		}

		return true;
	}

	public function deleteMessage(&$pks)
	{
		$pks = (array) $pks;
		$db = JFactory::getDBO();
		foreach ($pks as $pk)
		{
			$trashQuery = $db->getQuery(true);
			$trashQuery->delete('#__mue_messages');
			$trashQuery->where('msg_id = '.$pk);
			$db->setQuery($trashQuery);
			$db->execute();
		}

		return true;
	}
	
}
