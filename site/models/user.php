<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 *
 */
class MUEModelUser extends JModel
{
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
		$data		= JRequest::getVar('jform', array(), 'post', 'array'); 
		$dispatcher = JDispatcher::getInstance();
		$isNew = true;
		$db		= $this->getDbo();
		$ugroup = $data['userGroupID'];
		$date = new JDate('now');
		$usernotes = $date->toSql(true)." Updated"."\r\n";
		$cfg = MUEHelper::getConfig();
		
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		
		// Allow an exception to be thrown.
		try
		{
			//setup item and bind data
			$fids = array();
			$flist = $this->getUserFields($ugroup,false,false,false);
			foreach ($flist as $d) {
				$fieldname = $d->uf_sname;
				if ($d->uf_type == 'mailchimp') {
					$mclist=$fieldname;
				} else if ($d->uf_type=='birthday') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.$fday;
				} else if ($d->uf_type=="mcbox" || $d->uf_type=="mlist") {
					$item->$fieldname = implode(" ",$item->$fieldname);
				} else if ($fl->uf_type=='cbox') { 
					if ($item->$fieldname=='on') $item->$fieldname = 1;
					else $item->$fieldname = 0;
				} else $item->$fieldname = $data[$fieldname];
				$fids[]=$d->uf_id;
			}
			$item->site_url = JURI::base();
			
			if ($mclist) {
				include_once 'components/com_mue/lib/mailchimp.php';
				if ($data[$mclist]) {
					$mc = new MailChimp($cfg->mckey,$cfg->mclist);
					$mcdata = array('FNAME'=>$item->fname, 'LNAME'=>$item->lname, 'OPTIN_IP'=>$_SERVER['REMOTE_ADDR'], 'OPTIN_TIME'=>$date->toSql(true));
					$othervars=explode(",",$cfg->mcvars);
					foreach ($othervars as $ov) {
						list($mue, $mcv) = explode(":",$ov,2);
						$mcdata[$mcv] = $item->$mue;
					} $mcd=print_r($mcdata,true);
					$mcresult = $mc->subscribeUser($item->email,$mcdata,false,"html");
					if ($mcresult) { $item->$mclist; $usernotes .= $date->toSql(true)." Subscribed to MailChimp List #".$cfg->mclist.' '.$mcd."\r\n"; }
					else { $item->$mclist; $usernotes .= $date->toSql(true)." Could not subscribe to MailChimp List #".$cfg->mclist." Error: ".$mc->error."\r\n"; }
				} else {
					$mc = new MailChimp($cfg->mckey,$cfg->mclist);
					$mcresult = $mc->unsubscribeUser($item->email);
					if ($mcresult) { $item->$mclist; $usernotes .= $date->toSql(true)." Unsubscribed from MailChimp List #".$cfg->mclist."\r\n"; }
					else { $item->$mclist; $usernotes .= $date->toSql(true)." Could not unsubscribe from MailChimp List #".$cfg->mclist." Error: ".$mc->error."\r\n"; }
				}
			}
			
			//Update Joomla User Info
			$user=JFactory::getUser();
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

					$qf = 'INSERT INTO #__mue_users (usr_user,usr_field,usr_data) VALUES ("'.$user->id.'","'.$fl->uf_id.'","'.$item->$fieldname.'")';
					$db->setQuery($qf);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
			
			//Update update date
			$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$usernotes.'") WHERE userg_user = '.$user->id;
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

	

}
