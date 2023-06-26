<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

use Joomla\CMS\Session\Session;

class MUEModelSubscribe extends JModelLegacy
{
	
	var $codeError= "";
	
	function getPlanInfo($pid,$discountcode = "")
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$query  = 'SELECT c.*';
		$query .= 'FROM #__mue_subs as c ';
		$query .= 'WHERE c.published = 1 && c.access IN ('.implode(",",$user->getAuthorisedViewLevels()).') ';
		$query .= ' && c.sub_id = '.$pid;
		$db->setQuery( $query );
		$plan = $db->loadObject();
		$plan->discounted = -1;
		if ($discountcode) {
			if ($codeinfo = $this->getDiscountCodeInfo($discountcode)) {
				if ($codeinfo->cu_singleuse && $this->checkCouponUseByUser($user,$discountcode)) {
					$plan->discounted = -1;
				} else if ($codeinfo->cu_limit > 0 && $this->couponUseLimitMet($codeinfo->cu_limit,$discountcode)) {
					$plan->discounted = -1;
				} else if (in_array($plan->sub_id,explode(",",$codeinfo->cu_plans))) {
					if ($codeinfo->cu_type == "amount") {
						$plan->discounted = number_format(($plan->sub_cost - $codeinfo->cu_value),2);
					} else if ($codeinfo->cu_type == "percent") {
						$plan->discounted = number_format(($plan->sub_cost - ($plan->sub_cost*($codeinfo->cu_value/100))),2);
					}
				} else {
					$plan->discounted = -1;
				}
			} else {
				$plan->discounted = -1;
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
		$app=Jfactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_subs');
		$query->where('published = 1');
		$query->where('access IN ('.implode(",",$user->getAuthorisedViewLevels()).')');
		if ($hadTrial) $query->where('sub_type != "trial"');
		$query->order('ordering');
		$db->setQuery( $query );
		$plans = $db->loadObjectList();
		if ( $discountcode ) {
			$codeinfo = $this->getDiscountCodeInfo( $discountcode );
		}
		else $codeinfo = false;
		$failreason = "";
		foreach ($plans as &$p) {
			$p->discounted = -1;
			if ( $discountcode ) {
				if ( $codeinfo ) {
					if ($codeinfo->cu_singleuse && $this->checkCouponUseByUser($user,$discountcode)) {
						$p->discounted = -1;
						$failreason="Coupon Code has already been used";
					} else if ($codeinfo->cu_limit > 0 && $this->couponUseLimitMet($codeinfo->cu_limit,$discountcode)) {
						$p->discounted = -1;
						$failreason="Coupon Code use limit has been met";
					} else if ( in_array( $p->sub_id, explode( ",", $codeinfo->cu_plans ) ) ) {
						if ( $codeinfo->cu_type == "amount" ) {
							$p->discounted = number_format( ( $p->sub_cost - $codeinfo->cu_value ), 2);
						} else if ( $codeinfo->cu_type == "percent" ) {
							$p->discounted = number_format( ( $p->sub_cost - ( $p->sub_cost * ( $codeinfo->cu_value / 100 ) ) ), 2);
						}
					} else {
						$p->discounted = -1;
					}
				} else {
					$p->discounted = -1;
					$failreason="Coupon Code invalid";
				}
			} else {
				$p->discounted = -1;
			}
		}

		$app->setUserState('com_mue.failreason',$failreason);
		return $plans;
	}

	function getDiscountCodeInfo($discountcode) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mue_coupons');
		$query->where('published = 1');
		$query->where('cu_code = "' . $db->escape($discountcode) . '"');
		$query->where('((cu_start <= NOW() && cu_end >= NOW()) || cu_start = "0000-00-00")');
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	function payByCheck($pinfo,$start) {
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$q = 'INSERT INTO #__mue_usersubs (usrsub_user,usrsub_sub,usrsub_type,usrsub_ip,usrsub_status,usrsub_start,usrsub_end) VALUES ('.$user->id.','.$pinfo->sub_id.',"check","'.$_SERVER['REMOTE_ADDR'].'","pending",';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .= ',DATE_ADD(';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .=',INTERVAL '.$pinfo->sub_length.' '.strtoupper($pinfo->sub_period).'))';
		$db->setQuery($q);
		if (!$db->execute()) return false;
		return $db->insertid();
	}

	function freeOfCharge($pinfo,$start) {
		$cfg=MUEHelper::getConfig();
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		if ($pinfo->discounted > -1) {
			$q = 'INSERT INTO #__mue_usersubs (usrsub_user,usrsub_sub,usrsub_type,usrsub_ip,usrsub_status,usrsub_coupon,usrsub_start,usrsub_end) VALUES (' . $user->id . ',' . $pinfo->sub_id . ',"redeem","' . $_SERVER['REMOTE_ADDR'] . '","completed","'.$pinfo->discountcode.'",';
		} else {
			$q = 'INSERT INTO #__mue_usersubs (usrsub_user,usrsub_sub,usrsub_type,usrsub_ip,usrsub_status,usrsub_start,usrsub_end) VALUES (' . $user->id . ',' . $pinfo->sub_id . ',"trial","' . $_SERVER['REMOTE_ADDR'] . '","completed",';
		}
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .= ',DATE_ADD(';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .=',INTERVAL '.$pinfo->sub_length.' '.strtoupper($pinfo->sub_period).'))';
		$db->setQuery($q);
		if (!$db->execute()) return false;
		$newid = $db->insertid();
		MUEHelper::getActiveSub();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('user_id'));
		$query->from($db->quoteName('#__user_usergroup_map'));
		$query->where($db->quoteName('group_id') . ' = ' . (int) $cfg->subgroup);
		$query->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
		$db->setQuery($query);
		$hasgroup = $db->loadResult();
		if (!$hasgroup) {
			$query->clear();
			$query->insert($db->quoteName('#__user_usergroup_map'));
			$query->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')));
			$query->values((int) $user->id . ',' . $cfg->subgroup);
			$db->setQuery($query);
			$db->execute();
		}
		return $newid;
	}
	
	function sendSubedEmail($pinfo) {
		$config=MUEHelper::getConfig();
		$user = JFactory::getUser();
		
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
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$date = new JDate('now');
		$usernotes = "\r\n".$date->toSql(true)." User Subscription Added\r\n";
		$uginfo = $this->getUserGroupInfo($user->id);
		
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

		// Active Campaign Integration
		$acfields = $this->getACFields();
		if (count($acfields) > 0) {
			// Load up AC connector
			require_once( JPATH_ROOT . '/components/com_mue/lib/activecampaign.php' );
			$acClient = new ActiveCampaign( $cfg->ackey, $cfg->acurl );

			//get first list
			$aclistFirst = $acfields[0];

			// get contact
			$contact = $acClient->getContact($user->email);

			// update group fields
			if ($contact) {
				// set field data
				$fieldData = [];

				// Set Subscription Status
				if ($aclistFirst->params->acsubstatus) {
					$fieldVal = '';
					$fieldVal=$aclistFirst->params->acsubtextyes;
					$fieldDataEntry = [];
					$fieldDataEntry['field'] = $aclistFirst->params->acsubstatus;
					$fieldDataEntry['value'] = $fieldVal;
					$fieldData[] = $fieldDataEntry;
				}

				// Set Member Since
				if ( $aclistFirst->params->acsubsince && $uginfo->userg_subsince != "0000-00-00") {
					$fieldDataEntry = [];
					$fieldDataEntry['field'] = $aclistFirst->params->acsubsince;
					$fieldDataEntry['value'] = $uginfo->userg_subsince;
					$fieldData[] = $fieldDataEntry;
				}

				// Set Member Exp
				if ( $aclistFirst->params->acsubexp  && $uginfo->userg_subexp != '0000-00-00') {
					$fieldDataEntry = [];
					$fieldDataEntry['field'] = $aclistFirst->params->acsubexp;
					$fieldDataEntry['value'] = $uginfo->userg_subexp;
					$fieldData[] = $fieldDataEntry;
				}

				// Set Active/End Member Plan
				if ( $aclistFirst->params->acsubplan ) {
					$fieldDataEntry = [];
					$fieldDataEntry['field'] = $aclistFirst->params->acsubplan;
					$fieldDataEntry['value'] = $uginfo->userg_subendplanname;
					$fieldData[] = $fieldDataEntry;
				}

				// update ac data
				if (count($fieldData)) {
					$acClient->updateContactFields($contact['id'],$fieldData);
				}

				$usernotes .= $date->toSql(true)." Contact Data Updated on Active Campaign\r\n";
			}
		}

		//Update update date
		$qud = 'UPDATE #__mue_usergroup SET userg_update = "'.$date->toSql(true).'", userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$user->id;
		$db->setQuery($qud);
		if (!$db->execute()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
	}
	
	function getMCFields() {
		$db = JFactory::getDBO();
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
		$db = JFactory::getDBO();
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

	function getACFields() {
		$db = JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_ufields as f ';
		$qd.= ' WHERE f.published = 1 ';
		$qd .= ' && f.uf_type = "aclist"';
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

	public function checkCouponUseByUser($user,$code) {
		$db		= $this->getDbo();
		$query = 'SELECT * FROM #__mue_usersubs ';
		$query.= 'WHERE usrsub_user="'.$user->id.'"';
		$query .= ' && usrsub_coupon = "'.$db->escape($code).'"';
		$db->setQuery($query);
		$uses = $db->loadObject();
		return count($uses);

	}

	public function couponUseLimitMet($limit,$code) {
		$db		= $this->getDbo();
		$query = 'SELECT count(*) FROM #__mue_usersubs ';
		$query.= 'WHERE usrsub_coupon = "'.$db->escape($code).'"';
		$db->setQuery($query);
		$uses = $db->loadColumn()[0];
		if ($uses < $limit) return false;
		else return true;

	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}
}
