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
class MUEModelSubscribe extends JModelLegacy
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
		foreach ($this->getMCFields() as $f) {
			if ($f->params->mcrgroup) {
				include_once 'components/com_mue/lib/mailchimp.php';
				
				$mc = new MailChimp($cfg->mckey,$f->uf_default);
				$mcdata=array();
				$mcdata['GROUPINGS']=array(array("name"=>$f->params->mcrgroup,"groups"=>$f->params->mcsubgroup));
				$mcd=print_r($mcdata,true);
				if ($mc->subStatus($user->email)) {
					$mcresult = $mc->updateUser($user->email,$mcdata,false,"html");
					if ($mcresult) { $usernotes .= $date->toSql(true)." EMail Subscription Updated on MailChimp List #".$f->uf_default.' '.$mcd."\r\n"; }
					else { $usernotes .= $date->toSql(true)." Could not update EMail subscription on MailChimp List #".$f->uf_default." Error: ".$mc->error."\r\n"; }
				}
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
	
}