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
			
		} else if ($this->params->get('forceupdate', false) && $user->id) {
			// Load helper
			require_once('components/com_mue/helpers/mue.php');
			$dayssince=MUEHelper::getDaysSinceLastUpdate();
			if ($dayssince >=  $this->params->get('updatedays', 180) || $dayssince == -1) {
				JError::raiseNotice('mueupdateprofile','Please update your user profile');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=proedit'));
			}
		}
		
		//Redirect com_users
		if ( JRequest::getVar('option') == 'com_users' && $this->params->get('usersredir', false)) {
			switch ( JRequest::getVar('view') ) {
				case 'profile':
					$view='user';
					$layout = 'profile';
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
					$layout = 'login';
					$return = JRequest::getVar('return', '');
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
			return array(array('link' => JRoute::_('index.php?option=com_mue'), 'text' => "MUE User Extension", 'access' => array('core.manage', 'com_mue')	));
		}
		return array();
	}
}
