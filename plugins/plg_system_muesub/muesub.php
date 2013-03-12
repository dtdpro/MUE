<?php


defined('_JEXEC') or die;

class plgSystemMUESub extends JPlugin
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
			
		} else if ($user->id) {
			// Load helper
			require_once('components/com_mue'.DS.'helpers'.DS.'mue.php');
			$config=MUEHelper::getConfig();
			$numsubs=count(MUEHelper::getUserSubs());
			if ($config->subscribe && $user->id) {
				if ($numsubs) {
					$sub=MUEHelper::getActiveSub();
					if (!$sub) {
						$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'));
					} else {
						if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile") && $sub->daysLeft <= 10 && $this->params->get('warnexp', 0)) {
							JError::raiseNotice('muesubexpiressoon','Subscription Expires in '.$sub->daysLeft. ' day(s)');
						}
					}
				} else {
					$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'));
				}
			}
		}
	}
}
