<?php
/**
 * ContinuEd default controller
 *
 */

jimport('joomla.application.component.controller');

class MUEController extends JController {

	public function display($cachable = false, $urlparams = false) {
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName	 = JRequest::getCmd('view', 'mue');
		$vFormat = $document->getType();
		$lName	 = JRequest::getCmd('layout', 'default');

		if ($view = $this->getView($vName, $vFormat)) {
			// Do any specific processing by view.
			switch ($vName) {
				case 'userreg':
					// If the user is already logged in, redirect to the profile page.
					$user = JFactory::getUser();
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

				case 'lost':
					// If the user is already logged in, redirect to the profile page.
					$user = JFactory::getUser();
					if ($user->get('guest') != 1) {
						// Redirect to profile page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile', false));
						return;
					}

					// The user is a guest, load the lost password model and show the lost password page.
					$model = $this->getModel($vName);
					break;

				case 'user':

					// If the user is a guest, redirect to the login page.
					$user = JFactory::getUser();
					if ($user->get('guest') == 1) {
						// Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=login&layout=login', false));
						return;
					}
					$model = $this->getModel($vName);
					break;

				case 'subscribe':

					// If the user is a guest, redirect to the login page.
					$user = JFactory::getUser();
					if ($user->get('guest') == 1) {
						// Redirect to login page.
						$this->setRedirect(JRoute::_('index.php?option=com_mue&view=login&layout=login', false));
						return;
					}
					$model = $this->getModel($vName);
					break;

				case 'login':

					// If the user is a guest, redirect to the login page.
					$user = JFactory::getUser();
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

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}
?>
