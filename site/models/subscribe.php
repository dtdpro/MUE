<?php
/**
 * @version		$Id: subscribe.php 2012-07-24 $
 * @package		MUE.Site
 * @subpackage	subscribe
 * @copyright	Copyright (C) 2013 DtD Productions.
 * @license		GNU General Public License version 2
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * MUE Subscribe Model
 *
 * @static
 * @package		MUE.Site
 * @subpackage	subscribe
 * @since		always
 */
class MUEModelSubscribe extends JModel
{
	
	var $codeError= "";
	
	function getPlanInfo($pid)
	{
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query  = 'SELECT c.*';
		$query .= 'FROM #__mue_subs as c ';
		$query .= 'WHERE c.published = 1 && c.access IN ('.implode(",",$user->getAuthorisedViewLevels()).') ';
		$query .= ' && c.sub_id = '.$pid;
		$db->setQuery( $query );
		return $db->loadObject();
	}
	
	function getPlans()
	{
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query  = 'SELECT c.*';
		$query .= 'FROM #__mue_subs as c ';
		$query .= 'WHERE c.published = 1 && c.access IN ('.implode(",",$user->getAuthorisedViewLevels()).') ';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function redeemCode($cinfo,$code) {
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$qc = 'SELECT * FROM #__ce_purchased_codes WHERE code_code = "'.$code.'" && code_course IN (0,'.$cinfo->course_id.')';
		$db->setQuery( $qc );
		$codeinfo = $db->loadObject();
		if (!$codeinfo) {
			$this->codeError = 'Code invalid';
			return false;
		}
		if ($codeinfo->code_limit == 0) { 
			$this->codeError = 'Code use limit met';
			return false;
		} else if ($codeinfo->code_limit != -1) { 
			$qu = 'UPDATE #__ce_purchased_codes SET code_limit=code_limit-1 WHERE code_code = "'.$code.'" && code_course IN (0,'.$cinfo->course_id.')';
			$db->setQuery($qu); $db->query();
		}
		$q = 'INSERT INTO #__ce_purchased (purchase_user,purchase_course,purchase_status,purchase_type,purchase_transid,purchase_ip) VALUES ('.$user->id.','.$cinfo->course_id.',"completed","redeem","'.$code.'","'.$_SERVER['REMOTE_ADDR'].'")';
		$db->setQuery($q); $db->query();
		return true;
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
		if ($cfg->mcrgroup) {
			include_once 'components/com_mue/lib/mailchimp.php';
			
			$mc = new MailChimp($cfg->mckey,$cfg->mclist);
			$mcdata=array();
			$mcdata['GROUPINGS']=array(array("name"=>$cfg->mcrgroup,"groups"=>$cfg->mcsubgroup));
			$mcd=print_r($mcdata,true);
			if ($mc->subStatus($user->email)) {
				$mcresult = $mc->updateUser($user->email,$mcdata,false,"html");
				if ($mcresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$cfg->mclist.' '.$mcd."\r\n"; }
				else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$cfg->mclist." Error: ".$mc->error."\r\n"; }
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
	
}
