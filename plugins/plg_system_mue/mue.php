<?php


defined('_JEXEC') or die;

class plgSystemMUE extends JPlugin
{
	function onAfterRoute()
	{
		$app = JFactory::getApplication();

		// No sub enforcement me for admin
		if ($app->isAdmin()) {
			return;
		}

		$user = JFactory::getUser();
		$exceptions = Array();
		$exceptions[]="com_mue";
		//$exceptions[]="com_mcor";
		if (in_array(JRequest::getVar('option'),$exceptions) && $user->id) {
			return;
		} else if ($this->params->get('forceupdate', false) && $user->id) {
			// Load helper
			require_once('components/com_mue'.DS.'helpers'.DS.'mue.php');
			$dayssince=MUEHelper::getDaysSinceLastUpdate();
			if ($dayssince >=  $this->params->get('updatedays', 180) || $dayssince == -1) {
				JError::raiseNotice('mueupdateprofile','Please update your user profile');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=proedit'));
			}
		}
	}
	

	public function onGetIcons($context) {
		if ('mod_quickicon' == $context && $this->params->get('quickicon', true)) {
			return array(array('link' => JRoute::_('index.php?option=com_mue'), 'image' => '../../../../media/com_mue/images/mue-48x48.png', 'text' => "MUE User Extension", 'access' => array('core.manage', 'com_mue')	));
		}
		return array();
	}
}
