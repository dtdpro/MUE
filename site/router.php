<?php

defined('_JEXEC') or die;

function MUEBuildRoute(&$query)
{
	// Declare static variables.
	static $items;
	static $default;
	static $userreg;
	static $profile;
	static $subs;
	static $proedit;
	static $login;
	static $logout;
	static $lost;

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
			// Check to see if we have found the registration menu item.
			if (empty($userreg) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'userreg')) {
				$userreg = $items[$i]->id;
			}

			// Check to see if we have found the log in/out menu item.
			if (empty($login) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'login') && !empty($items[$i]->query['layout']) && ($items[$i]->query['layout'] == 'login')) {
				$login = $items[$i]->id;
			}
			if (empty($logout) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'login') && !empty($items[$i]->query['layout']) && ($items[$i]->query['layout'] == 'logout')) {
				$logout = $items[$i]->id;
			}
			
			// Check to see if we have found the profile,profile edit, and records menu item.
			if (empty($profile) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'user') && !empty($items[$i]->query['layout']) && ($items[$i]->query['layout'] == 'profile')) {
				$profile = $items[$i]->id;
			}
			if (empty($subs) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'user') && !empty($items[$i]->query['layout']) && ($items[$i]->query['layout'] == 'subs')) {
				$subs = $items[$i]->id;
			}
			if (empty($proedit) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'user') && !empty($items[$i]->query['layout']) && ($items[$i]->query['layout'] == 'proedit')) {
				$peoedit = $items[$i]->id;
			}
			
			// Check to see if we have found the lost info menu item.
			if (empty($lost) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'lost')) {
				$lost = $items[$i]->id;
			}			
		}
	}

	if (!empty($query['view'])) {
		switch ($query['view']) {
			case 'userreg':
				if ($query['Itemid'] = $userreg) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
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
				}
				break;

			case 'lost':
				if ($query['Itemid'] = $lost) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
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
