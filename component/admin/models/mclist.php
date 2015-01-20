<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

class MUEModelMclist extends JModelLegacy
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
		$query->where('uf_type NOT IN ("brlist","cmlist","mailchimp","captcha")');
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

	function syncList($field) {
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/mailchimp.php');
		
		if (!$field) { $this->setError("Field ID Not Provided"); return false; }
		
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
		if (strstr($list->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$list->uf_default,2);	}
		else { $mc_key = $cfg->mckey; $mc_list = $list->uf_default; }
		$mc = new MailChimpHelper($mc_key);
		
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
		foreach ($list->params->mcvars as $mcv=>$mue) { if ($mue) { $muef[]=$mue; } }
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
		$query->select('ug.userg_update as lastUpdate,ug.userg_notes,ug.userg_siteurl,ug.userg_subsince,ug.userg_subexp,ug.userg_lastpaidvia');
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
				$query->where('s.usrsub_status IN ("completed","accepted")');
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
			$mcitem=array('email'=>array("email"=>$u->email));
			$mcdata = array('FNAME'=>$udata->fname[$userid], 'LNAME'=>$udata->lname[$userid]);
			if ($list->params->mcvars) {
				$othervars=$list->params->mcvars;
				foreach ($othervars as $mcv=>$mue) { 
					if ($mue) {
					$ufield = $udata->$mue;
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
			}
			if ($list->params->mcrgroup && $cfg->subscribe) {
				if (!$u->substatus) $mcdata[$list->params->mcrgroup]=$list->params->mcreggroup;
				else $mcdata[$list->params->mcrgroup]=$list->params->mcsubgroup;
				if ($list->params->mcsubsince) {
					if ($u->userg_subsince != "0000-00-00")	$mcdata[$list->params->mcsubsince] = $u->userg_subsince;
					else $mcdata[$list->params->mcsubsince] = "";
				}
				if ($list->params->mcsubexp) {
					if ($u->userg_subexp != '0000-00-00') $mcdata[$list->params->mcsubexp] = $u->userg_subexp;
					else $mcdata[$list->params->mcsubexp] = "";
				}
				if ($list->params->mcsubpaytype) $mcdata[$list->params->mcsubpaytype] = $u->userg_lastpaidvia;
			}
			if ($list->params->mcigroup) {
				$mcdata['groupings']=array(array("name"=>$list->params->mcigroup,"groups"=>$list->params->mcigroups));
			}
			$mcitem['merge_vars']=$mcdata;
			$mcitem['email_type']='html';
			$mcbatch[] = $mcitem;
			if (count($mcbatch) == 1000) {
				$result = $mc->listBatchSubscribe($mcbatch,$mc_list);
				$resinfo['add_count'] = $resinfo['add_count'] + $result->add_count;
				$resinfo['update_count'] = $resinfo['update_count'] + $result->update_count;
				$resinfo['error_count'] = $resinfo['error_count'] + $result->error_count;
				$resinfo['errors'] = array_merge($resinfo['errors'],$result->errors);
				$mcbatch = array();
			}
		}
		
		if (count($mcbatch)) {
			$result = $mc->listBatchSubscribe($mcbatch,$mc_list);
			$resinfo['add_count'] = $resinfo['add_count'] + $result->add_count;
			$resinfo['update_count'] = $resinfo['update_count'] + $result->update_count;
			$resinfo['error_count'] = $resinfo['error_count'] + $result->error_count;
			$resinfo['errors'] = array_merge($resinfo['errors'],$result->errors);
			$resinfo['total'] = count($users);
		}
		
		$emlsrm = array();
		foreach ($resinfo['errors'] as $e) {
			$emlsrm[] = $e['email']['email'];
		}
		if (count($emlsrm)) {
			$query = $db->getQuery(true);
			$query->select("id");
			$query->from('#__users');
			$query->where('email IN ("'.implode('","',$emlsrm).'")');
			$db->setQuery($query);
			$rmids = $db->loadColumn();
			
			$query = $db->getQuery(true);
			$query->update('#__mue_users');
			$query->set('usr_data = "0"');
			$query->where('usr_field = '.$field);
			$query->where('usr_user IN ('.implode(",",$rmids).')');
			$db->setQuery($query);
			$db->query();
		}		
		
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
		if (strstr($list->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$list->uf_default,2);	}
		else { $mc_key = $cfg->mckey; $mc_list = $list->uf_default; }
		$mc = new MailChimpHelper($mc_key);
		
		$action=array("subscribe"=>true,"unsubscribe"=>true,"profile"=>true,"cleaned"=>true,"upemail"=>true,"campaign"=>true);
		$sources=array("user"=>true,"admin"=>true,"api"=>false);
		$url=str_replace("administrator/","",JURI::base()).'components/com_mue/helpers/mchook.php';
		if (!$res = $mc->addListWebhook($mc_list,$url,$actions,$sources)) {
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
		if (strstr($list->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$list->uf_default,2);	}
		else { $mc_key = $cfg->mckey; $mc_list = $list->uf_default; }
		$mc = new MailChimpHelper($mc_key);
		$mclist=$mc->getLists($mc_list);
		
		$list->list_info=$mclist[0];
		if ($list->list_info['stats']['grouping_count'] > 0)	$list->list_igroups = $mc->getListInterestGroupings($mc_list);
		else $list->list_igroups = false;
		$list->list_mvars = $mc->getListMergeVars($mc_list);
		$list->list_tvars = array();
		$list->list_msvars = array();
		$list->list_datevars = array();
		$list->list_webhooks = $mc->getListWebhooks($mc_list);
		
		$n=0;
		foreach ($list->list_mvars as $v) {
			if ($v['field_type'] == 'dropdown' || $v['field_type'] == 'radio') {
				$list->list_msvars[$n] = (object)$v;
				foreach ($v['choices'] as $o) {
					$list->list_msvars[$n]->options[] = JHtml::_('select.option', $o,$o);
				}
			}
			if ($v['field_type'] == 'date') {
				$list->list_datevars[$n] = (object)$v;
			}
			if ($v['field_type'] == 'text') {
				$list->list_tvars[$n] = (object)$v;
			}
			$n++;
		}
		
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
		require_once(JPATH_ROOT.'/components/com_mue/lib/mailchimp.php');
		$cfg=MUEHelper::getConfig();
		if (!$cfg->mckey) return false;
		if (strstr($list->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$list->uf_default,2);	}
		else { $mc_key = $cfg->mckey; $mc_list = $list->uf_default; }
		$mc = new MailChimpHelper($mc_key);
		
		$parameter = new JRegistry;
		$parameter->loadArray($data);
		$params = (string)$parameter;
		$pdata = $parameter->toObject();
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		foreach ($pdata->mcfieldtypes as $mcf=>$mcft) {
			if (($mcft == "radio" || $mcft == "dropdown") && $mcft != $pdata->mcrgroup->field && $pdata->mcvars->$mcf) {
				$mue = $pdata->mcvars->$mcf;
		
				$query = $db->getQuery(true);
				$query->select("uf_id");
				$query->from('#__mue_ufields');
				$query->where('uf_sname = "'.$mue.'"');
				$db->setQuery($query);
				if (!$muefid = $db->loadResult()){
					$this->setError($db->getQuery());
					return false;
				}
		
				$q2 = $db->getQuery(true);
				$q2->select('opt_text');
				$q2->from('#__mue_ufields_opts');
				$q2->where('opt_field = '.$muefid);
				$db->setQuery($q2);
				if (!$opts = $db->loadColumn()) {
					$this->setError($db->getQuery());
					return false;
				}
				$data=array('choices'=>$opts);
				if (!$mc->updateMergeVar($mc_list,$mcf,$data)) {
					$this->setError('MC API Error: '.$mc->error);
					return false;
				}
			}
		}
		
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
