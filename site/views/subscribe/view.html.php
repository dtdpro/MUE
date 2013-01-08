<?php
/**
 * @version		$Id: view.html.php 2013-01-01 $
 * @package		MUE.Site
 * @subpackage	subscribe
 * @copyright	Copyright (C) 2013 DtD Productions.
 * @license		GNU General Public License version 2
 */

jimport( 'joomla.application.component.view');

/**
 * MUE Subscribe View
 *
 * @static
 * @package		MUE.Site
 * @subpackage	subscribe
 * @since		always
 */
class MUEViewSubscribe extends JView
{
	var $pinfo = null;
	var $pid = null;
	var $plans = null;
	
	function display($tpl = null)
	{
		$layout = $this->getLayout();
		$app=Jfactory::getApplication();
		$model =& $this->getModel();
		$planid = JRequest::getVar( 'plan' );
		$user =& JFactory::getUser();
		$this->pinfo = $model->getPlanInfo($planid);

		
		switch ($layout) {
			case "ppsubpay":
				$this->ppSubmitPayment();
				break;
			case "ppconfirm":
				$this->ppConfirmPayment();
				break;
			case "ppverify":
				$this->ppVerifyPayment();
				break;
			case "ppcancel":
				$this->ppCancelPayment();
				break;
			case "redeem":
				$this->redeemCode();
				break;
			case 'default':
			default:
				$this->subPlans();
				break;
		}
		
		parent::display($tpl);

	}
	
	function subPlans() {
		$model =& $this->getModel();
		$this->plans=$model->getPlans();
	}
	
	function ppSubmitPayment() {	
		$user =& JFactory::getUser();	
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$sub = MUEHelper::getActiveSub();
		if ($sub) $end = $sub->usrsub_end;
		else $sub = false;
		if (!$user->id ) {
			//take user to fm if not logged in
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id);
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->submitPayment($this->pinfo,$end)) {
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id,$paypal->error,'error');
		}
	}
	
	function ppConfirmPayment() {	
		$user =& JFactory::getUser();	
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = JRequest::getVar( 'purchaseid' );
		$token = JRequest::getVar( 'token' );
		if (!$user->id ) {
			//take user to fm if not logged in
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id);
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->confirmPayment($this->pinfo,$this->usid,$token)) {
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id,$paypal->error,'error');
		}
	}
	
	function ppVerifyPayment() {	
		$user =& JFactory::getUser();	
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = JRequest::getVar( 'purchaseid' );
		if (!$user->id ) {
			//take user to fm if not logged in
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id);
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->verifyPayment($this->pinfo,$this->usid)) {
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id,$paypal->error,'error');
		} else {
			
			$app->redirect('index.php?option=com_mue&view=user&layout=profile','Thank you, your subscription has been completed.');
			
		}
	}
	
	function ppCancelPayment() {	
		$user =& JFactory::getUser();	
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = JRequest::getVar( 'purchaseid' );
		if (!$user->id ) {
			//take user to fm if not logged in
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id);
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		$paypal->cancelPayment($this->pinfo,$this->usid);
		$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id,'Canceled');
	}
	
	
	
	function redeemCode() {
		$user =& JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model =& $this->getModel();
		$code = JRequest::getVar( 'redeemcode' );
		if (!$user->id ) {
			//take user to fm if not logged in
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id);
		}
		if (!$model->redeemCode($this->pinfo,$code)) {
			$app->redirect('index.php?option=com_mue&view=subscribe&Itemid='.JRequest::getVar( 'Itemid' ).'&plan='.$this->pinfo->sub_id,$model->codeError,'error');
		} else {
				
			$app->redirect('index.php?option=com_mue&view=user&layout=profile','Thank you, your code has been accepted.');
				
		}
	}
}
?>
