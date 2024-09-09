<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport('joomla.utilities.date');

class MUEModelActivation extends JModelLegacy
{

	public function activateUser($token) {
		$db = $this->getDbo();
        $app=Jfactory::getApplication();
		$config     = JFactory::getConfig();
		$userParams = JComponentHelper::getParams('com_users');
        $mueCfg = MUEHelper::getConfig();

		// Get the user id based on the token.
		$query = $db->getQuery(true);
		$query->select('id,username')
		      ->from($db->quoteName('#__users'))
		      ->where($db->quoteName('activation') . ' = ' . $db->quote($token))
		      ->where($db->quoteName('block') . ' = ' . 1);
		$db->setQuery($query);
        $foundUser =  $db->loadObject();
        if (!$foundUser) {
            $this->setError("User not found or already active");
            return false;
        }
		$userId = $foundUser->id;

		$user = JFactory::getUser($userId);

		if ($userParams->get('useractivation') == 2 && !$user->getParam('activate', 0)) {
			// Admin Activation required, user has verified or does not need to

            // Compile the admin notification mail values.
			$data = $user->getProperties();
			$data['activation'] = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
			$user->set('activation', $data['activation']);
			$data['siteurl'] = JUri::base();
			$data['activate'] = JRoute::link( 'site', 'index.php?option=com_mue&view=activation&layout=adminactivate&token=' . $data['activation'], false, 0, true );

			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
			$data['sitename'] = $config->get('sitename');
			$user->setParam('activate', 1);

			// Svae User
			if (!$user->save())
			{
				$this->setError($user->getError());
				return false;
			}

			$emailSubject = JText::sprintf( 'COM_MUE_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT', $data['name'], $data['sitename'] );

			$emailBody = JText::sprintf( 'COM_MUE_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY', $data['sitename'], $data['name'], $data['email'], $data['username'], $data['activate'] );

			// Get all admin users
			$db = $this->getDbo();
			$query = $db->getQuery(true)
			            ->select($db->quoteName(array('name', 'email', 'sendEmail', 'id')))
			            ->from($db->quoteName('#__users'))
			            ->where($db->quoteName('sendEmail') . ' = 1')
			            ->where($db->quoteName('block') . ' = 0');
			$db->setQuery($query);

			$admins = $db->loadObjectList();

			// Send mail to all users with users creating permissions and receiving system emails
			foreach ($admins as $row)
			{
				$usercreator = JFactory::getUser($row->id);

				if ($usercreator->authorise('core.create', 'com_users') && $usercreator->authorise('core.manage', 'com_users'))
				{
					JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBody);
				}
			}
			return 'adminactivate';
		} else {
            // No admin activation required, user can log in or be logged in
            if ($mueCfg->verify_then_login) {
                // Auto login user
                $credentials = [];
                $credentials['username'] = $foundUser->username;
                $credentials['token'] = $token;
                $options = [];
                $options['remember'] = true;

                // Activate
                $user->set('block', '0');
                if (!$user->save())
                {
                    $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
                    return false;
                }
                $status = 'active';

                // Login User
                if ($app->login( $credentials, $options )) {
                    $status = 'active_loggedin';
                }

                // Clear activation token
                $user->set('activation', '');
                if (!$user->save())
                {
                    $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
                    return false;
                }

                return $status;
            } else {
                // Do not auto login user
                $user->set('activation', '');
                $user->set('block', '0');
                if (!$user->save())
                {
                    $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
                    return false;
                }
                return 'active';
            }
		}
	}

	public function adminActivateUser($token) {
		$db = $this->getDbo();
		$config     = JFactory::getConfig();
		$userParams = JComponentHelper::getParams('com_users');

		// Get the user id based on the token.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))
		      ->from($db->quoteName('#__users'))
		      ->where($db->quoteName('activation') . ' = ' . $db->quote($token))
		      ->where($db->quoteName('block') . ' = ' . 1);
		$db->setQuery($query);
		$userId = $db->loadResult();
		if (!$userId) {
			$this->setError("User not found or already active");
			return false;
		}

		$user = JFactory::getUser($userId);

		if (($userParams->get('useractivation') == 2) && $user->getParam('activate', 0)) {
			$user->set('activation', '');
			$user->set('block', '0');
			if (!$user->save())
			{
				$this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
				return false;
			}

			// Compile the user activated notification mail values.
			$data = $user->getProperties();
			$user->setParam('activate', 0);
			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
			$data['sitename'] = $config->get('sitename');
			$data['siteurl'] = JUri::base();
			$emailSubject = JText::sprintf( 'COM_MUE_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT', $data['name'], $data['sitename'] );

			$emailBody = JText::sprintf( 'COM_MUE_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY', $data['name'], $data['siteurl'], $data['username'] );

			JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
			return true;
		}

		return true;

	}
	

}
