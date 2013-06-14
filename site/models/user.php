<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 *
 */
class MUEModelUser extends JModel
{
	function getGroups() {
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$aid = $user->getAuthorisedViewLevels();
		$qd = 'SELECT ug.* FROM #__mue_ugroups as ug WHERE ug.access IN ('.implode(",",$aid).')';
		$qd.= ' ORDER BY ug.ordering';
		$db->setQuery( $qd );
		$ugroups = $db->loadObjectList();
		return $ugroups;
	}
	
	function saveGroup($groupid) {
		$db =& JFactory::getDBO();
		$user=JFactory::getUser();
		$date = new JDate('now');
		$usernotes = "User Group Changed\r\n";
		$qud = 'UPDATE #__mue_usergroup SET userg_group = '.$groupid.', userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->getEscaped($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}
	
	function getUserCERecords() {
		$db =& JFactory::getDBO();
		$user=JFactory::getUser();
		
		$qci=$db->getQuery(true);
		$qci->select('*');
		$qci->from('#__mcme_sessions as s');
		$qci->join('RIGHT','#__mcme_courses AS c ON s.sess_course = c.course_id');
		$qci->join('LEFT','#__mcme_certsissued AS ci ON ci.ci_sess = s.sess_id');
		$qci->where('s.sess_user = '.$user->id);
		$qci->where('c.published >= 1');
		$qci->order('s.sess_start DESC');
		$db->setQuery($qci);
		return $db->loadObjectList();
	}
	
	function getUserFields($group,$showhidden=false,$all=false,$changable=false) {
		$db =& JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_uguf as g';
		$qd.= ' RIGHT JOIN #__mue_ufields as f ON g.uguf_field = f.uf_id';
		$qd.= ' WHERE f.published = 1 && g.uguf_group='.$group;
		if (!$showhidden) $qd.=" && f.uf_hidden = 0";
		if ($changable) $qd.=" && f.uf_change = 1";
		$qd .= ' && f.uf_type != "captcha"';
		$qd.= ' ORDER BY f.ordering';
		$db->setQuery( $qd ); 
		$ufields = $db->loadObjectList();
		if ($all) {
			foreach ($ufields as &$f) {
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
		return $ufields;
	}
	
	public function save()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Initialise variables;
		$user=JFactory::getUser();
		$data		= JRequest::getVar('jform', array(), 'post', 'array'); 
		$dispatcher = JDispatcher::getInstance();
		$isNew = true;
		$db		= $this->getDbo();
		$ugroup = $data['userGroupID'];
		$ginfo=MUEHelper::getGroupInfo($ugroup);
		$date = new JDate('now');
		$usernotes = $date->toSql(true)." User Profile Updated"."\r\n";
		$cfg = MUEHelper::getConfig();
		$substatus=MUEHelper::getActiveSub();
		
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$fids = array();
			$optfs = array();
			$moptfs = array();
			$mclists = array();
			$flist = $this->getUserFields($ugroup,false,false,false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'mailchimp') {
					$mclists[]=$d;
				} else if ($d->uf_type=='birthday') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.$fday;
				} else if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
				} else if ($d->uf_type=='cbox') { 
					$item->$fieldname = ($data[$fieldname]=='on') ? "1" : "0";
				} else $item->$fieldname = $data[$fieldname];
				if ($d->uf_type=="multi" || $d->uf_type=="dropdown") $optfs[]=$d->uf_sname;
				if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") $moptfs[]=$d->uf_sname;
				$fids[]=$d->uf_id;
			}
			$item->site_url = JURI::base();
			$item->user_group = $ginfo->ug_name;
			
			$odsql = "SELECT * FROM #__mue_ufields_opts";
			$db->setQuery($odsql);
			$optionsdata = array();
			$optres = $db->loadObjectList();
			foreach ($optres as $o) {
				$optionsdata[$o->opt_id]=$o->opt_text;
			}
			
			foreach ($mclists as $mclist) {
				include_once 'components/com_mue/lib/mailchimp.php';
				if ($data[$mclist->uf_sname]) {
					$mcf=$mclist->uf_sname;
					$mc = new MailChimp($cfg->mckey,$mclist->uf_default);
					$mcdata = array('FNAME'=>$item->fname, 'LNAME'=>$item->lname, 'OPTIN_IP'=>$_SERVER['REMOTE_ADDR'], 'OPTIN_TIME'=>$date->toSql(true));
					if ($cfg->mcvars) {
						$othervars=explode(",",$cfg->mcvars);
						foreach ($othervars as $ov) {
							list($mue, $mcv) = explode(":",$ov,2);
							if (in_array($mue,$optfs)) $mcdata[$mcv] = $optionsdata[$item->$mue];
							else if (in_array($mue,$moptfs)) {
								$mcdata[$mcv] = "";
								foreach (explode(" ",$item->$mue) as $mfo) {
									$mcdata[$mcv] .= $optionsdata[$mfo]." ";
								}
							}
							else $mcdata[$mcv] = $item->$mue;
						} 
					}
					if ($cfg->mcrgroup) {
						if (!$substatus) $mcdata['GROUPINGS']=array(array("name"=>$cfg->mcrgroup,"groups"=>$cfg->mcreggroup));
						else $mcdata['GROUPINGS']=array(array("name"=>$cfg->mcrgroup,"groups"=>$cfg->mcsubgroup));
					}
					$mcd=print_r($mcdata,true);
					if ($mc->subStatus($item->email)) {
						$mcresult = $mc->updateUser($item->email,$mcdata,false,"html");
						if ($mcresult) { $item->$mcf=1; $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$mclist->uf_default.' '.$mcd."\r\n"; }
						else { $item->$mcf=1; $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
					}
					else {
						$mcresult = $mc->subscribeUser($item->email,$mcdata,false,"html");
						if ($mcresult) { $item->$mcf=1; $usernotes .= $date->toSql(true)." EMail Subscribed to MailChimp List #".$mclist->uf_default.' '.$mcd."\r\n"; }
						else { $item->$mcf=0; $usernotes .= $date->toSql(true)." Could not subscribe EMail to MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
					}
					
				} else {
					$mcf=$mclist->uf_sname;
					$mc = new MailChimp($cfg->mckey,$mclist->uf_default);
					$mcresult = $mc->unsubscribeUser($item->email);
					if ($mcresult) { $item->$mcf=0; $usernotes .= $date->toSql(true)." EMail Unsubscribed from MailChimp List #".$mclist->uf_default."\r\n"; }
					else { $item->$mcf=0; $usernotes .= $date->toSql(true)." Could not unsubscribe EMail from MailChimp List #".$mclist->uf_default." Error: ".$mc->error."\r\n"; }
				}
			}
			
			//User Directory
			$udf=$cfg->on_userdir_field;
			if ($cfg->userdir && $item->$udf) {
				$dudsql = "DELETE FROM #__mue_userdir WHERE ud_user = ".$user->id;
				$db->setQuery($dudsql);
				$db->query();
				$af=explode(",",$cfg->userdir_mapfields);
				$uf=explode(",",$cfg->userdir_userinfo);
				$sf=explode(",",$cfg->userdir_searchinfo);
				$address="";
				$dbuserinfo="";
				$dbsearchinfo="";
				foreach ($af as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$address .= $optionsdata[$item->$f]." ";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$address .= $optionsdata[$mfo]." ";
							}
						} else {
							$address .= $item->$f." ";
						}
					}
				}
				foreach ($uf as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$dbuserinfo .= $optionsdata[$item->$f]."<br />";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$dbuserinfo .= $optionsdata[$mfo]."<br />";
							}
						} else {
							$dbuserinfo .= $item->$f."<br />";
						}
					}
				}
				foreach ($sf as $f) {
					if ($item->$f) {
						if (in_array($f,$optfs)) {
							$dbsearchinfo .= $optionsdata[$item->$f]." ";
						} else if (in_array($f,$moptfs)) {
							foreach (explode(" ",$item->$f) as $mfo) {
								$dbsearchinfo .= $optionsdata[$mfo]." ";
							}
						} else {
							$dbsearchinfo .= $item->$f." ";
						}
					}
				}
				$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=".urlencode($address);
				$gdata_json = $this->curl_file_get_contents($url);
				$gdata = json_decode($gdata_json);
				if ($gdata->status == 'OK') {
					$udsql = 'INSERT INTO #__mue_userdir (ud_user,ud_lat,ud_lon,ud_userinfo,ud_searchinfo) VALUES  ("'.$user->id.'","'.$gdata->results[0]->geometry->location->lat.'","'.$gdata->results[0]->geometry->location->lng.'","'.$dbuserinfo.'","'.$dbsearchinfo.'")';
					$db->setQuery($udsql);
					$db->query(); 
					$usernotes .= $date->toSql(true)." Added to/Updated in User Directory\r\n";
				} else {
					$item->$udf = 0;
					$usernotes .= $date->toSql(true)." Could not add to/update User Directory\r\n";
				}
			} else if ($cfg->userdir && !$item->$udf) {
				$dudsql = "DELETE FROM #__mue_userdir WHERE ud_user = ".$user->id;
				$db->setQuery($dudsql);
				$db->query();
				$usernotes .= $date->toSql(true)." Removed from User Directory\r\n";
			}
				
				
			
			//Update Joomla User Info
			$udata['name']=$item->fname." ".$item->lname;
			$udata['password']=$item->password;
			$udata['password2']=$item->cpassword;
			$udata['block']=$user->block;
			if (!$user->bind($udata)) {
				$this->setError($user->getError());
				return false;
			};
			if (!$user->save(true)) {
				$this->setError($user->getError());
				return false;
			}
			
			//remove joomla user info from item
			unset($item->password);
			unset($item->cpassword);
			
			
			//Save ContinuEd Userinfo
			$query	= $db->getQuery(true);
			$query->delete();
			$query->from('#__mue_users');
			$query->where('usr_user = '.$user->id);
			$query->where('usr_field IN ('.implode(",",$fids).')');
			$db->setQuery((string)$query);
			$db->query();
			
			$flist = $this->getUserFields($ugroup,false,false,true);
			foreach ($flist as $fl) {
				$fieldname = $fl->uf_sname;
				if (!$fl->uf_cms) {

					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$db->getEscaped($item->$fieldname).'")';
					$db->setQuery($qf);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
			
			//Update update date
			$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->getEscaped($usernotes).'") WHERE userg_user = '.$user->id;
			$db->setQuery($qud);
			if (!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
		
		return true;
	}

	private function curl_file_get_contents($URL){
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);
	
		if ($contents) return $contents;
		else return FALSE;
	}

	

}
