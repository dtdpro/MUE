<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

class MUEModelBrlist extends JModelLegacy
{
	protected function populateState()
	{
		// Set the component (option) we are dealing with.
		$field = JRequest::getInt('field');
		$this->setState('brlist.field', $field);
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
	/*
	function syncList($field) {
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
		if (!$cfg->brkey) { $this->setError("Bronto Mail not Configured"); return false; }
		
		$token = $cfg->brkey;
		$bronto = new Bronto_Api();
		$bronto->setToken($token);
		$bronto->login();
		
		//Fields for Merge Data
		$muef=array("fname","lname","email");
		foreach ($list->params->brvars as $brv=>$mue) { if ($mue) { $muef[]=$mue; } }
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
		$query->select('ug.userg_update as lastUpdate,ug.userg_notes,ug.userg_siteurl,ug.userg_subsince,ug.userg_subexp,ug.userg_lastpaidvia,ug.userg_subendplanname');
		$query->join('LEFT', '#__mue_usergroup AS ug ON u.id = ug.userg_user');
		$query->select('g.ug_name');
		$query->join('LEFT', '#__mue_ugroups AS g ON ug.userg_group = g.ug_id');
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
		
		//Build Bronto Contact Data
		$brbatch=array(); 
		$resinfo=array();
		$resinfo['errors']=array();
		foreach ($users as $u) {
			$userid=$u->id; 
			$britem=array('email'=>$u->email);
			$brfields = array();
			
			// Update fields
			if ($list->params->brvars) {
				$othervars=$list->params->brvars;
				foreach ($othervars as $brv=>$mue) {
					if ($mue) {
						$ufield = $udata->$mue;
						if ($mue == 'username') { $brfields[] = array("fieldId" => $brv, "content" => $u->username); }
						else if ($mue == 'user_group') { $brfields[] = array("fieldId" => $brv, "content" => $u->ug_name); }
						else if ($mue == 'site_url') { $brfields[] = array("fieldId" => $brv, "content" => $u->userg_siteurl); }
						else if ($brlist->params->brfieldtypes->$brv == "checkbox") {
							if ($ufield[$userid] == "1") $brfields[] = array("fieldId" => $brv, "content" => 'true');
							else $brfields[] = array("fieldId" => $brv, "content" => 'false');
						} else if (in_array($mue,$optfs)) {
							$brfields[] = array("fieldId" => $brv, "content" => $optionsdata[$ufield[$userid]]);
						}
						else if (in_array($mue,$moptfs)) {
							$mcdata[$mcv] = "";
							$fv = '';
							foreach (explode(" ",$ufield[$userid]) as $mfo) {
								$fv .= $optionsdata[$mfo]." ";
							}
							$brfields[] = array("fieldId" => $brv, "content" => $fv);
						}
						else {
							$brfields[] = array("fieldId" => $brv, "content" => $ufield[$userid]);
						}
					}
				}
			}

			// Sub info
			if ($list->params->brsubstatus && $cfg->subscribe) {
				// Set Member Status
				if ($u->substatus) $brfields[] = array("fieldId" => $list->params->brsubstatus, "content" => $list->params->brsubtextyes);
				else $brfields[] = array("fieldId" => $list->params->brsubstatus, "content" => $list->params->brsubtextno);

				// Set Member Since
				if ( $list->params->brsubsince && $u->userg_subsince != "0000-00-00") {
					$brfields[] = array("fieldId" => $list->params->brsubsince, "content" => $u->userg_subsince );
				}

				// Set Member Exp
				if ( $list->params->brsubexp  && $u->userg_subexp != '0000-00-00') {
					$brfields[] = array("fieldId" => $list->params->brsubexp, "content" => $u->userg_subexp );
				}

				// Set Active/End Member Plan
				if ( $list->params->brsubplan ) {
					if ( !$u->substatus ) {
						$brfields[] = array("fieldId" => $list->params->brsubplan, "content" => 'None');
					} else {
						$brfields[] = array("fieldId" => $list->params->brsubplan, "content" => $u->userg_subendplanname);
					}
				}
			}

			$britem['fields']=$brfields;
			$brbatch[] = $britem;
			if (count($brbatch) == 1000) {
				$contactObject = $bronto->getContactObject();
				$contacts = $contactObject->addOrUpdate($brbatch);
				$brbatch = array();
			}
		}
		
		if (count($brbatch)) {
			$contactObject = $bronto->getContactObject();
			$contacts = $contactObject->addOrUpdate($brbatch);
		}
		
		return true;
				
		
	}
	*/
	function getList()
	{
		// Initialise variables.
		$field = $this->getState('brlist.field');
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$list = $db->loadObject();
		
		$cfg=MUEHelper::getConfig();
		if (!$cfg->brkey) return false;

        $token = $cfg->brkey;
        $bronto = new Bronto_Api();
        $bronto->setToken($token);
        $bronto->login();

        $listObject = $bronto->getListObject();
        $brlist = $listObject->createRow();
        $brlist->id = $list->uf_default;
        $brlist->read();

        $fieldObject = $bronto->getFieldObject();
        $brfields=$fieldObject->readAll();

		
		$list->list_info=$brlist;
		$list->list_mvars = $fieldObject->readAll();
		$list->list_tvars = array();
		$list->list_msvars = array();
		$list->list_datevars = array();
        $list->list_cbvars = array();
		
		$n=0;
		foreach ($brfields->iterate() as $v) {
			if ($v->type == 'select' || $v->type == 'radio') {
				$list->list_msvars[$n] = (object)$v;
				$list->list_msvars[$n]->options=array();
				foreach ($v['options'] as $o) {
					$list->list_msvars[$n]->options[] = JHtml::_('select.option', $o->value,$o->label);
				}
			}
			if ($v->type == 'date') {
				$list->list_datevars[$n] = $v;
			}
            if ($v->type == 'checkbox') {
                $list->list_cbvars[$n] = $v;
            }
			if ($v->type == 'text' || $v->type == 'textarea') {
				$list->list_tvars[$n] = $v;
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
		if (!$cfg->brkey) return false;
        $token = $cfg->brkey;

		
		$parameter = new JRegistry;
		$parameter->loadArray($data);
		$params = (string)$parameter;
		$pdata = $parameter->toObject();
		
		/*
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
		*/

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
