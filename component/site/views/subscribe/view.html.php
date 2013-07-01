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
	
	function display($tpl = null)
	{
		$layout = $this->getLayout();
		$app=Jfactory::getApplication();
		$model =& $this->getModel();
		$planid = JRequest::getVar( 'plan' );
		$user =& JFactory::getUser();
		if ($planid) $this->pinfo = $model->getPlanInfo($planid);

		
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
			case "check":
				$this->checkInfo();
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
	
	function cartForm() {

	}
	
	function checkInfo() {
		$this->print = JRequest::getVar('print',0);
	}
	
	function ppSubmitPayment() {	
		$user =& JFactory::getUser();	
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$sub = MUEHelper::getActiveSub();
		if ($sub) $end = $sub->usrsub_end;
		else $sub = false;
		if (!$user->id ) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id));
		}
		include_once 'components/com_mue/helpers/paypal.php';
		$paypal = new PayPalAPI($muecfg->paypal_mode,$muecfg->paypal_username,$muecfg->paypal_password,$muecfg->paypal_signature);
		if (!$paypal->submitPayment($this->pinfo,$end)) {
			$app->redirect(JRoute::_('index.php?option=com_mue&view=subscribe&plan='.$this->pinfo->sub_id),$paypal->error,'error');
		}
	}
	
	function ppConfirmPayment() {	
		$user =& JFactory::getUser();	
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
		$user =& JFactory::getUser();	
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model =& $this->getModel();
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
		$user =& JFactory::getUser();	
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
	
	
	
	function payByCheck() {
		$user =& JFactory::getUser();
		$app=Jfactory::getApplication();
		$muecfg = MUEHelper::getConfig();
		$model =& $this->getModel();
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
}
?>
