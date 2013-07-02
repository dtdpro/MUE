<?php

// No direct access
defined('_JEXEC') or die;

class plgAuthenticationMUE extends JPlugin
{
	function onUserAuthenticate($credentials, $options, &$response) {
		
	}
	function onUserAuthorisation($user, $options)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return true;
		}
		require_once('components/com_mue/helpers/mue.php');
		$config=MUEHelper::getConfig();
		$db =& JFactory::getDBO();
		if ($config->subscribe) {
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('username') . ' = ' . $db->Quote($user->username));
			$db->setQuery($query);
			$userid = $db->loadResult();
			MUEHelper::updateSubJoomlaGroup($userid);
		}
		return true;
	}
}