<?php


use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die;

jimport( 'joomla.application.categories' );

class MUERouter extends RouterView {
	public function __construct( $app = null, $menu = null ) {
		$params = JComponentHelper::getParams( 'com_mue' );

		parent::__construct( $app, $menu );

		$this->attachRule( new MUERules( $this ) );

		// needed for Joomla 4
		$router = $app::getRouter();
		$router->attachParseRule( [ $this, 'parseProcessAfter' ], Router::PROCESS_AFTER );
	}

	/**
	 * Parse method for URLs
	 *
	 * @param array  &$segments Array of URL string-segments
	 *
	 * @return  array  Associative array of query values
	 *
	 * @since   3.5
	 */
	public function parse( &$segments ) {
		$vars = array();

		//Get the active menu item.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$item	= $menu->getActive();

		return $this->processParse( $segments, $vars );
	}

	private function processParse( $segments, $vars ) {

		// Process the parsed variables based on custom defined rules
		foreach ( $this->rules as $rule ) {
			$rule->parse( $segments, $vars );
		}

		return $vars;
	}

	/**
	 * @param Router $router
	 * @param Uri $uri
	 *
	 * @return void
	 */
	public function parseProcessAfter( Router $router, Uri $uri ) {
		// Kinda crazy but needed in Joomla 4
		$uri->setPath( null );
	}

}

class MUERules implements RulesInterface {
	public function __construct( $router ) {
		$this->router = $router;
	}

	public function preprocess( &$query ) {
		$default = 0;
		$userreg = 0;
		$regform = 0;
		$profile = 0;
		$cerecords = 0;
		$chgemail = 0;
		$chggroup = 0;
		$subs = 0;
		$proedit = 0;
		$login = 0;
		$logout = 0;
		$userdir = 0;
		$subscribe = 0;
		$check = 0;
		$ppconfirm = 0;
		$ppverify = 0;
		$useractivation= 0;
		$adminactivation= 0;
		$completeactivation= 0;
		$messages = 0;

		// Get all relevant menu items.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('component', 'com_mue');

		// Build an array of serialized query strings to menu item id mappings.
		for ($i = 0, $n = count($items); $i < $n; $i++) {

			if (isset($items[ $i ]->query['layout'])) $layout = $items[ $i ]->query['layout']; else $layout = "";
			if (isset($items[ $i ]->query['view'])) $view = $items[ $i ]->query['view']; else $view = '';

			if ($view) {

				// Check to see if we have found the registration menu item.
				if ( $view == 'userreg' ) {
					switch($layout) {
						case "regform":
							if (empty( $regform )) $regform = $items[ $i ]->id;
							break;
						case "default":
							if (empty( $userreg )) $userreg = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the log in/out menu item.
				if ( $view == 'login' ) {
					switch($layout) {
						case "login":
						default:
							if (empty( $login )) $login = $items[ $i ]->id;
							break;
						case "logout":
							if (empty( $logout )) $logout = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the profile,profile edit, and records menu item.
				if ( $view == 'user' ) {
					switch($layout) {
						case "profile":
							if (empty( $profile )) $profile = $items[ $i ]->id;
							break;
						case "subs":
							if (empty( $subs )) $subs = $items[ $i ]->id;
							break;
						case "proedit":
							if (empty( $proedit )) $proedit = $items[ $i ]->id;
							break;
						case "cerecords":
							if (empty( $cerecords )) $cerecords = $items[ $i ]->id;
							break;
						case "chgemail":
							if (empty( $chgemail )) $chgemail = $items[ $i ]->id;
							break;
						case "chggroup":
							if (empty( $chggroup )) $chggroup = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the user directory menu item.
				if ( $view == 'userdir' && empty( $userdir ) ) {
					$userdir = $items[ $i ]->id;
				}

				// Check to see if we have found the subscribe/pay by check info menu item.
				if ( $view == 'subscribe' ) {
					switch($layout) {
						case "check":
							if (empty( $check )) $check = $items[ $i ]->id;
							break;
						case "ppconfirm":
							if (empty( $ppconfirm )) $ppconfirm = $items[ $i ]->id;
							break;
						case "ppverify":
							if (empty( $ppverify )) $ppverify = $items[ $i ]->id;
							break;
						case "default":
						default:
							if (empty( $subscribe )) $subscribe = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the subscribe/pay by check info menu item.
				if ( $view == 'pm' ) {
					if (empty( $messages )) $messages = $items[ $i ]->id;
				}

				// Check to see if we have found the activation menu item.
				if ( $view == 'activation' ) {
					switch($layout) {
						case "adminactivate":
							if (empty( $adminactivation )) $adminactivation = $items[ $i ]->id;
							break;
						case "useractivate":
							if (empty( $useractivation )) $useractivation = $items[ $i ]->id;
							break;
						case "complete":
						default:
							if (empty( $completeactivation )) $completeactivation = $items[ $i ]->id;
							break;
					}
				}
			}
		}

		if (isset($query['view'])) $queryView = $query['view']; else $queryView = "";
		if (isset($query['layout'])) $queryLayout = $query['layout']; else $queryLayout = "";

		switch ($queryView) {
			case 'userreg':
				switch ( $queryLayout ) {
					case 'regform':
						$query['Itemid'] = $regform;
						break;
					case 'default':
					default:
						$query['Itemid'] = $userreg ;
						break;
				}
				break;

			case 'login':
				switch ($queryLayout) {
					case 'login':
						$query['Itemid'] = $login;
						break;
					case 'logout':
					default:
						$query['Itemid'] = $logout;
						break;
				}
				break;

			case 'pm':
				switch ($queryLayout) {
					case 'messages':
						$query['Itemid'] = $messages;
						break;
					default:
						$query['Itemid'] = $messages;
						break;
				}
				break;

			case 'activation':
				switch ($queryLayout) {
					case 'adminactivate':
						$query['Itemid'] = $adminactivation;
						break;
					case 'useractivate':
						$query['Itemid'] = $useractivation;
						break;
					case 'complete':
					default:
						$query['Itemid'] = $completeactivation;
						break;
				}
				break;

			case 'user':
				switch ($queryLayout) {

					case 'proedit':
						$query['Itemid'] = $proedit;
						break;
					case 'subs':
						$query['Itemid'] = $subs;
						break;
					case 'cerecords':
						$query['Itemid'] = $cerecords;
						break;
					case 'chgemail':
						$query['Itemid'] = $chgemail;
						break;
					case 'chggroup':
						$query['Itemid'] = $chggroup;
						break;
					case 'profile':
					default:
						$query['Itemid'] = $profile;
						break;
				}
				break;

			case 'userdir':
				$query['Itemid'] = $userdir;
				break;

			case 'subscribe':
				switch ( $queryLayout ) {
					case 'check':
						 $query['Itemid'] = $check;
						break;
					case 'ppconfirm':
						$query['Itemid'] = $ppconfirm ;
						break;
					case 'ppverify':
						$query['Itemid'] = $ppverify ;
						break;
					case 'default':
					default:
						$query['Itemid'] = $subscribe;
						break;
				}
				break;

			default:
				break;
		}

	}

	public function build( &$query, &$segments ) {
		$default = 0;
		$userreg = 0;
		$regform = 0;
		$profile = 0;
		$cerecords = 0;
		$chgemail = 0;
		$chggroup = 0;
		$subs = 0;
		$proedit = 0;
		$login = 0;
		$logout = 0;
		$userdir = 0;
		$subscribe = 0;
		$check = 0;
		$ppconfirm = 0;
		$ppverify = 0;
		$useractivation= 0;
		$adminactivation= 0;
		$completeactivation= 0;
		$messages = 0;

		// Get all relevant menu items.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('component', 'com_mue');

		// Build an array of serialized query strings to menu item id mappings.
		for ($i = 0, $n = count($items); $i < $n; $i++) {

			if (isset($items[ $i ]->query['layout'])) $layout = $items[ $i ]->query['layout']; else $layout = "";
			if (isset($items[ $i ]->query['view'])) $view = $items[ $i ]->query['view']; else $view = '';

			if ($view) {

				// Check to see if we have found the registration menu item.
				if ( $view == 'userreg' ) {
					switch($layout) {
						case "regform":
							if (empty( $regform )) $regform = $items[ $i ]->id;
							break;
						case "default":
							if (empty( $userreg )) $userreg = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the log in/out menu item.
				if ( $view == 'login' ) {
					switch($layout) {
						case "login":
						default:
							if (empty( $login )) $login = $items[ $i ]->id;
							break;
						case "logout":
							if (empty( $logout )) $logout = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the profile,profile edit, and records menu item.
				if ( $view == 'user' ) {
					switch($layout) {
						case "profile":
							if (empty( $profile )) $profile = $items[ $i ]->id;
							break;
						case "subs":
							if (empty( $subs )) $subs = $items[ $i ]->id;
							break;
						case "proedit":
							if (empty( $proedit )) $proedit = $items[ $i ]->id;
							break;
						case "cerecords":
							if (empty( $cerecords )) $cerecords = $items[ $i ]->id;
							break;
						case "chgemail":
							if (empty( $chgemail )) $chgemail = $items[ $i ]->id;
							break;
						case "chggroup":
							if (empty( $chggroup )) $chggroup = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the user directory menu item.
				if ( $view == 'userdir' && empty( $userdir ) ) {
					$userdir = $items[ $i ]->id;
				}

				// Check to see if we have found the subscribe/pay by check info menu item.
				if ( $view == 'subscribe' ) {
					switch($layout) {
						case "check":
							if (empty( $check )) $check = $items[ $i ]->id;
							break;
						case "ppconfirm":
							if (empty( $ppconfirm )) $ppconfirm = $items[ $i ]->id;
							break;
						case "ppverify":
							if (empty( $ppverify )) $ppverify = $items[ $i ]->id;
							break;
						case "default":
						default:
							if (empty( $subscribe )) $subscribe = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the subscribe/pay by check info menu item.
				if ( $view == 'pm' ) {
					if (empty( $messages )) $messages = $items[ $i ]->id;
				}

				// Check to see if we have found the activation menu item.
				if ( $view == 'activation' ) {
					switch($layout) {
						case "adminactivate":
							if (empty( $adminactivation )) $adminactivation = $items[ $i ]->id;
							break;
						case "useractivate":
							if (empty( $useractivation )) $useractivation = $items[ $i ]->id;
							break;
						case "complete":
						default:
							if (empty( $completeactivation )) $completeactivation = $items[ $i ]->id;
							break;
					}
				}
			}
		}

		if (isset($query['view'])) $queryView = $query['view']; else $queryView = "";
		if (isset($query['layout'])) $queryLayout = $query['layout']; else $queryLayout = "";

		$found = 0;
		$keepLayout = false;

		switch ($queryView) {
			case 'userreg':
				switch ( $queryLayout ) {
					case 'regform':
						$found = $regform;
						break;
					case 'default':
						$found = $userreg ;
						break;
					default:
						$keepLayout = true;
						$found = $userreg ;
						break;
				}
				break;

			case 'login':
				switch ($queryLayout) {
					case 'login':
						$found = $login;
						break;
					case 'logout':
						$found = $logout;
						break;
					default:
						$keepLayout = true;
						$found = $login;
						break;
				}
				break;

			case 'pm':
				switch ($queryLayout) {
					case 'messages':
						$found = $messages;
						break;
					default:
						$keepLayout = true;
						$found = $messages;
						break;
				}
				break;

			case 'activation':
				switch ($queryLayout) {
					case 'adminactivate':
						$found = $adminactivation;
						break;
					case 'useractivate':
						$found = $useractivation;
						break;
					case 'complete':
						$found = $completeactivation;
						break;
					default:
						$keepLayout = true;
						$found = $completeactivation;
						break;
				}
				break;

			case 'user':
				switch ($queryLayout) {

					case 'proedit':
						$found = $proedit;
						break;
					case 'subs':
						$found = $subs;
						break;
					case 'cerecords':
						$found = $cerecords;
						break;
					case 'chgemail':
						$found = $chgemail;
						break;
					case 'chggroup':
						$found = $chggroup;
						break;
					case 'profile':
						$found = $profile;
						break;
					default:
						$keepLayout = true;
						$found = $profile;
						break;
				}
				break;

			case 'userdir':
				$found = $userdir;
				break;

			case 'subscribe':
				switch ( $queryLayout ) {
					case 'check':
						$found = $check;
						break;
					case 'ppconfirm':
						$found = $ppconfirm ;
						break;
					case 'ppverify':
						$found = $ppverify ;
						break;
					case 'default':
						$found = $subscribe;
						break;
					default:
						$keepLayout = true;
						$found = $subscribe;
						break;
				}
				break;

			default:
				break;
		}
		if ($found > 0) {
			unset( $query['view'] );
			if (!$keepLayout) unset( $query['layout'] );
		}

	}

	public function parse( &$segments, &$vars ) {

	}
}


function MUEBuildRoute( &$query ) {
	$app    = JFactory::getApplication();
	$router = new MUERouter( $app, $app->getMenu() );

	return $router->build( $query );
}

function MUEParseRoute( $segments ) {
	$app    = JFactory::getApplication();
	$router = new MUERouter( $app, $app->getMenu() );

	return $router->parse( $segments );
}
