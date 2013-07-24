<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

class MUEModelMCList extends JModelLegacy
{
	protected function populateState()
	{
		// Set the component (option) we are dealing with.
		$field = JRequest::getInt('field');
		$this->setState('mclist.field', $field);
	}
	
	function getUFields() {
		$app = JFactory::getApplication('administrator');
		$query = $this->_db->getQuery(true);
		$query->select('uf_sname AS value, CONCAT(uf_name," [",uf_sname,"]") AS text');
		$query->from('#__mue_ufields');
		$query->where('uf_type NOT IN ("mailchimp","captcha")');
		$query->where('published = 1');
		$query->order('ordering');
		$this->_db->setQuery($query);
		$data=$this->_db->loadObjectList();
		
		$fields = array();
		$fields[] = JHtml::_('select.option', "user_group","MUE User Group [user_group]");
		$fields[] = JHtml::_('select.option', "site_url","Site URL [site_url]");
		foreach ($data as $d) {
			$fields[] = JHtml::_('select.option', $d->value,$d->text);
		}
		return $fields;
	}

	function syncField($field) {
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/mailchimp.php');
		
		if (!$field) { $this->setError("Filed ID Not Provided"); return false; }
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->mckey) { $this->setError("MailChimp not Configured"); return false; }
		$mc = new MailChimp($cfg->mckey);
		if (!$listmembers=$mc->getListMembers($list->uf_default)) {
			$this->setError($mc->error);
			return false;
		}
		
		$userids = array();
		foreach ($listmembers as $l) {
			$q=$db->getQuery(true);
			$q->select('id');
			$q->from('#__users');
			$q->where('email="'.$l->email.'"');
			$db->setQuery($q);
			$res=$db->loadResult();
			if ($res) $userids[]=$res;
		}
		
		$query = $db->getQuery(true);
		$query->select("id");
		$query->from('#__users');
		$query->where('id NOT IN ('.implode(",",$userids).')');
		$db->setQuery($query);
		$notinlist = $db->loadColumn();
		
		$query	= $db->getQuery(true);
		$query->delete();
		$query->from('#__mue_users');
		$query->where('usr_field = '.$field);
		$db->setQuery((string)$query);
		$db->query();
		
		foreach ($userids as $u) {
			$q=$db->getQuery(true);
			$q->insert('#__mue_users');
			$q->columns('usr_data,usr_field,usr_user');
			$q->values("1,$field,$u");
			$db->setQuery($q);
			if (!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
		
		foreach ($notinlist as $u) {
			$q=$db->getQuery(true);
			$q->insert('#__mue_users');
			$q->columns('usr_data,usr_field,usr_user');
			$q->values("0,$field,$u");
			$db->setQuery($q);
			if (!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
		
		
		return true;
	}
	function getList()
	{
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/mailchimp.php');
		
		// Initialise variables.
		$field = $this->getState('mclist.field');
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->mckey) return false;
		$mc = new MailChimp($cfg->mckey);
		$mclist=$mc->getLists($list->uf_default);
		
		$list->list_info=$mclist[0];
		
		$list->list_igroups = $mc->getListInterestGroupings($list->uf_default);
		
		$list->list_mvars = $mc->getListMergeVars($list->uf_default);
		
		if (property_exists($list, 'params'))
		{
			$registry = new JRegistry();
			$registry->loadString($list->params);
			$list->params = $registry->toObject();
		}

		return $list;
	}

	public function save($data,$field)
	{
		$parameter = new JRegistry;
		$parameter->loadArray($data);
		$params = (string)$parameter;
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__mue_ufields');
		$query->set('params = '.$db->quote($params));
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		if ($db->query()) { return true; }
		else { $this->setError($db->getError()); return false; }
	}
}
