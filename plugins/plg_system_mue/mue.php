<?php


defined('_JEXEC') or die;

class plgSystemMUE extends JPlugin
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
			
		} else if ($this->params->get('forceupdate', false) && $user->id) {
			// Load helper
			require_once('components/com_mue/helpers/mue.php');
			$dayssince=MUEHelper::getDaysSinceLastUpdate();
			if ($dayssince >=  $this->params->get('updatedays', 180) || $dayssince == -1) {
				$app->enqueueMessage('Please update your user profile','warning');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=proedit'));
			}
		}
		
		//Redirect com_users
		if ( $jinput->getVar('option') == 'com_users' && $this->params->get('usersredir', false)) {
			switch ( $jinput->getVar('view') ) {
				case 'profile':
					$view='user';
					if ($jinput->getVar('layout') != 'edit') {
						$layout = 'profile';
					} else {
						$layout = 'proedit';
					}
					break;
				case 'registration':
					$view =	'userreg';
					break;
				/*case 'reset':
				case 'remind':
					$view = 'lost';
					break;*/
				case 'logout':
					$view = 'login';
					$layout = 'logout';
					break;
				case 'login':
				//default:
					$view = 'login';
					$layout = $jinput->getVar('layout');
					if (!$layout) $layout = "login";
					$return = $jinput->getVar('return', '');
					break;
			}

			if ($view) {
				$url = 'index.php?option=com_mue&view=' . $view;
				$url .= ( $layout ? '&layout=' . $layout : null );
				$url .= ( $return ? '&return=' . $return : null );

				$app->redirect( JRoute::_( $url ) );
			}
		}
		
		return;
	}

	public function onGetIcons($context) {
		if ('mod_quickicon' == $context && $this->params->get('quickicon', true)) {
			return [
				[
					'link' => JRoute::_('index.php?option=com_mue&view=users'),
					'text' => "MUE User Extension",
					'access' => array('core.manage', 'com_mue')	,
					'image' => "users fas fa-users"
				]
			];
		}
		return [];
	}
}
