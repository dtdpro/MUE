<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

class MUEModelPM extends JModelLegacy
{
	function checkAccessRequirement() {
		$config=MUEHelper::getConfig();
		$user = JFactory::getUser();
		$userAccessLevels = $user->getAuthorisedViewLevels();
		if (in_array($config->pmgroup,$userAccessLevels)) {
			return true;
		}
		return false;
	}

	function checkRecentSent() {
		$numSentMax = 5;
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('msg_id');
		$query->from('#__mue_messages AS m');
		$query->where('m.msg_from = '.$user->id);
		$query->where('m.msg_date >= ( CURDATE() - INTERVAL 1 DAY )');
		$query->order('m.msg_date DESC');
		$db->setQuery($query);
		$messages = $db->loadColumn();
		if (count($messages) <= $numSentMax) {
			return true;
		}
		return false;
	}

	function getUserMessages($sent=false) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_messages AS m');
		if ($sent) {
			$query->join('RIGHT','#__users AS u ON m.msg_to = u.id');
			$query->where('m.msg_from = '.$user->id);
			$query->where('m.msg_status IN ("new","read","trashed")');
		}
		else {
			$query->join('RIGHT','#__users AS u ON m.msg_from = u.id');
			$query->where('m.msg_to = '.$user->id);
			$query->where('m.msg_status IN ("new","read")');
		}
		$query->order('m.msg_date DESC');
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		return $messages;
	}

	function getUserMessage($msgId) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_messages AS m');
		$query->join('RIGHT','#__users AS u ON m.msg_from = u.id');
		$query->where('m.msg_id = '.$db->quote($msgId));
		$query->where('m.msg_to = '.$user->id);
		$db->setQuery($query);
		$message = $db->loadObject();
		if ($message) {
			$mrQuery = $db->getQuery(true);
			$mrQuery->update('#__mue_messages')->set(['msg_status = ' . $db->quote('read'),])->where([ 'msg_id = '.$db->quote($message->msg_id)]);
			$db->setQuery($mrQuery);
			$result = $db->execute();
			//$result = $db->updateObject('#__mue_messages', $message, 'msg_id');
		}
		return $message;
	}

	function getUserSentMessage($msgId) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_messages AS m');
		$query->join('RIGHT','#__users AS u ON m.msg_to = u.id');
		$query->where('m.msg_id = '.$db->quote($msgId));
		$query->where('m.msg_from = '.$user->id);
		$db->setQuery($query);
		$message = $db->loadObject();
		return $message;
	}

	function trashMessage($msgId) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$mrQuery = $db->getQuery(true);
		$mrQuery->update('#__mue_messages')->set(['msg_status = ' . $db->quote('trashed'),])->where([ 'msg_id = '.$db->quote($msgId),'msg_to = '.$user->id]);
		$db->setQuery($mrQuery);
		$result = $db->execute();
	}

	function spamMessage($msgId) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$mrQuery = $db->getQuery(true);
		$mrQuery->update('#__mue_messages')->set(['msg_status = ' . $db->quote('spam'),])->where([ 'msg_id = '.$db->quote($msgId),'msg_to = '.$user->id]);
		$db->setQuery($mrQuery);
		$result = $db->execute();
	}

	function createMessageFromUDID($udid) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_userdir');
		$query->where('ud_id = '.$udid);
		$db->setQuery($query);
		$udinfo = $db->loadObject();
		if (!$udinfo) {
			return false;
		}

		// Create a new query object.
		$newQuery = $db->getQuery(true);

		// Insert columns.
		$columns = array('msg_from', 'msg_to', 'msg_status');

		// Insert values.
		$values = array($user->id, $udinfo->ud_user, $db->quote("unsent"));

		// Prepare the insert query.
		$newQuery
			->insert($db->quoteName('#__mue_messages'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		// Set the query using our newly populated query object and execute it.
		$db->setQuery($newQuery);
		$db->execute();
		$newId = $db->insertid();
		return $newId;
	}

	function createMessageFromMessage($msgId) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_messages AS m');
		$query->join('RIGHT','#__users AS u ON m.msg_from = u.id');
		$query->where('m.msg_id = '.$db->quote($msgId));
		$query->where('m.msg_to = '.$user->id);
		$db->setQuery($query);
		$message = $db->loadObject();
		if (!$message) {
			return false;
		}

		// Create a new query object.
		$newQuery = $db->getQuery(true);

		// Insert columns.
		$columns = array('msg_from', 'msg_to', 'msg_status', 'msg_subject');

		// Insert values.
		$values = array($user->id, $message->msg_from, $db->quote("unsent"), $db->quote("RE: ".$message->msg_subject));

		// Prepare the insert query.
		$newQuery
			->insert($db->quoteName('#__mue_messages'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		// Set the query using our newly populated query object and execute it.
		$db->setQuery($newQuery);
		$db->execute();
		$newId = $db->insertid();
		return $newId;
	}

	function editMessage($msgId) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_messages AS m');
		$query->join('RIGHT','#__users AS u ON m.msg_to = u.id');
		$query->where('m.msg_id = '.$db->quote($msgId));
		$query->where('m.msg_from = '.$user->id);
		$db->setQuery($query);
		$message = $db->loadObject();
		return $message;
	}

	function saveMessage($msgId,$msgSubject,$msgBody) {
		JSession::checkToken() or jexit( 'Invalid Token' );
		$user = JFactory::getUser();
		$config=MUEHelper::getConfig();
		$db = JFactory::getDBO();
		$mrQuery = $db->getQuery(true);
		$columns = [
			'msg_status = ' . $db->quote('new'),
			'msg_subject = ' . $db->quote($msgSubject),
			'msg_body = ' . $db->quote(nl2br($msgBody)),
		];
		$where = [
			'msg_id = '.$db->quote($msgId),
			'msg_from = '.$user->id
		];
		$mrQuery->update('#__mue_messages')->set($columns)->where($where);
		$db->setQuery($mrQuery);
		$result = $db->execute();
		if (!$result) return false;

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_messages AS m');
		$query->where('m.msg_id = '.$db->quote($msgId));
		$query->where('m.msg_from = '.$user->id);
		$db->setQuery($query);
		$message = $db->loadObject();

		//send notification email
		$toUser = JFactory::getUser($message->msg_to);
		$emailmsg = $config->pmemail_content;
		$mail = JFactory::getMailer();
		$mail->IsHTML(true);
		$mail->addRecipient($toUser->email);
		$mail->setSender($config->pmemail_email,$config->pmemail_name);
		$mail->setSubject($config->pmemail_subject);
		$mail->setBody( $emailmsg );
		$sent = $mail->Send();

		return true;
	}

}
