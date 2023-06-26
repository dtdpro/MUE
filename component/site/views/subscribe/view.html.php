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
		$this->input = JFactory::getApplication()->input;
		$app=Jfactory::getApplication();
		$this->params	= $app->getParams('com_mue');
		$this->discountcode = $app->getUserState('com_mue.discountcode',"");
		$model = $this->getModel();
		$planid = $this->input->get( 'plan' );
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
		else $this->return = false;

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
		$this->print = $this->input->get('print',0);
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
		/*if ($sub->usrsub_coupon && $app->getUserState('com_mue.discountcode')) {
			$app->setUserState('com_mue.discountcode',"");
			$app->enqueueMessage('You may only use one active coupon at a time.');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}*/
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->submitPayment($this->pinfo,$end)) {
			$app->enqueueMessage($paypal->error,'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
	}
	
	function ppConfirmPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = $this->input->get( 'purchaseid' );
		$token = $this->input->get( 'token' );
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->confirmPayment($this->pinfo,$this->usid,$token)) {
			$app->enqueueMessage($paypal->error,'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
	}
	
	function ppVerifyPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model = $this->getModel();
		$this->usid = $this->input->get( 'purchaseid' );
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->verifyPayment($this->pinfo,$this->usid)) {
			$app->enqueueMessage($paypal->error,'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		} else {
			$model->sendSubedEmail($this->pinfo);
			$model->updateProfile();
			$app->enqueueMessage('Thank you, your subscription has been completed. Once you have received your PayPal receipt please log out and log back in to access all features of the site. ');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
			
		}
	}
	
	function ppCancelPayment() {	
		$user = JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$this->usid = $this->input->get( 'purchaseid' );
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		$paypal->cancelPayment($this->pinfo,$this->usid);
		$app->enqueueMessage('Canceled');
		$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
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
				$app->enqueueMessage("Free subscription not available",'error');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
			} else {
				$model->sendSubedEmail($this->pinfo);
				$model->updateProfile();
				$app->enqueueMessage('Thank you, your subscription has been activated.');
				$app->redirect(JRoute::_('index.php?option=com_mue&view=user&layout=profile'));
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
			$app->enqueueMessage("Could Not Pay by Check",'error');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		} else {
			$model->sendSubedEmail($this->pinfo);
			$model->updateProfile();
			$app->enqueueMessage('Thank you, Please see details below.');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id.'&layout=check'));
				
		}
	}

	function addCode() {
		$sub = MUEHelper::getActiveSub();
		$app=Jfactory::getApplication();
		/*if ($sub->usrsub_coupon) {
			$app->setUserState('com_mue.discountcode',"");
			$app->enqueueMessage('You may only use one active coupon at a time.');
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}*/
		$this->discountcode = $this->input->get( 'discountcode' );
		$app->setUserState('com_mue.discountcode',$this->discountcode);
		$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
	}
}
?>
