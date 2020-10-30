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
class MUEViewSubscribe extends JViewLegacy
{
	var $pinfo = null;
	var $pid = null;
	var $plans = null;
	var $params;
	var $code = null;
	var $discountcode = "";
	
	function display($tpl = null)
	{
		$layout = $this->getLayout();
		$app=Jfactory::getApplication();
		$this->params	= $app->getParams('com_mue');
		$this->discountcode = $app->getUserState('com_mue.discountcode',"");
		$model = $this->getModel();
		$planid = JRequest::getVar( 'plan' );
		$user = JFactory::getUser();
		$hadTrial = MUEHelper::userHadTrial();
		$this->subCount = count(MUEHelper::getUserSubs());
		if ($planid) {
			$this->pinfo = $model->getPlanInfo($planid,$this->discountcode);
			if ($hadTrial && $this->pinfo->sub_type == "trial") {
				$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe'));
			}
		}

		if ($app->getUserState('mue.userreg.return')) $this->return = $app->getUserState('mue.userreg.return');
		
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
			case "cartops":
				$this->cartForm();
				break;
			case "paybycheck":
				$this->payByCheck();
				break;
			case "freeofcharge":
				$this->freeOfCharge();
				break;
			case "check":
				$this->checkInfo();
				break;
			case "addcode":
				$this->addCode();
				break;
			case 'default':
			default:
				$this->subPlans();
				break;
		}
		
		parent::display($tpl);

	}
	
	function subPlans() {
		$model = $this->getModel();
		$app=Jfactory::getApplication();
		$this->plans=$model->getPlans($this->discountcode);
		if ($failreason = $app->getUserState('com_mue.failreason',"")) {
			$app->setUserState('com_mue.failreason',"");
			$app->enqueueMessage($failreason,'error');
		}
	}
	
	function cartForm() {
	}
	
	function checkInfo() {
		$this->print = JRequest::getVar('print',0);
	}
	
	function ppSubmitPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$sub = MUEHelper::getActiveSub();
		if ($sub) $end = $sub->usrsub_end;
		else $sub = false;
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		if ($sub->usrsub_coupon) {
			$app->setUserState('com_mue.discountcode',"");
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),'You may only use one active coupon at a time.');
		}
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->submitPayment($this->pinfo,$end)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),$paypal->error,'error');
		}
	}
	
	function ppConfirmPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = JRequest::getVar( 'purchaseid' );
		$token = JRequest::getVar( 'token' );
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->confirmPayment($this->pinfo,$this->usid,$token)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),$paypal->error,'error');
		}
	}
	
	function ppVerifyPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model = $this->getModel();
		$this->usid = JRequest::getVar( 'purchaseid' );
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->verifyPayment($this->pinfo,$this->usid)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),$paypal->error,'error');
		} else {
			$model->sendSubedEmail($this->pinfo);
			$model->updateProfile();
			$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),'Thank you, your subscription has been completed. Once you have received your PayPal receipt please log out and log back in to access all features of the site. ');
			
		}
	}
	
	function ppCancelPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = JRequest::getVar( 'purchaseid' );
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		$paypal->cancelPayment($this->pinfo,$this->usid);
		$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),'Canceled');
	}

	function freeOfCharge() {
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model = $this->getModel();
		$sub = MUEHelper::getActiveSub();
		if ($sub) $end = $sub->usrsub_end;
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		if ($this->pinfo->sub_cost == 0 || $this->pinfo->discounted == 0) {
			if (!$subid = $model->freeOfCharge($this->pinfo,$end)) {
				$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),"Free subscription not available",'error');
			} else {
				$model->sendSubedEmail($this->pinfo);
				$model->updateProfile();
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'),'Thank you, your subscription has been activated.');
			}
		} else {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
	}
	
	function payByCheck() {
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model = $this->getModel();
		$sub = MUEHelper::getActiveSub();
		if ($sub) $end = $sub->usrsub_end;
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		if (!$subid = $model->payByCheck($this->pinfo,$end)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),"Could Not Pay by Check",'error');
		} else {
			$model->sendSubedEmail($this->pinfo);
			$model->updateProfile();
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id.'&layout=check'),'Thank you, Please see details below.');
				
		}
	}

	function addCode() {
		$sub = MUEHelper::getActiveSub();
		$app=Jfactory::getApplication();
		if ($sub->usrsub_coupon) {
			$app->setUserState('com_mue.discountcode',"");
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),'You may only use one active coupon at a time.');
		}
		$this->discountcode = JRequest::getVar( 'discountcode' );
		$app->setUserState('com_mue.discountcode',$this->discountcode);
		$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
	}
}
?>
