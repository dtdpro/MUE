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
		$query->where('uf_cms = 0');
		$query->where('published = 1');
		$query->order('ordering');
		$this->_db->setQuery($query);
		$data=$this->_db->loadObjectList();
		
		$fields = array();
		$fields[] = JHtml::_('select.option', "user_group","MUE User Group [user_group]");
		$fields[] = JHtml::_('select.option', "site_url","Site URL [site_url]");
		$fields[] = JHtml::_('select.option', "username","Username [username]");
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
		
		$mclist=$mc->getLists($list->uf_default);
		$list->list_info=$mclist[0];
		$totalmembers = $list->list_info->stats->member_count;
		
		$listmembers = array();
		$start=0;
		while (count($listmembers) < $totalmembers) {
			$lm = array();
			if (!$lm=$mc->getListMembers($list->uf_default,500,$start)) {
				$this->setError($mc->getError());
				return false;
			}
			$listmembers = array_merge($lm,$listmembers);
			$start = $start+1;
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
		
		$resinfo['members']=count($listmembers);
		$resinfo['users']=count($userids);
		
		return $resinfo; 
	}
	
	function syncList($field) {
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
		
		if (property_exists($list, 'params'))
		{
			$registry = new JRegistry();
			$registry->loadString($list->params);
			$list->params = $registry->toObject();
		}
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->mckey) { $this->setError("MailChimp not Configured"); return false; }
		$mc = new MailChimp($cfg->mckey);
		
		//Users in list
		$query = $db->getQuery(true);
		$query->select("usr_user");
		$query->from('#__mue_users');
		$query->where('usr_field='.$field);
		$query->where('usr_data = "1"');
		$db->setQuery($query);
		$inlist = $db->loadColumn();
		
		//Fields for Merge Data
		$muef=array("fname","lname","email");
		foreach ($list->params->mcvars as $mcv=>$mue) { $muef[]=$mue; }
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_sname IN ("'.implode('","',$muef).'")');
		$db->setQuery($query); 
		$muefields = $db->loadObjectList(); 
		
		//Users
		$query = $db->getQuery(true);
		$query->select('u.*');
		$query->from('#__users as u');
		$query->select('ug.userg_update as lastUpdate,ug.userg_notes,ug.userg_siteurl');
		$query->join('LEFT', '#__mue_usergroup AS ug ON u.id = ug.userg_user');
		$query->select('g.ug_name');
		$query->join('LEFT', '#__mue_ugroups AS g ON ug.userg_group = g.ug_id');
		$query->where('u.id IN ('.implode(',',$inlist).')');
		$db->setQuery($query);
		$users=$db->loadObjectList();
		
		//User Sub Status
		if ($cfg->subscribe) {
			foreach ($users as &$i) {
				$query = $db->getQuery(true);
				$query->select('s.*,p.*,DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft');
				$query->from('#__mue_usersubs as s');
				$query->join('LEFT','#__mue_subs AS p ON s.usrsub_sub = p.sub_id');
				$query->where('s.usrsub_status IN ("completed","verified","accepted")');
				$query->where('s.usrsub_user="'.$i->id.'"');
				$query->order('daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC');
				$db->setQuery($query,0,1);
				$sub = $db->loadObject();
				if ($sub) {
					if ((int)$sub->daysLeft > 0) {
						$i->substatus=true;
					} else $i->substatus=false;
				} else {
					$i->substatus=false;
				}
			}
		}
		
		//User Field Data
		foreach ($muefields as $f) {
			if (!$f->uf_cms) {
				$sname = $f->uf_sname;
				$ud = Array();
				$fid=$f->uf_id;
				$q2 = $db->getQuery(true);
				$q2->select('usr_user,usr_data');
				$q2->from('#__mue_users');
				$q2->where('usr_field = '.$fid);
				$q2->where('usr_user IN ('.implode(',',$inlist).')');
				$db->setQuery($q2);
				$opts = $db->loadObjectList();
				foreach ($opts as $o) {
					$uid = $o->usr_user;
					$ud[$uid] = $o->usr_data;
				}
				$udata->$sname = $ud;
			}
		} 
		
		//Field Answers
		$fids = Array();
		$optfs = Array();
		$moptfs = array();
		foreach ($muefields as $f) {
			if (!$f->uf_cms) $fids[]=$f->uf_id;
			if ($f->uf_type=="multi" || $f->uf_type=="dropdown") $optfs[]=$f->uf_sname;
			if ($f->uf_type=="mcbox" || $f->uf_type=="mlist") $moptfs[]=$f->uf_sname;
		}
		$q2 = $db->getQuery(true);
		$q2->select('*');
		$q2->from('#__mue_ufields_opts');
		$q2->where('opt_field IN ('.implode(",",$fids).')');
		$q2->where('published >= 1');
		$db->setQuery($q2);
		$opts = $db->loadObjectList();
		$optionsdata = Array();
		foreach ($opts as $o) {
			$optionsdata[$o->opt_id] = $o->opt_text;
		}
		
		//Build MC Batch Data
		$mcbatch=array(); 
		$resinfo=array();
		$resinfo['errors']=array();
		foreach ($users as $u) {
			$userid=$u->id; 
			$mcdata = array('FNAME'=>$udata->fname[$userid], 'LNAME'=>$udata->lname[$userid],'EMAIL'=>$u->email);
			if ($list->params->mcvars) {
				$othervars=$list->params->mcvars;
				foreach ($othervars as $mcv=>$mue) { $ufield = $udata->$mue;
					if ($mue == 'username') { $mcdata[$mcv] = $u->username; }
					else if ($mue == 'user_group') { $mcdata[$mcv] = $u->ug_name; }
					else if ($mue == 'site_url') { $mcdata[$mcv] = $u->userg_siteurl; }
					else if ($mue && $udata->$mue) { 
						if (in_array($mue,$optfs)) $mcdata[$mcv] = $optionsdata[$ufield[$userid]];
						else if (in_array($mue,$moptfs)) {
							$mcdata[$mcv] = "";
							foreach (explode(" ",$ufield[$userid]) as $mfo) {
								$mcdata[$mcv] .= $optionsdata[$mfo]." ";
							}
						}
						else $mcdata[$mcv] = $ufield[$userid];
					}
				}
			}
			if ($list->params->mcrgroup && $cfg->subscribe) {
				if (!$u->substatus) $mcdata['GROUPINGS']=array(array("name"=>$list->params->mcrgroup,"groups"=>$list->params->mcreggroup));
				else $mcdata['GROUPINGS']=array(array("name"=>$list->params->mcrgroup,"groups"=>$list->params->mcsubgroup));
			}
			$mcbatch[] = $mcdata;
			if (count($mcbatch) == 3000) {
				$result = $mc->listBatchSubscribe($mcbatch,$list->uf_default);
				$resinfo['add_count'] = $res_info['add_count'] + $result->add_count;
				$resinfo['update_count'] = $res_info['update_count'] + $result->update_count;
				$resinfo['error_count'] = $res_info['error_count'] + $result->error_count;
				$resinfo['errors'] = array_merge($resinfo['errors'],$result->errors);
				$mcbatch = array();
			}
		}
		
		$result = $mc->listBatchSubscribe($mcbatch,$list->uf_default);
		$resinfo['add_count'] = $res_info['add_count'] + $result->add_count;
		$resinfo['update_count'] = $res_info['update_count'] + $result->update_count;
		$resinfo['error_count'] = $res_info['error_count'] + $result->error_count;
		$resinfo['errors'] = array_merge($resinfo['errors'],$result->errors);
		$resinfo['total'] = count($users);
		return $resinfo;
				
		
	}
	
	function addWebhook($field)
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
		
		$action=array("subscribe"=>true,"unsubscribe"=>true,"profile"=>true,"cleaned"=>true,"upemail"=>true,"campaign"=>true);
		$sources=array("user"=>true,"admin"=>true,"api"=>false);
		$url=str_replace("administrator/","",JURI::base()).'components/com_mue/helpers/mchook.php';
		if (!$res = $mc->addListWebhook($list->uf_default,$url,$actions,$sources)) {
			$this->setError($mc->error);
			return false;
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
		$list->list_webhooks = $mc->getListWebhooks($list->uf_default);
		
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
