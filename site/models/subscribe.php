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
	
}
