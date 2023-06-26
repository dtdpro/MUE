<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MUEModelUser extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'usr_user')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mue.user.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	protected function populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		//$table = $this->getTable();
		$key = 'usr_user';
		$input      = JFactory::getApplication()->input;

		// Get the pk of the record from the request.
		$pk = $input->get($key);
		$this->setState($this->getName().'.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}
	
	public function getTable($type = 'User', $prefix = 'MUETable', $config = array()) 
	{
		//there is no table
		return 0;
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		//there is no form
		return 0;
	}
	
	public function getItem($pk = null)
	{
		// Initialise variables.
		$input      = JFactory::getApplication()->input;
		$pk = (!empty($pk)) ? $pk : $input->get('id',0);
		$cfg = MUEHelper::getConfig();
		
		//set item variable
		$qu='SELECT id,username,block,email,requireReset FROM #__users WHERE id = '.$pk;
		$this->_db->setQuery($qu);
		$item = $this->_db->loadObject();
		
		$fields = $this->getFields(false);
		foreach ($fields as $f) {
			$fn = $f->uf_sname;
			if (!isset($item->$fn)) $item->$fn = '';
		}
		
		//get data for user fields
		$q =  'SELECT u.*,f.uf_sname,f.uf_type,f.uf_default FROM #__mue_users as u ';
		$q .= 'RIGHT JOIN #__mue_ufields as f ON u.usr_field = f.uf_id ';
		$q .= 'WHERE usr_user = '.$pk.' && f.published=1 && f.uf_type NOT IN ("message","captcha","brlist")';
		$this->_db->setQuery($q); 
		$data=$this->_db->loadObjectList();
		
		$item->usr_user = $pk;
		foreach ($data as $d) {
			$fieldname = $d->uf_sname;
			if ($item->$fieldname == '') $item->$fieldname = $d->usr_data;
			if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") {
				$item->$fieldname = explode(" ",$item->$fieldname);
			} else if ($d->uf_type == 'mailchimp') {
				include_once JPATH_ROOT.'/components/com_mue/lib/mailchimp.php';

				if (strstr($d->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$d->uf_default,2);	}
				else { $mc_key = $cfg->mckey; $mc_list = $d->uf_default; }
				$mc = new MailChimpHelper($mc_key,$mc_list);
				$mcresult = $mc->subStatus($item->email);
				if ($mcresult) $onlist="1";
				else $onlist="0";
				$item->$fieldname=$onlist;
			} else if ($d->uf_type == 'cmlist') {
				include_once JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php';
				$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
				$cmresult = $cm->getSubscriberDetails($d->uf_default,$item->email);
				if ($cmresult){ if ($cmresult->State=="Active")  { $onlist="0";} else { $onlist="0"; } }
				else $onlist="0";
				$item->$fieldname=$onlist;
			} else if ($d->uf_type == 'brlist') {
				$token = $cfg->brkey;
				$bronto = new Bronto_Api();
				$bronto->setToken($token);
				$bronto->login();
				$contactObject = $bronto->getContactObject();
				$contact = $contactObject->createRow();
				$contact->email = $item->email;
				$contact->read();
				$onlist = false;
				if ($contact->status == 'active' || $contact->status == 'onboarding') {
					if (isset($contact->listIds)) {
						if ( in_array( $d->uf_default, $contact->listIds ) ) {
							$onlist = "1";
						} else {
							$onlist = "0";
						}
					} else {
						$onlist = "0";
					}
				} else {
					$onlist = "0";
				}
				$item->$fieldname=$onlist;
			} 
		}

				
		//get users group
		$qg = 'SELECT * FROM #__mue_usergroup WHERE userg_user = '.$item->usr_user;
		$this->_db->setQuery($qg);
		$uginfo = $this->_db->loadObject();
		if ($uginfo) {
			$item->usergroup=$uginfo->userg_group;
			$item->lastupdate=$uginfo->userg_update;
			$item->usernotes=$uginfo->userg_notes;
			$item->usersiteurl=$uginfo->userg_siteurl;
		}
		
		return $item;
	}
	
	public function save($data)
	{
		// Initialise variables;
		$userId=(int)$data['usr_user'];
		$isNew = $userId ? false : true;
		$usernotes=$data['usernotes'];
		$db		= $this->getDbo();
		$date = new JDate('now');
		$cfg = MUEHelper::getConfig();
		$item=null;
		
		JPluginHelper::importPlugin('user');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$flist = $this->getFields(false);
			$item = new stdClass();
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				$item->$fieldname = $data[$fieldname];
			}
			
			//Update Joomla User Info
			if ($userId != 0) {
				$user=JUser::getInstance($userId);
				$oldemail = $user->email;
			} else {
				$user = new JUser;
				$udata['groups'][]=2;
				$oldemail = false;
			}
			$udata['name']=$item->fname." ".$item->lname;
			$udata['email']=$item->email;
			$udata['username']=$item->username;
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;
			$udata['requireReset']=$data['requireReset'];
			$udata['block']=$item->block;
			if (!$user->bind($udata)) {
				$this->setError('Bind Error: '.$user->getError());
				return false;
			}
			if (!$user->save()) {
				$this->setError('Save Error:'.$user->getError());
				return false;
			}
			
			//remove joomla user info from item
			unset($item->email);
			unset($item->cemail);
			unset($item->password);
			unset($item->cpassword);
			unset($item->block);
			unset($item->username);
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
				
		$this->setState($this->getName() . '.id', $user->id);
		
		//Update Users Group
		$ginfo=$this->getGroupInfo((int)$data['usergroup']);
		$item->site_url = $data['usersiteurl'];
		$item->user_group = $ginfo->ug_name;
		$item->username = $user->username;
		
		if ($isNew) {
			$query	= $db->getQuery(true);
			$query->delete();
			$query->from('#__mue_usergroup');
			$query->where('userg_user = '.$user->id);
			$db->setQuery((string)$query);
			$db->execute();

			$usernotes = $date->toSql(true)." User Added by Admin\r\n";
			if (!empty($data['usergroup'])) {
				$qc = 'INSERT INTO #__mue_usergroup (userg_user,userg_group,userg_notes,userg_siteurl,userg_update) VALUES ('.$user->id.','.(int)$data['usergroup'].',"'.$usernotes.'","'.$item->site_url.'","'.$date->toSql(true).'")';
				$db->setQuery($qc);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				} 
			}
		} else {
			$hasusergq = "SELECT * FROM #__mue_usergroup WHERE userg_user = ".$user->id;
			$db->setQuery($hasusergq);
			$hasuserg = $db->loadObject();
			$usernotes = $date->toSql(true)." User Updated by Admin\r\n";
			
			if ($hasuserg) {
				$qud = 'UPDATE #__mue_usergroup SET userg_group = '.(int)$data['usergroup'].', userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
				$db->setQuery($qud);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			} else {
				$qc = 'INSERT INTO #__mue_usergroup (userg_user,userg_group,userg_notes,userg_siteurl,userg_update) VALUES ('.$user->id.','.(int)$data['usergroup'].',"'.$usernotes.'","'.$item->site_url.'","'.$date->toSql(true).'")';
				$db->setQuery($qc);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		}
		
		//Save MUE User Data	
		$flist = $this->getFields(false);
		$optfs = array();
		$moptfs = array();
		foreach ($flist as $fl) {
			$fieldname = $fl->uf_sname;
			if (!$fl->uf_cms) {
				if ($fl->uf_type=="mcbox" || $fl->uf_type=="mlist") $item->$fieldname = implode(" ",$item->$fieldname);
				
				if ($fl->uf_type=="multi" || $fl->uf_type=="dropdown") $optfs[]=$fl->uf_sname;
				if ($fl->uf_type=="mcbox" || $fl->uf_type=="mlist") $moptfs[]=$fl->uf_sname;
				
				if (!$isNew) {
					$qd=$db->getQuery(true);
					$qd->delete();
					$qd->from("#__mue_users");
					$qd->where("usr_user = ".$user->id);
					$qd->where('usr_field = '.$fl->uf_id);
					$db->setQuery($qd);
					$db->execute();
				}
				$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$item->$fieldname.'")';
				
				$db->setQuery($qf);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		}
		
		$odsql = "SELECT * FROM #__mue_ufields_opts";
		$db->setQuery($odsql);
		$optionsdata = array();
		$optres = $db->loadObjectList();
		foreach ($optres as $o) {
			$optionsdata[$o->opt_id]=$o->opt_text;
		}
		
		//Update Mailing lists if not a new user, Admin can subscribe user but they must confirm, only update user information
		if (!$isNew) {
			$usernotes = "";

			//Active Campaign Integration
			$aclists = $this->getFields(false,"aclist");
			if (count($aclists) > 0) {
				// Load up AC connector
				require_once(JPATH_ROOT.'/components/com_mue/lib/activecampaign.php');
				$acClient = new ActiveCampaign($cfg->ackey,$cfg->acurl);

				//get first list
				$aclistFirst = $aclists[0];

				//setup field data
				$fieldData = [];
				if ( $aclistFirst->params->acvars) {
					// User Fields
					foreach ($aclistFirst->params->acvars as $acFieldId => $mueVar) {
						if ($mueVar) {
							$fieldVal = '';
							if ( $mueVar == "user_group" ) {
								$fieldVal = $ginfo->ug_name;
							} else if ( in_array( $mueVar, $optfs ) ) {
								$fieldVal = $optionsdata[ $item->$mueVar ];
							} else if ( in_array( $mueVar, $moptfs ) ) {
								$fv = '';
								foreach ( explode( " ", $item->$mueVar ) as $mfo ) {
									$fv .= $optionsdata[ $mfo ] . " ";
								}
								$fieldVal= $fv;
							} else {
								$fieldVal = $item->$mueVar;
							}
							$fieldDataEntry = [];
							$fieldDataEntry['field'] = $acFieldId;
							$fieldDataEntry['value'] = $fieldVal;
							$fieldData[] = $fieldDataEntry;
						}
					}
				}

				// sync contact
				$acClient->syncContact($oldemail,$item->fname, $item->lname,$fieldData);

				// get contact
				$contact = $acClient->getContact($oldemail);

				// pause
				sleep(1);

				// update email address
				if ($contact) {
					if ($user->email != $oldemail) {
						$acClient->updateContactEmail($contact['id'],$user->email);
					}
				}
			}
			
			//Campaign Monitor Integration
			$cmlists = $this->getFields(false,"cmlist");
			require_once(JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php');
			foreach ($cmlists as $cmlist) {
				$cmuf=$cmlist->uf_sname;
				if ($item->$cmuf) {
					$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
					$cmdata = array('Name'=>$item->fname.' '.$item->lname, 'EmailAddress'=>$user->email, 'Resubscribe'=>'true');
					$customfields = array();
					if ($cmlist->params->cmfields) {
						$othervars=$cmlist->params->cmfields;
						foreach ($othervars as $cmf=>$mue) {
							if ($cmlist->params->cmfieldtypes->$cmf == "MultiSelectMany") {
								if (in_array($mue,$moptfs)) {
									foreach (explode(" ",$item->$mue) as $mfo) {
										$newcmf=array();
										$newcmf['Key']=$cmf;
										$newcmf['Value'] = $optionsdata[$mfo];
										$customfields[]=$newcmf;
									}
								} else {
									$newcmf=array();
									$newcmf['Key']=$cmf;
									$newcmf['Value'] == "";
									$newcmf['Clear']='true';
									$customfields[]=$newcmf;
								}
							} else {
								if ($mue) {
									$newcmf=array();
									$newcmf['Key']=$cmf;
									if (in_array($mue,$optfs)) $newcmf['Value'] = $optionsdata[$item->$mue];
									else if (in_array($mue,$moptfs)) {
										$newcmf['Value'] = "";
										foreach (explode(" ",$item->$mue) as $mfo) {
											$newcmf['Value'] .= $optionsdata[$mfo]." ";
										}
									}
									else $newcmf['Value'] = $item->$mue;
								}
								if (!$mue || $newcmf['Value'] == "") $newcmf['Clear']='true';
								$customfields[]=$newcmf;
							}
						}
					}
					$cmdata['CustomFields']=$customfields;
					$cmd=print_r($cmdata,true);
					if ($cm->getSubscriberDetails($cmlist->uf_default,$oldemail)) {
						$cmresult = $cm->updateSubscriber($cmlist->uf_default,$oldemail,$cmdata);
						if ($cmresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated by Admin on Campaign Monitor List #".$cmlist->uf_default."\r\n".$cmd."\r\n"; }
						else { $usernotes .= $date->toSql(true)." Could not update EMail subscription by Admin on Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error."\r\n".$cmd."\r\n"; }
					} else {
						$cmdata['Resubscribe'] = true;
						$cmdata['RestartSubscriptionBasedAutoResponders'] = true;
						$cmresult = $cm->addSubscriber($cmlist->uf_default,$cmdata);
						if ($cmresult) { $usernotes .= $date->toSql(true)." EMail Subscribed to Campaign Monitor List #".$cmlist->uf_default." by admin, confirmation required \r\n".$cmd."\r\n"; }
						else { $usernotes .= $date->toSql(true)." Could not subscribe EMail to Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error."\r\n".$cmd."\r\n"; }
					}
				} else {
					$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
					$cmresult = $cm->removeSubscriber($cmlist->uf_default,$oldemail);
					if ($cmresult) { $usernotes .= $date->toSql(true)." EMail Unsubscribed from Campaign Monitor List #".$cmlist->uf_default."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not unsubscribe EMail from Campaign Monitor List #".$cmlist->uf_default." Error: ".$cm->error."\r\n"; }
				}

			} 
			
			//Update usernotes
			$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
			$db->setQuery($qud);
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		} else {
			//Populate list fields as not on for new users
			$lists = array_merge($this->getFields(false,"cmlist"),$this->getFields(false,"mailchimp"));
			foreach ($lists as $l) {
				$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$l->uf_id.'","0")';
				
				$db->setQuery($qf);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		}
		
		// Update Joomla User Groups
		// Delete the old user group maps.
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_db->quoteName('#__user_usergroup_map'));
		$query->where($this->_db->quoteName('user_id') . ' = ' . (int) $user->id);
		$this->_db->setQuery($query);
		$this->_db->execute();
		
		// Set the new user group maps.
		$query->clear();
		$query->insert($this->_db->quoteName('#__user_usergroup_map'));
		$query->columns(array($this->_db->quoteName('user_id'), $this->_db->quoteName('group_id')));
		$query->values($user->id . ', ' . implode('), (' . $user->id . ', ', $data['groups']));
		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}
	
	public function getUserGroups() {
		$query = 'SELECT ug_id as value, ug_name as text' .
				' FROM #__mue_ugroups' .
				' ORDER BY ug_name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	function getGroupInfo($groupid) {
		if (!$groupid) {
			return false;
		}
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mue_ugroups ';
		$query.= 'WHERE ug_id="'.$groupid.'"';
		$db->setQuery($query);
		$usergroup = $db->loadObject();
		return $usergroup;
	}
	
	public function getFields($all = true, $type = "") {
		$q  = 'SELECT * FROM #__mue_ufields WHERE published > 0';
		if ($type) $q .= ' && uf_type = "'.$type.'"';
		else $q .= ' && uf_type NOT IN ("message","captcha","brlist")';
		$q .= ' ORDER BY ordering';
		$this->_db->setQuery($q);
		$fields=$this->_db->loadObjectList();
		
		foreach ($fields as &$f) {
			$registry = new JRegistry();
			$registry->loadString($f->params);
			$f->params = $registry->toObject();
			if ($all) {
				switch ($f->uf_type) {
					case 'multi':
					case 'dropdown':
					case 'mcbox':
					case 'mlist':
						$qo = 'SELECT opt_id as value, opt_text as text FROM #__mue_ufields_opts WHERE opt_field='.$f->uf_id.' && published > 0 ORDER BY ordering';
						$this->_db->setQuery($qo);
						$f->options = $this->_db->loadObjectList();
						break;
				}
			}
		}
		return $fields;
	}
	
	function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$return	= true; //$form->validate($data, $group);

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($data->getErrors() as $message) {
				$this->setError(JText::_($message));
			}

			return false;
		}

		return $data;
	}
	
	function block(&$pks, $value = 1)
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		// Check if I am a Super Admin
		$iAmSuperAdmin	= $user->authorise('core.admin');
		
		$pks		= (array) $pks;

		JPluginHelper::importPlugin('user');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($value == 1 && $pk == $user->get('id'))
			{
				// Cannot block yourself.
				unset($pks[$i]);

			}
			else 
			{
				$allow	= $user->authorise('core.edit.state', 'com_users');
				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				

				if ($allow)
				{
					$sql = 'UPDATE #__users SET block = '.$value.' WHERE id = '.$pk;
					$this->_db->setQuery($sql);
					if (!$this->_db->execute()) {
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
				}
			}
		}

		return true;
	}
	
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);
	
		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}
	
		if (empty($pks))
		{
			$this->setError(JText::_('COM_MUE_USERS_NO_ITEM_SELECTED'));
			return false;
		}
	
		$done = false;
	
		if (!empty($commands['group_id']))
		{
			$cmd = JArrayHelper::getValue($commands, 'group_action', 'add');
	
			if (!$this->batchUser((int) $commands['group_id'], $pks, $cmd))
			{
				return false;
			}
			$done = true;
		}
		
		if (!empty($commands['reset_id']))
		{
			if (!$this->batchReset($pks, $commands['reset_id']))
			{
				return false;
			}
			$done = true;
		}
		
	
		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}
	
		// Clear the cache
		$this->cleanCache();
	
		return true;
	}
	
	public function batchReset($user_ids, $action)
	{
		// Set the action to perform
		if ($action === 'yes')
		{
			$value = 1;
		}
		else
		{
			$value = 0;
		}
		
		// Prune out the current user if they are in the supplied user ID array
		$user_ids = array_diff($user_ids, array(JFactory::getUser()->id));
		
		// Get the DB object
		$db = $this->getDbo();
		JArrayHelper::toInteger($user_ids);
		$query = $db->getQuery(true);
		
		// Update the reset flag
		$query->update($db->quoteName('#__users'))
		->set($db->quoteName('requireReset') . ' = ' . $value)
		->where($db->quoteName('id') . ' IN (' . implode(',', $user_ids) . ')');
		$db->setQuery($query);
		
		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
	
	public function batchUser($group_id, $user_ids, $action)
	{
		// Get the DB object
		$db = $this->getDbo();
	
		JArrayHelper::toInteger($user_ids);
	
		// Non-super admin cannot work with super-admin group
		if ((!JFactory::getUser()->get('isRoot') && JAccess::checkGroup($group_id, 'core.admin')) || $group_id < 1)
		{
			$this->setError(JText::_('COM_USERS_ERROR_INVALID_GROUP'));
			return false;
		}
	
		switch ($action)
		{
			// Sets users to a selected group
			case 'set':
				$doDelete	= 'all';
				$doAssign	= true;
				break;
	
				// Remove users from a selected group
			case 'del':
				$doDelete	= 'group';
				break;
	
				// Add users to a selected group
			case 'add':
			default:
				$doAssign	= true;
				break;
		}
	
		// Remove the users from the group if requested.
		if (isset($doDelete))
		{
			$query = $db->getQuery(true);
	
			// Remove users from the group
			$query->delete($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('user_id') . ' IN (' . implode(',', $user_ids) . ')');
	
			// Only remove users from selected group
			if ($doDelete == 'group')
			{
				$query->where($db->quoteName('group_id') . ' = ' . (int) $group_id);
			}
	
			$db->setQuery($query);
	
			// Check for database errors.
			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
	
		// Assign the users to the group if requested.
		if (isset($doAssign))
		{
			$query = $db->getQuery(true);
	
			// First, we need to check if the user is already assigned to a group
			$query->select($db->quoteName('user_id'));
			$query->from($db->quoteName('#__user_usergroup_map'));
			$query->where($db->quoteName('group_id') . ' = ' . (int) $group_id);
			$db->setQuery($query);
			$users = $db->loadColumn();
	
			// Build the values clause for the assignment query.
			$query->clear();
			$groups = false;
			foreach ($user_ids as $id)
			{
				if (!in_array($id, $users))
				{
					$query->values($id . ',' . $group_id);
					$groups = true;
				}
			}
	
			// If we have no users to process, throw an error to notify the user
			if (!$groups)
			{
				$this->setError(JText::_('COM_USERS_ERROR_NO_ADDITIONS'));
				return false;
			}
	
			$query->insert($db->quoteName('#__user_usergroup_map'));
			$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
			$db->setQuery($query);
	
			// Check for database errors.
			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
	
		return true;
	}
	
	public function getAssignedGroups($userId = null)
	{
		if (empty($userId))
		{
			$result = array();
			$config = JComponentHelper::getParams('com_users');
			if ($groupId = $config->get('new_usertype'))
			{
				$result[] = $groupId;
			}
		}
		else
		{
			$result = JUserHelper::getUserGroups($userId);
		}
	
		return $result;
	}
}
