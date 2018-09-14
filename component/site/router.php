<?php

defined('_JEXEC') or die;

function MUEBuildRoute(&$query)
{
	// Declare static variables.
	static $items;
	static $default;
	static $userreg;
	static $regform;
	static $profile;
	static $cerecords;
	static $chgemail;
	static $chggroup;
	static $subs;
	static $proedit;
	static $login;
	static $logout;
	static $lost;
	static $userdir;
	static $subscribe;
	static $check;
	static $ppconfirm;

	// Initialise variables.
	$segments = array();

	// Get the relevant menu items if not loaded.
	if (empty($items)) {
		// Get all relevant menu items.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('component', 'com_mue');

		// Build an array of serialized query strings to menu item id mappings.
		for ($i = 0, $n = count($items); $i < $n; $i++) {

			if (!empty($items[$i]->query['view'])) {

				$layout = $items[ $i ]->query['layout'];
				$view = $items[ $i ]->query['view'];

				// Check to see if we have found the registration menu item.
				if ( $view == 'userreg' ) {
					switch($layout) {
						case "regform":
							if (empty( $regform )) $regform = $items[ $i ]->id;
							break;
						case "default":
						default:
							if (empty( $userreg )) $userreg = $items[ $i ]->id;
							break;
					}
				}

				// Check to see if we have found the log in/out menu item.
				if ( $view == 'login' ) {
					switch($layout) {
						case "login":
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

				// Check to see if we have found the lost info menu item.
				if ( $view == 'lost' && empty( $lost ) ) {
					$lost = $items[ $i ]->id;
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
						case "default":
						default:
							if (empty( $subscribe )) $subscribe = $items[ $i ]->id;
							break;
					}
				}
			}
		}
	}

	if (!empty($query['view'])) {
		switch ($query['view']) {
			case 'userreg':
				switch ($query['layout']) {
					case 'regform':
						if ($query['Itemid'] = $regform) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;

					case 'default':
					default:
						if ($query['Itemid'] = $userreg) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
				}
				break;

			case 'login':
				switch ($query['layout']) {
					case 'login':
						if ($query['Itemid'] = $login) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;

					case 'logout':
						if ($query['Itemid'] = $logout) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
				}
				break;

			case 'user':
				switch ($query['layout']) {
					case 'profile':
						if ($query['Itemid'] = $profile) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
		
					case 'proedit':
						if ($query['Itemid'] = $proedit) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
		
					case 'subs':
						if ($query['Itemid'] = $subs) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
		
					case 'cerecords':
						if ($query['Itemid'] = $cerecords) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
		
					case 'chgemail':
						if ($query['Itemid'] = $chgemail) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
		
					case 'chggroup':
						if ($query['Itemid'] = $chggroup) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
				}
				break;

			case 'lost':
				if ($query['Itemid'] = $lost) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;
				
			case 'userdir':
				if ($query['Itemid'] = $userdir) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;
				
			case 'subscribe':
				switch ($query['layout']) {
					case 'check':
						if ($query['Itemid'] = $check) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
					case 'ppconfirm':
						if ($query['Itemid'] = $ppconfirm) {
							unset ($query['view']);
							unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
					case 'default':
					default:
						if ($query['Itemid'] = $subscribe) {
							unset ($query['view']);
							//unset ($query['layout']);
						} else {
							$query['Itemid'] = $default;
						}
						break;
				}
				break;
				
			default:
				break;
		}
	}

	return $segments;
}


function MUEParseRoute($segments)
{
	// Initialise variables.
	$vars = array();

	// Only run routine if there are segments to parse.
	if (count($segments) < 1) {
		return;
	}

	return $vars;
}
