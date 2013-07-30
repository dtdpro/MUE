<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

class MUEModelCMList extends JModelLegacy
{
	protected function populateState()
	{
		// Set the component (option) we are dealing with.
		$field = JRequest::getInt('field');
		$this->setState('cmlist.field', $field);
	}
	
	function getUFields() {
		$app = JFactory::getApplication('administrator');
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_ufields');
		$query->where('uf_type NOT IN ("mailchimp","captcha")');
		$query->where('uf_cms = 0');
		$query->where('published = 1');
		$query->order('ordering');
		$this->_db->setQuery($query);
		$data=$this->_db->loadObjectList();
		
		$fields->text = array();
		$fields->mso = array();
		$fields->msm = array();
		$fields->text[] = JHtml::_('select.option', "user_group","MUE User Group [user_group]");
		$fields->text[] = JHtml::_('select.option', "site_url","Site URL [site_url]");
		$fields->text[] = JHtml::_('select.option', "username","Username [username]");
		foreach ($data as $d) {
			$fields->text[] = JHtml::_('select.option', $d->uf_sname,$d->uf_name." [".$d->uf_sname."]");
			if ($d->uf_type == "multi" || $d->uf_type == "dropdown") $fields->mso[] = JHtml::_('select.option', $d->uf_sname,$d->uf_name." [".$d->uf_sname."]");
			if ($d->uf_type == "mlist" || $d->uf_type == "mcbox") $fields->msm[] = JHtml::_('select.option', $d->uf_sname,$d->uf_name." [".$d->uf_sname."]");
		}
		return $fields;
	}

	function syncField($field) {
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php');
		
		if (!$field) { $this->setError("Filed ID Not Provided"); return false; }
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->cmkey) return false;
		$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
		
		$list->list_info=$cm->getListDetails($list->uf_default);
		$list->list_stats=$cm->getListStats($list->uf_default);
		$totalmembers = $list->list_stats->TotalActiveSubscribers;
		
		$listmembers = array();
		$start=1;
		while (count($listmembers) < $totalmembers) {
			if (!$lm=$cm->getActiveSubscribers($list->uf_default,$start,500)) {
				$this->setError($cm->error);
				return false;
			}
			$listmembers = array_merge($lm->Results,$listmembers);
			$start = $start+1;
		} 
		
		$userids = array();
		foreach ($listmembers as $l) {
			$q=$db->getQuery(true);
			$q->select('id');
			$q->from('#__users');
			$q->where('email="'.$l->EmailAddress.'"');
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
		require_once(JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php');
		
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
		if (!$cfg->cmkey) return false;
		$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
		
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
		foreach ($list->params->cmfields as $mcv=>$mue) { $muef[]=$mue; }
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
		
		//Build CM Batch Data
		$cmbatch=array(); 
		$resinfo=array();
		$resinfo['errors']=array();
		foreach ($users as $u) {
			$userid=$u->id; 
			
			$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
			$cmdata = array('Name'=>$udata->fname[$userid].' '.$udata->lname[$userid], 'EmailAddress'=>$u->email);
			$customfields = array();
			if ($list->params->cmfields) {
				$othervars=$list->params->cmfields;
				foreach ($othervars as $cmf=>$mue) {
					$ufield = $udata->$mue;
					if ($list->params->cmfieldtypes->$cmf == "MultiSelectMany") {
						if (in_array($mue,$moptfs)) {
							foreach (explode(" ",$ufield[$userid]) as $mfo) {
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
						$newcmf=array();
						$newcmf['Key']=$cmf;
						if ($mue == 'username') { $newcmf['Key']=$cmf; $newcmf['Value'] = $u->username; }
						else if ($mue == 'user_group') { $newcmf['Key']=$cmf; $newcmf['Value'] = $u->ug_name; }
						else if ($mue == 'site_url') { $newcmf['Key']=$cmf; $newcmf['Value'] = $u->userg_siteurl; }
						else if ($mue && $udata->$mue) {
							if (in_array($mue,$optfs)) $newcmf['Value'] = $optionsdata[$ufield[$userid]];
							else if (in_array($mue,$moptfs)) {
								$newcmf['Value'] = "";
								foreach (explode(" ",$ufield[$userid]) as $mfo) {
									$newcmf['Value'] .= $optionsdata[$mfo]." ";
								}
							}
							else $newcmf['Value'] = $ufield[$userid];
						}
						if (!$mue || $newcmf['Value'] == "") $newcmf['Clear']='true';
						$customfields[]=$newcmf;
					}
				}
			}
			if ($list->params->msgroup->field && $cfg->subscribe) {
				$newcmf=array();
				$newcmf['Key']=$list->params->msgroup->field;
				if (!$u->substatus) { $newcmf['Value']=$list->params->msgroup->reg; }
				else { $newcmf['Value']=$list->params->msgroup->sub; }
				$customfields[]=$newcmf;
			}
			$cmdata['CustomFields']=$customfields;
			$cmbatch[] = $cmdata;
			if (count($cmbatch) == 500) {
				if (!$result = $cm->importSubscribers($list->uf_default,$cmbatch)) {$this->setError($cm->error); return false;}
				$resinfo['add_count'] = $res_info['add_count'] + $result->TotalNewSubscribers;
				$resinfo['update_count'] = $res_info['update_count'] + $result->TotalExistingSubscribers;
				$resinfo['errors'] = array_merge($resinfo['errors'],$result->FailureDetails);
				$mcbatch = array();
			}
		}
		
		if (!$result = $cm->importSubscribers($list->uf_default,$cmbatch)) {$this->setError($cm->error); return false;}
		$resinfo['add_count'] = $res_info['add_count'] + $result->TotalNewSubscribers;
		$resinfo['update_count'] = $res_info['update_count'] + $result->TotalExistingSubscribers;
		$resinfo['errors'] = array_merge($resinfo['errors'],$result->FailureDetails);
		$resinfo['total'] = count($users);
		return $resinfo;
				
		
	}
	
	function addWebhook($field)
	{
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php');
		
		// Initialise variables.
		$field = $this->getState('cmlist.field');
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->cmkey) return false;
		$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
		
		$url=str_replace("administrator/","",JURI::base()).'components/com_mue/helpers/cmhook.php';
		$webhook=array("Events"=>array("Subscribe","Deactivate","Update"),"Url"=>$url,"PayloadFormat"=>"json");
		if (!$res = $cm->addListWebhook($list->uf_default,$webhook)) {
			$this->setError($cm->error);
			return false;
		}	
		
		return true;
	}
	
	function getList()
	{
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php');
		
		// Initialise variables.
		$field = $this->getState('cmlist.field');
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->cmkey) return false;
		$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
		
		$list->list_info=$cm->getListDetails($list->uf_default);
		$list->list_stats=$cm->getListStats($list->uf_default);
		$list->list_fields = $cm->getListCustomFields($list->uf_default);
		$list->list_webhooks = $cm->getListWebhooks($list->uf_default);
		$list->list_msfields = array();
		
		$n=0;
		foreach ($list->list_fields as &$v) {
			$v->Key=str_replace(array("[","]"),"",$v->Key);
			if ($v->DataType == "MultiSelectOne" && $v->FieldName != "newsletterformat") {
				$list->list_msfields[$n] = $v;
				foreach ($v->FieldOptions as $o) {
					$list->list_msfields[$n]->options[] = JHtml::_('select.option', $o,$o);
				}
				$n++;
			}
		}
		
		if (property_exists($list, 'params'))
		{
			$registry = new JRegistry();
			$registry->loadString($list->params);
			$list->params = $registry->toObject();
		}
		
		if ($list->list_info->UnsubscribeSetting == "AllClientLists") JError::raiseNotice('allclientlists','List Unsubscribe set to Unsubscribe from All Lists');

		return $list;
	}

	public function save($data,$field)
	{
		require_once(JPATH_ROOT.'/components/com_mue/lib/campaignmonitor.php');
		
		$db = JFactory::getDBO();
		$cfg=MUEHelper::getConfig();
		if (!$cfg->cmkey) return false;
		$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$parameter = new JRegistry;
		$parameter->loadArray($data);
		$params = (string)$parameter;
		$pdata = $parameter->toObject();
		
		foreach ($pdata->cmfieldtypes as $cmf=>$cmft) {
			if (($cmft == "MultiSelectOne" || $cmft == "MultiSelectMany") && $cmft != $pdata->msgroup->field) {
				$mue = $pdata->cmfields->$cmf;
				
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
				
				if (!$cm->updateFieldOptions($list->uf_default,'['.$cmf.']',$opts)) {
					$this->setError('MC API Error: '.$cm->error);
					return false;
				}
			}
		}
		
		$query = $db->getQuery(true);
		$query->update('#__mue_ufields');
		$query->set('params = '.$db->quote($params));
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		if ($db->query()) { return true; }
		else { $this->setError($db->getError()); return false; }
	}
}
