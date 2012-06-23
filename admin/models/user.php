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

		// Get the pk of the record from the request.
		$pk = JRequest::getInt($key);
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
		$pk = (!empty($pk)) ? $pk : JRequest::getInt('id',0);;
		
		//set item variable
		$qu='SELECT username,block,email FROM #__users WHERE id = '.$pk;
		$this->_db->setQuery($qu);
		$item = $this->_db->loadObject();
		
		$fields = $this->getFields(false);
		foreach ($fields as $f) {
			$fn = $f->uf_sname;
			if (!isset($item->$fn)) $item->$fn = '';
		}
		
		//get data for user fields
		$q =  'SELECT u.*,f.uf_sname,f.uf_type FROM #__mue_users as u ';
		$q .= 'RIGHT JOIN #__mue_ufields as f ON u.usr_field = f.uf_id ';
		$q .= 'WHERE usr_user = '.$pk;
		$this->_db->setQuery($q); 
		$data=$this->_db->loadObjectList();
		
		$item->usr_user = $pk;
		foreach ($data as $d) {
			$fieldname = $d->uf_sname;
			if ($item->$fieldname == '') $item->$fieldname = $d->usr_data;
			if ($d->uf_type=="mcbox") $item->$fieldname = explode(" ",$item->$fieldname);
		}
				
		//get users group
		$qg = 'SELECT * FROM #__mue_usergroup WHERE userg_user = '.$item->usr_user;
		$this->_db->setQuery($qg);
		$uginfo = $this->_db->loadObject();
		$item->usergroup=$uginfo->userg_group;
		$item->lastupdate=$uginfo->userg_updtae;
		$item->usernotes=$uginfo->userg_notes;
		
		return $item;
	}
	
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$isNew = true;
		$userId=(int)$data['usr_user'];
		$usernotes=$data['usernotes'];
		$db		= $this->getDbo();
		
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$flist = $this->getFields(false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				$item->$fieldname = $data[$fieldname];
			}
			
			//Update Joomla User Info
			if ($userId != 0) {
				$user=JFactory::getUser($userId);
			} else {
				$user = new JUser;
				$udata['groups'][]=2;
			}
			$udata['name']=$item->fname." ".$item->lname;
			$udata['email']=$item->email;
			$udata['username']=$item->username;
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;
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
			
			
			//Save ContinuEd Userinfo
			$query	= $db->getQuery(true);
			$query->delete();
			$query->from('#__mue_users');
			$query->where('usr_user = '.$user->id);
			$db->setQuery((string)$query);
			$db->query();
			
			$flist = $this->getFields(false);
			foreach ($flist as $fl) {
				$fieldname = $fl->uf_sname;
				if (!$fl->uf_cms) {
					if ($fl->uf_type=="mcbox") $item->$fieldname = implode(" ",$item->$fieldname);
					//if ($fl->uf_type=='cbox') $item->$fieldname = ($item->$fieldname=='on') ? "1" : "0";
					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$item->$fieldname.'")';
					$db->setQuery($qf);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
			
			
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
		
		
		
		if (isset($user->id))
		{
			$this->setState($this->getName() . '.id', $user->id);
		}
		$this->setState($this->getName() . '.new', $isNew);
		
		//Update Users Group
		$query	= $db->getQuery(true);
		$query->delete();
		$query->from('#__mue_usergroup');
		$query->where('userg_user = '.$user->id);
		$db->setQuery((string)$query);
		$db->query();
		
		if (!empty($data['usergroup'])) {
			$qc = 'INSERT INTO #__mue_usergroup (userg_user,userg_group,userg_notes) VALUES ('.$user->id.','.(int)$data['usergroup'].',"'.$usernotes.'")';
			$db->setQuery($qc);
			if (!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
			} 
		}
		
		return true;
	}
	
	public function getUserGroups() {
		$query = 'SELECT ug_id as value, ug_name as text' .
				' FROM #__mue_ugroups' .
				' ORDER BY ug_name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	public function getFields($all = true) {
		$q  = 'SELECT * FROM #__mue_ufields WHERE published > 0';
		$q .= ' && uf_type != "message"';
		$q .= ' ORDER BY ordering';
		$this->_db->setQuery($q);
		$fields=$this->_db->loadObjectList();
		if ($all) {
			foreach ($fields as &$f) {
				switch ($f->uf_type) {
					case 'multi':
					case 'dropdown':
					case 'mcbox':
						$qo = 'SELECT opt_id as value, opt_text as text FROM #__mue_ufields_opts WHERE opt_field='.$f->uf_id.' && published > 0 ORDER BY ordering';
						$this->_db->setQuery($qo);
						$f->options = $this->_db->loadObjectList();
						break;
				}
			}
		}
		return $fields;
	}
	
	function validate($data, $group = null)
	{
		// Filter and validate the form data.
		$return	= true; //$form->validate($data, $group);

		// Check for an error.
		if (JError::isError($return)) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
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
				JError::raiseWarning(403, JText::_('COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF'));

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
					if (!$this->_db->query()) {
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		return true;
	}
}
