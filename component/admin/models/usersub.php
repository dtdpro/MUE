<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MUEModelUserSub extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'usrsub_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mue.usersub.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	public function getTable($type = 'UserSub', $prefix = 'MUETable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mue.usersub', 'usersub', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	public function getScript() 
	{
		return 'administrator/components/com_mue/models/forms/usersub.js';
	}
	
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mue.edit.usersub.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('usersub.id') == 0) {
				$app = JFactory::getApplication();
				
			}
		}
		return $data;
	}
	
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
	}
	
	public function save($data) {
		$cfg = MUEHelper::getConfig();
		$db = $this->getDbo();
		
		if (in_array($data['usrsub_status'],array("completed","verified","accepted"))) {
			if ($cfg->subgroup > 2) {
				$query = $db->getQuery(true);
				$query->select($db->quoteName('user_id'));
				$query->from($db->quoteName('#__user_usergroup_map'));
				$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
				$query->where($db->quoteName('user_id') . ' = ' . (int) $data['usrsub_user']);
				$db->setQuery($query);
				$hasgroup = $db->loadResult();
				if (!$hasgroup) {
					$query->clear();
					$query->insert($db->quoteName('#__user_usergroup_map'));
					$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
					$query->values((int) $data['usrsub_user'] . ',' . $cfg->subgroup);
					$db->setQuery($query);
					$db->query();
				}
			}
			if (!$this->updateMCSub(JFactory::getUser($data['usrsub_user']),true)) return false;
		} else {
			if ($cfg->subgroup > 2) {
				$query = $db->getQuery(true);
				
				// Remove user from the sub group
				$query->delete($db->quoteName('#__user_usergroup_map'));
				$query->where($db->quoteName('user_id') . ' = ' . (int) $data['usrsub_user']);
				$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
				$db->setQuery($query);
				$db->query();
			}
			if (!$this->updateMCSub(JFactory::getUser($data['usrsub_user']),false)) return false;;

		}
		return parent::save($data);
	}

	public function copy(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$pks = (array) $pks;
		$table = $this->getTable();
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
		
			if ($table->load($pk))
			{
			
				$table->usrsub_id=0;
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				} 
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}
	
	public function getHistory($usid) {
		$query = 'SELECT *' .
				' FROM #__mue_usersubs_log' .
				' WHERE usl_usid = '.$usid .
				' ORDER BY usl_time DESC';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	function getMCFields() {
		$db =& JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_ufields as f ';
		$qd.= ' WHERE f.published = 1 ';
		$qd .= ' && f.uf_type = "mailchimp"';
		$qd.= ' ORDER BY f.ordering';
		$db->setQuery( $qd );
		$ufields = $db->loadObjectList();
		foreach ($ufields as &$f) {
			$registry = new JRegistry();
			$registry->loadString($f->params);
			$f->params = $registry->toObject();
		}
		return $ufields;
	}
	
	function updateMCSub($user,$sub=false) {
		if (!$user->id) return false;
		$cfg = MUEHelper::getConfig();
		$db =& JFactory::getDBO();
		$date = new JDate('now');
		$usernotes = '';
		foreach ($this->getMCFields() as $f) {
			if ($f->params->mcrgroup) {
				include_once '../components/com_mue/lib/mailchimp.php';
		
				$mc = new MailChimp($cfg->mckey,$f->uf_default);
				$mcdata=array();
				if ($sub) $mcdata['GROUPINGS']=array(array("name"=>$f->params->mcrgroup,"groups"=>$f->params->mcsubgroup));
				else $mcdata['GROUPINGS']=array(array("name"=>$f->params->mcrgroup,"groups"=>$f->params->mcreggroup));
				$mcd=print_r($mcdata,true);
				if ($mc->subStatus($user->email)) {
					$mcresult = $mc->updateUser($user->email,$mcdata,false,"html");
					if ($mcresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$f->uf_default.' '.$mcd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$f->uf_default." Error: ".$mc->error."\r\n"; }
				}
			}
		}
		//Update update date
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}
}
