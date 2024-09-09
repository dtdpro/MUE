<?php
use Joomla\CMS\Authentication\Authentication;

// No direct access
defined('_JEXEC') or die;

class plgAuthenticationMUE extends JPlugin
{
	function onUserAuthenticate($credentials, $options, &$response) {
        $app = JFactory::getApplication();
        if (!$app->isClient('administrator')) {
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
            $query->select('id,username,email,name')
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('activation') . ' = ' . $db->quote($credentials['token']))
                ->where($db->quoteName('username') . ' = ' . $db->quote($credentials['username']));
            $db->setQuery($query);
            $foundUser = $db->loadObject();
            if ($foundUser) {
                $response->email = $foundUser->email;
                $response->fullname = $foundUser->name;
                $response->status = Authentication::STATUS_SUCCESS;
                $response->error_message = '';
            }
        }
	}
	function onUserAuthorisation($user, $options)
	{
		$app = JFactory::getApplication();
		if ($app->isClient('administrator')) {
			return true;
		}
        require JPATH_ROOT.'/components/com_mue/vendor/autoload.php';
		require_once('components/com_mue/helpers/mue.php');
		$config=MUEHelper::getConfig();
		$db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('username') . ' = ' . $db->Quote($user->username));
        $db->setQuery($query);
        $userid = $db->loadResult();
        MUEHelper::updateUserLoginTime($userid);
		if ($config->subscribe) {
			MUEHelper::updateSubJoomlaGroup($userid);
		}
		return true;
	}
}