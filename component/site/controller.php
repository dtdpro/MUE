<?php
jimport('joomla.application.component.controller');

class MUEController extends JControllerLegacy {

	public function display($cachable = false, $urlparams = false) {
		// Get the document object.
		$document	= JFactory::getDocument();
		$app = JFactory::getApplication();
		// Set the default view name and format from the Request.
		$vName	 = JFactory::getApplication()->input->get('view', 'mue');
		$vFormat = $document->getType();
		$lName	 = JFactory::getApplication()->input->get('layout', 'default');
		$user = JFactory::getUser();
			
		if ($view = $this->getView($vName, $vFormat)) {
			// Do any specific processing by view.
			switch ($vName) {
				case 'userreg':
					// If the user is already logged in, redirect to the profile page.
					if ($user->get('guest') != 1) {
						// Redirect to profile page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile', false));
						return;
					}

					// Check if user registration is enabled
            		if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
            			// Registration is disabled - Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=login', false));
						return;
            		}

					// The user is a guest, load the registration model and show the registration page.
					$model = $this->getModel($vName);
					break;

				case 'userdir':
					if (!$user->id) {
						$return = JRoute::_("index.php?option=com_mue&view=".$vName.'&layout='.$lName);
						// Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=login&layout=login&return='.base64_encode($return), false));
						return;
					}

					// The user is a guest, load the lost password model and show the lost password page.
					$model = $this->getModel($vName);
					break;

				case 'lost':
					// If the user is already logged in, redirect to the profile page.
					if ($user->get('guest') != 1) {
						// Redirect to profile page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile', false));
						return;
					}
					$this->setRedirect(JRoute::_('index.php?option=com_users&view=reset', false));
					return;
					// The user is a guest, load the lost password model and show the lost password page.
					$model = $this->getModel($vName);
					break;

				case 'user':

					// If the user is a guest, redirect to the login page.
					if ($user->get('guest') == 1) {
						$return = JRoute::_("index.php?option=com_mue&view=".$vName.'&layout='.$lName);
						// Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=login&layout=login&return='.base64_encode($return), false));
						return;
					}
					$model = $this->getModel($vName);
					break;

				case 'subscribe':

					// If the user is a guest, redirect to the login page.
					if ($user->get('guest') == 1) {
						$return = JRoute::_("index.php?option=com_mue&view=".$vName.'&layout='.$lName);
						// Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=login&layout=login&return='.base64_encode($return), false));
						return;
					}
					$model = $this->getModel($vName);
					break;

				case 'login':
					// no default layout, use login instead
					if ($lName == 'default') {
						$lName = 'login';
					}
					// If the user is a guest, redirect to the login page.
					if ($lName == 'login' && $user->id) {
						// Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile', false));
						return;
					} 
					$model = $this->getModel($vName);
					break;

				default:
					$model = $this->getModel($vName);
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);
			
			if ($vName != "subscribe") {
				$config=MUEHelper::getConfig();
				$numsubs=count(MUEHelper::getUserSubs());
				if ($config->subscribe && $user->id) {
					if ($numsubs) {
						$sub=MUEHelper::getActiveSub();
						if (!$sub) {
							$app->enqueueMessage('Subscription Expired','error');
						} else {
							if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile") && $sub->daysLeft <= 10) {
								$app->enqueueMessage('Subscription Expires in '.$sub->daysLeft. ' day(s)','warning');
							}
						}
					} else {
						$app->enqueueMessage('Subscription Required','error');
					}
				}
			}
			$view->display();
		}
	}
}
?>
