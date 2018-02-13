<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MUEModelUsersub extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'usrsub_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mue.usersub.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	public function getTable($type = 'Usersub', $prefix = 'MUETable', $config = array()) 
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
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
	}
	
	public function save($data) {
		$cfg = MUEHelper::getConfig();
		$db = $this->getDbo();
		
		if (in_array($data['usrsub_status'],array("completed","accepted"))) {
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
		}
		$saved = parent::save($data);
		$subStatus = $this->updateUserSub((int) $data['usrsub_user']);
		if (!$this->updateMCSub(JFactory::getUser($data['usrsub_user']),$subStatus)) return false;
		if (!$this->updateCMSub(JFactory::getUser($data['usrsub_user']),$subStatus)) return false;
		if (!$this->updateBRSub(JFactory::getUser($data['usrsub_user']),$subStatus)) return false;
		return $saved;
	}

	public function delete(&$pks) {
		$pks = (array) $pks;
		$usersToUpdate = array();
		$table = $this->getTable();

		// Grab Users ids to update Sub info on
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				$usersToUpdate[] = $table->usrsub_user;
			}
		}

		// Call parent delete to delete sub
		$ret = parent::delete($pks);

		// if delete failed, return
		if (!$ret) return $ret;

		// Update users sub info
		foreach ($usersToUpdate as $u) {
			$subStatus = $this->updateUserSub((int)$u);
			if (!$this->updateMCSub(JFactory::getUser((int)$u),$subStatus)) return false;
			if (!$this->updateCMSub(JFactory::getUser((int)$u),$subStatus)) return false;
			if (!$this->updateBRSub(JFactory::getUser((int)$u),$subStatus)) return false;
		}

		return $ret;
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

	function getBRFields() {
		$db =& JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_ufields as f ';
		$qd.= ' WHERE f.published = 1 ';
		$qd .= ' && f.uf_type = "brlist"';
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

	function getCMFields() {
		$db =& JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_ufields as f ';
		$qd.= ' WHERE f.published = 1 ';
		$qd .= ' && f.uf_type = "cmlist"';
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

	function updateUserSub($userid) {
		$db =& JFactory::getDBO();
		$query = 'SELECT s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$query.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$query.= 'WHERE s.usrsub_status IN ("completed","accepted") && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="'.$userid.'" ';
		$query.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
		$db->setQuery($query);
		$sub = $db->loadObject();

		//Member Since
		$query = $db->getQuery(true);
		$query->select('s.usrsub_start');
		$query->from('#__mue_usersubs as s');
		$query->where('s.usrsub_status IN ("completed","accepted","verified")');
		$query->where('s.usrsub_user="'.$userid.'"');
		$query->order('s.usrsub_start ASC');
		$db->setQuery($query,0,1);
		$member_since = $db->loadResult();

		if ($sub) {
			$qud = $db->getQuery(true);
			$qud->update('#__mue_usergroup');
			$qud->set('userg_subexp = "'.$sub->usrsub_end.'"');
			$qud->set('userg_lastpaidvia = "'.$sub->usrsub_type.'"');
			$qud->set('userg_subendplanname = "'.$sub->sub_exttitle.'"');
			if ($member_since) $qud->set('userg_subsince = "'.$member_since.'"');
			$qud->where('userg_user = '.$userid);
			$db->setQuery($qud);
			$db->query();
			return true;
		} else {
			return false;
		}
	}

	function updateMCSub($user,$sub=false) {
		if (!$user->id) return false;
		$cfg = MUEHelper::getConfig();
		$db =& JFactory::getDBO();
		$date = new JDate('now');
		$usernotes = '';
		$ugq = "SELECT * FROM #__mue_usergroup WHERE userg_user = ".$user->id;
		$db->setQuery($ugq);
		$ug = $db->loadObject();
		foreach ($this->getMCFields() as $f) {
			if ($f->params->mcrgroup) {
				include_once '../components/com_mue/lib/mailchimp.php';

				if (strstr($f->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$f->uf_default,2);	}
				else { $mc_key = $cfg->mckey; $mc_list = $f->uf_default; }
				$mc = new MailChimpHelper($mc_key,$mc_list);
				$mcdata=array();
				
				if (!$sub) $mcdata[$f->params->mcrgroup]=$f->params->mcreggroup;
				else $mcdata[$f->params->mcrgroup]=$f->params->mcsubgroup;

				if ($f->params->mcsubsince) {
					if ($ug->userg_subsince != "0000-00-00")	$mcdata[$f->params->mcsubsince] = $ug->userg_subsince;
					else $mcdata[$f->params->mcsubsince] = "";
				}
				if ($f->params->mcsubexp) {
					if ($ug->userg_subexp != '0000-00-00') $mcdata[$f->params->mcsubexp] = $ug->userg_subexp;
					else $mcdata[$f->params->mcsubexp] = "";
				}
				if ($f->params->mcsubpaytype) $mcdata[$f->params->mcsubpaytype] = $ug->userg_lastpaidvia;
				
				$mcd=print_r($mcdata,true);
				if ($mc->subStatus($user->email)) {
					$mcresult = $mc->updateUser(array("email"=>$user->email),$mcdata,false,"html");
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

	function updateBRSub($user,$sub=false) {
		if (!$user->id) return false;
		$cfg = MUEHelper::getConfig();
		$db =& JFactory::getDBO();
		$date = new JDate('now');
		$usernotes = '';
		$ugq = "SELECT * FROM #__mue_usergroup WHERE userg_user = ".$user->id;
		$db->setQuery($ugq);
		$uginfo = $db->loadObject();
		foreach ($this->getBRFields() as $f) {

			// Update Subscription Info
			if ($f->params->brsubstatus) {
				$token = $cfg->brkey;
				$bronto = new Bronto_Api();
				$bronto->setToken($token);
				$bronto->login();

				// Get Contact
				$contactObject = $bronto->getContactObject();
				$contact = $contactObject->createRow();
				$contact->email = $user->email;
				$contact->read();

				// Set Member Status
				if ($sub) $contact->setField( $f->params->brsubstatus, $f->params->brsubtextyes );
				else $contact->setField( $f->params->brsubstatus, $f->params->brsubtextno );

				// Set Member Since
				if ( $f->params->brsubsince && $uginfo->userg_subsince != "0000-00-00" ) {
					$contact->setField( $f->params->brsubsince, $uginfo->userg_subsince );
				}

				// Set Member Exp
				if ( $f->params->brsubexp  && $uginfo->userg_subexp != '0000-00-00') {
					$contact->setField( $f->params->brsubexp, $uginfo->userg_subexp );
				}

				// Set Active/End Member Plan
				if ( $f->params->brsubplan ) {
					if ( !$sub ) {
						$contact->setField( $f->params->brsubplan, 'None' );
					} else {
						$contact->setField( $f->params->brsubplan, $uginfo->userg_subendplanname );
					}
				}

				// Save Contact
				$contact->save();

				$usernotes .= $date->toSql(true)." EMail Contact Updated on Bronto List ID: ".$f->uf_default."\r\n";
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
	
	function updateCMSub($user,$sub=false) {
		if (!$user->id) return false;
		$cfg = MUEHelper::getConfig();
		$db =& JFactory::getDBO();
		$date = new JDate('now');
		$usernotes = '';
		foreach ($this->getCMFields() as $f) {
			if ($f->params->msgroup->field) {
				include_once '../components/com_mue/lib/campaignmonitor.php';
				
				$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
				$cmdata=array();
				$cmdata = array('Name'=>$user->name, 'EmailAddress'=>$user->email, 'Resubscribe'=>'true');
				$customfields = array();
				$newcmf=array(); 
				$newcmf['Key']=$f->params->msgroup->field;
				if (!$sub) { $newcmf['Value']=$f->params->msgroup->reg; }
				else { $newcmf['Value']=$f->params->msgroup->sub; }
				$customfields[]=$newcmf;
				$cmdata['CustomFields']=$customfields;
				
				$cmd=print_r($cmdata,true);
				if ($cm->getSubscriberDetails($f->uf_default,$user->email)) {
					$cmresult = $cm->updateSubscriber($f->uf_default,$user->email,$cmdata);
					if ($cmresult) {  $usernotes .= $date->toSql(true)." EMail Subscription Updated on Campaign Monitor List #".$f->uf_default.' '.$cmd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on Campaign Monitor List #".$f->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
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
