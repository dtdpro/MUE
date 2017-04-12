<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MUEModelSubscribe extends JModelLegacy
{
	
	var $codeError= "";
	
	function getPlanInfo($pid,$discountcode = "")
	{
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query  = 'SELECT c.*';
		$query .= 'FROM #__mue_subs as c ';
		$query .= 'WHERE c.published = 1 && c.access IN ('.implode(",",$user->getAuthorisedViewLevels()).') ';
		$query .= ' && c.sub_id = '.$pid;
		$db->setQuery( $query );
		$plan = $db->loadObject();
		if ($discountcode) {
			if ($codeinfo = $this->getDiscountCodeInfo($discountcode)) {
				if (in_array($plan->sub_id,explode(",",$codeinfo->cu_plans))) {
					if ($codeinfo->cu_type == "amount") {
						$plan->discounted = number_format(($plan->sub_cost - $codeinfo->cu_value),2);
					} else if ($codeinfo->cu_type == "percent") {
						$plan->discounted = number_format(($plan->sub_cost - ($plan->sub_cost*($codeinfo->cu_value/100))),2);
					}
				} else {
					$plan->discounted = -1;
				}
			}
		} else {
			$plan->discounted = -1;
		}
		$plan->discountcode = $discountcode;
		return $plan;
	}
	
	function getPlans($discountcode = "")
	{
		$hadTrial = MUEHelper::userHadTrial();
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_subs');
		$query->where('published = 1');
		$query->where('access IN ('.implode(",",$user->getAuthorisedViewLevels()).')');
		if ($hadTrial) $query->where('sub_type != "trial"');
		$query->order('ordering');
		$db->setQuery( $query );
		$plans = $db->loadObjectList();
		if ( $discountcode ) $codeinfo = $this->getDiscountCodeInfo( $discountcode );
		else $codeinfo = false;
		foreach ($plans as &$p) {
			if ( $discountcode ) {
				if ( $codeinfo ) {
					if ( in_array( $p->sub_id, explode( ",", $codeinfo->cu_plans ) ) ) {
						if ( $codeinfo->cu_type == "amount" ) {
							$p->discounted = number_format( ( $p->sub_cost - $codeinfo->cu_value ), 2);
						} else if ( $codeinfo->cu_type == "percent" ) {
							$p->discounted = number_format( ( $p->sub_cost - ( $p->sub_cost * ( $codeinfo->cu_value / 100 ) ) ), 2);
						}
					} else {
						$p->discounted = -1;
					}
				}
			} else {
				$p->discounted = -1;
			}
		}
		return $plans;
	}

	function getDiscountCodeInfo($discountcode) {
		$db =& JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_coupons');
		$query->where('published = 1');
		$query->where('cu_code = "'.$db->escape($discountcode).'"');
		$query->where('((cu_start <= NOW() && cu_end >= NOW()) || cu_start = "0000-00-00")');
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	function payByCheck($pinfo,$start) {
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$q = 'INSERT INTO #__mue_usersubs (usrsub_user,usrsub_sub,usrsub_type,usrsub_ip,usrsub_status,usrsub_start,usrsub_end) VALUES ('.$user->id.','.$pinfo->sub_id.',"check","'.$_SERVER['REMOTE_ADDR'].'","pending",';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .= ',DATE_ADD(';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .=',INTERVAL '.$pinfo->sub_length.' '.strtoupper($pinfo->sub_period).'))';
		$db->setQuery($q);
		if (!$db->query()) return false;
		return $db->insertid();
	}

	function freeOfCharge($pinfo,$start) {
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$q = 'INSERT INTO #__mue_usersubs (usrsub_user,usrsub_sub,usrsub_type,usrsub_ip,usrsub_status,usrsub_start,usrsub_end) VALUES ('.$user->id.','.$pinfo->sub_id.',"trial","'.$_SERVER['REMOTE_ADDR'].'","completed",';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .= ',DATE_ADD(';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .=',INTERVAL '.$pinfo->sub_length.' '.strtoupper($pinfo->sub_period).'))';
		$db->setQuery($q);
		if (!$db->query()) return false;
		$newid = $db->insertid();
		MUEHelper::getActiveSub();
		return $newid;
	}
	
	function sendSubedEmail($pinfo) {
		$config=MUEHelper::getConfig();
		$user =& JFactory::getUser();
		
		//Confirm Email
		$emailmsg = $config->subemail_content;
		$emailmsg = str_replace("{fullname}",$user->name,$emailmsg);
		$emailmsg = str_replace("{username}",$user->username,$emailmsg);
		$emailmsg = str_replace("{plancost}",$pinfo->sub_cost,$emailmsg);
		$emailmsg = str_replace("{plantitle}",$pinfo->sub_exttitle,$emailmsg);
		$mail = &JFactory::getMailer();
		$mail->IsHTML(true);
		$mail->addRecipient($user->email);
		$mail->setSender($config->subemail_email,$config->subemail_name);
		$mail->setSubject($config->subemail_subject);
		$mail->setBody( $emailmsg );
		$sent = $mail->Send();
	}
	
	function updateProfile() {
		$cfg=MUEHelper::getConfig();
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();
		$date = new JDate('now');
		$usernotes = "\r\n".$date->toSql(true)." User Subcription Added\r\n";
		$uginfo = $this->getUserGroupInfo($user->id);
		
		//MailChimp List Update
		foreach ($this->getMCFields() as $f) {
			if ($f->params->mcrgroup) {
				include_once 'components/com_mue/lib/mailchimp.php';

				if (strstr($f->uf_default,"_")){ list($mc_key, $mc_list) = explode("_",$f->uf_default,2);	}
				else { $mc_key = $cfg->mckey; $mc_list = $f->uf_default; }
				$mc = new MailChimpHelper($mc_key,$mc_list);
				$mcdata=array();
				$mcdata[$f->params->mcrgroup]=$f->params->mcsubgroup;
				$mcd=print_r($mcdata,true);
				if ($mc->subStatus($user->email)) {
					$mcresult = $mc->updateUser(array("email"=>$user->email),$mcdata,false,"html");
					if ($mcresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$f->uf_default.' '.$mcd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$f->uf_default." Error: ".$mc->error."\r\n"; }
				}
			}
		}
		
		//Campaign Monitor List Update
		foreach ($this->getCMFields() as $f) {
			if ($f->params->msgroup->field) {
				include_once 'components/com_mue/lib/campaignmonitor.php';
				
				$cm = new CampaignMonitor($cfg->cmkey,$cfg->cmclient);
				$cmdata=array();
				$cmdata = array('Name'=>$user->name, 'EmailAddress'=>$user->email, 'Resubscribe'=>'true');
				$customfields = array();
				$newcmf=array(); 
				$newcmf['Key']=$f->params->msgroup->field; 
				$newcmf['Value']=$f->params->msgroup->sub; 
				$customfields[]=$newcmf;
				$cmdata['CustomFields']=$customfields;
				
				$cmd=print_r($cmdata,true);
				if ($cm->getSubscriberDetails($f->uf_default,$user->email)) {
					$cmresult = $cm->updateSubscriber($f->uf_default,$user->email,$cmdata);
					if ($cmresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated on Campaign Monitor List #".$f->uf_default.' '.$cmd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on Campaign Monitor List #".$f->uf_default." Error: ".$cm->error.' '.$cmd."\r\n"; }
				}
			}
		}

		// Bronto List Update
		foreach ($this->getBrontoFields() as $f) {
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
				$contact->setField($f->params->brsubstatus,$f->params->brsubtextyes);

				// Set Member Since
				if ( $f->params->brsubsince ) {
					$contact->setField( $f->params->brsubsince, $uginfo->userg_subsince );
				}

				// Set Member Exp
				if ( $f->params->brsubexp ) {
					$contact->setField( $f->params->brsubexp, $uginfo->userg_subexp );
				}

				// Set Active/End Member Plan
				if ( $f->params->brsubplan ) {
					$contact->setField( $f->params->brsubplan, $uginfo->userg_subendplanname );
				}

				// Save
				$contact->save(true);
			}
		}


		//Update update date
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
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

	function getBrontoFields() {
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

	public function getUserGroupInfo($user) {
		$db		= $this->getDbo();
		$query = 'SELECT * FROM #__mue_usergroup as ug ';
		$query.= 'WHERE ug.userg_user="'.$user.'"';
		$db->setQuery($query);
		return $db->loadObject();
	}
	
}
