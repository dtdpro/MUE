<?php


defined('_JEXEC') or die;

class plgSystemMUESub extends JPlugin
{
	function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;

		// No sub enforcement me for admin
		if ($app->isClient('administrator')) {
			return;
		}

		$user = JFactory::getUser();
		$exceptions = Array();
		$exceptions[]="com_mue";
		//$exceptions[]="com_mcor";
		if (in_array($jinput->getVar('option'),$exceptions) && $user->id) {
			return;
			
		} else if ($user->id) {
			// Load helper
			require_once('components/com_mue/helpers/mue.php');
			$config=MUEHelper::getConfig();
			$numsubs=count(MUEHelper::getUserSubs());
			if ($config->subscribe && $user->id) {
				if ($numsubs) {
					$sub=MUEHelper::getActiveSub();
					if (!$sub) {
						$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'));
					} else {
						if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile") && $sub->daysLeft <= 10 && $this->params->get('warnexp', 0)) {
							$app->enqueueMessage('Subscription Expires in '.$sub->daysLeft. ' day(s)','notice');
						}
					}
				} else {
					$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=subs'));
				}
			}
		}
	}
}
