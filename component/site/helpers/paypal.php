<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session;

class PayPalAPI {

	var $API_USERNAME = "";
	var $API_PASSWORD = "";
	var $API_SIGNATURE = "";
	var $API_ENDPOINT = "";
	var $SUBJECT = '';
	var $PAYPAL_URL = "";
	var $PPVERSION = '94.0';
	var $ACK_SUCCESS = 'SUCCESS';
	var $ACK_SUCCESS_WITH_WARNING = 'SUCCESSWITHWARNING';
	var $AUTH_TOKEN = '';
	var $AUTH_SIGNATURE = '';
	var $AUTH_TIMESTAMP = '';
	var $AUTH_MODE = '';
	var $IPN_URL = '';
	var $nvp_header = '';
	var $error="";
	
	function __construct($mode,$username,$pass,$sig) {
		$this->API_USERNAME = $username;
		$this->API_PASSWORD = $pass;
		$this->API_SIGNATURE = $sig;
		if ($mode == "sandbox") {
			$this->API_ENDPOINT = "https://api-3t.sandbox.paypal.com/nvp";
			$this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=";
			$this->IPN_URL = "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$this->API_ENDPOINT = "https://api-3t.paypal.com/nvp";
			$this->PAYPAL_URL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=";
			$this->IPN_URL = "https://ipnpb.paypal.com/cgi-bin/webscr";
		}
	}
	
	function cancelPayment($pinfo,$usid) {
		$db  = JFactory::getDBO();
		$q2='UPDATE #__mue_usersubs SET usrsub_status="canceled" WHERE usrsub_id = "'.$usid.'"';
		$db->setQuery($q2);
		$db->execute();
	}
	
	function cancelSub($sub) {
		$db  = JFactory::getDBO();
		$q2='SELECT * FROM #__mue_usersubs WHERE usrsub_id = "'.$sub.'"';
		$db->setQuery($q2);
		$db->execute();
		$sinfo = $db->loadObject();
		$nvpstr='&ACTION=Cancel'.'&PROFILEID='.$sinfo->usrsub_rpprofile ;
		$resArray=$this->hash_call("ManageRecurringPaymentsProfileStatus",$nvpstr); 
		$ack = strtoupper($resArray["ACK"]);

		if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING'){
			$this->error="An Error has occurred";
			return false;
		} else {
			return true;
		}
	}
	
	function verifyPayment($pinfo,$usid) {
		$input = JFactory::getApplication()->input;
		$session=JFactory::getSession();
		$db  = JFactory::getDBO();
		$user  = JFactory::getUser();
		$app=Jfactory::getApplication();
		$iid = $input->get('Itemid');
		$config = MUEHelper::getConfig();
		
		$qsubid = 'SELECT * FROM #__mue_usersubs WHERE usrsub_id = "'.$usid.'"';
		$db->setQuery($qsubid); $sinfo = $db->loadObject(); 
		
		$token =urlencode( $session->get('token'));
		$PAYMENTREQUEST_0_AMT =urlencode ($session->get('PAYMENTREQUEST_0_AMT'));
		$PAYMENTREQUEST_0_INVNUM = urlencode($session->get('PAYMENTREQUEST_0_INVNUM'));
		$PAYMENTREQUEST_0_PAYMENTACTION = "Sale";
		$PAYMENTREQUEST_0_CURRENCYCODE = urlencode($session->get('PAYMENTREQUEST_0_CURRENCYCODE'));
		$PAYMENTREQUEST_0_NOTIFYURL = urlencode($session->get('PAYMENTREQUEST_0_NOTIFYURL'));
		$PAYERID = urlencode($session->get('PAYERID'));
		$serverName = urlencode($_SERVER['SERVER_NAME']);
		
		$nvpstr ='&PAYMENTREQUEST_0_INVNUM='.$PAYMENTREQUEST_0_INVNUM;
		$nvpstr.='&PAYMENTREQUEST_0_PAYMENTACTION='.$PAYMENTREQUEST_0_PAYMENTACTION;
		$nvpstr.='&PAYMENTREQUEST_0_AMT='.$PAYMENTREQUEST_0_AMT;
		$nvpstr.='&PAYMENTREQUEST_0_CURRENCYCODE='.$PAYMENTREQUEST_0_CURRENCYCODE;
		$nvpstr.='&PAYMENTREQUEST_0_NOTIFYURL='.$PAYMENTREQUEST_0_NOTIFYURL;
		$nvpstr.='&TOKEN='.$token;
		$nvpstr.='&PAYERID='.$PAYERID;
		$nvpstr.='&IPADDRESS='.$serverName ;
		$resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
		$ack = strtoupper($resArray["ACK"]);

		if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING'){
			$qe='UPDATE #__mue_usersubs SET usrsub_status="error" WHERE usrsub_id = "'.$usid.'"';
			$db->setQuery($qe);
			$db->execute();
			$this->error="An Error has occurred";
			return false;
		} else {
			if ($pinfo->sub_recurring) {
				$nvpHeader=$this->nvpHeader();
				if ($pinfo->discounted != -1) {
					$cost = $pinfo->discounted;
				} else {
					$cost = $pinfo->sub_cost;
				}
				$nvpstr="&TOKEN=".$token."&PROFILESTARTDATE=".date(DATE_ATOM,strtotime($sinfo->usrsub_end))."&DESC=".JURI::base()." Subscription"."&BILLINGPERIOD=".$pinfo->sub_period."&BILLINGFREQUENCY=".$pinfo->sub_length;
				$nvpstr.="&PROFILEREFERENCE=".urlencode ($session->get('ivn'))."&INITAMT=0.00&AMT=".$cost."&CURRENCYCODE=USD&EMAIL=".urlencode($session->get('payer_email'));
				$nvpstr.="&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital&L_PAYMENTREQUEST_0_NAME0=".$pinfo->sub_exttitle."&L_PAYMENTREQUEST_0_AMT0=".$cost."&L_PAYMENTREQUEST_0_QTY0=1";
				$nvpstr = $nvpHeader.$nvpstr;
				$resArray=$this->hash_call("CreateRecurringPaymentsProfile",$nvpstr);
				$ack = strtoupper($resArray["ACK"]);

				if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
					
					$token = $resArray['TOKEN'];
					$q= 'UPDATE #__mue_usersubs SET usrsub_rpprofile="'.$resArray['PROFILEID'].'", usrsub_rpstatus="'.$resArray['PROFILESTATUS'].'" WHERE usrsub_id = '.$usid;
					$db->setQuery($q);
					$db->execute();
				} else  {
					$q='UPDATE #__mue_usersubs SET usrsub_status="error" WHERE usrsub_id = "'.$usid.'"';
					$db->setQuery($q);
					$db->execute();
					$this->error="An error has occured";
					return false;
				}
			} else {
				$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$resArray['PAYMENTINFO_0_TRANSACTIONID'].'", usrsub_status="accepted" WHERE usrsub_id = "'.$usid.'"';
				$db->setQuery($q2);
				$db->execute();
			}
		}
		return true;
		
	}
	
	function confirmPayment($pinfo,$usid,$ppt) {
		$input = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$db  = JFactory::getDBO();
		$user  = JFactory::getUser();
		$app = Jfactory::getApplication();
		$iid = $input->get('Itemid');
		
		$nvpHeader=$this->nvpHeader();
		$nvpstr="&TOKEN=".$ppt;
		$nvpstr = $nvpHeader.$nvpstr;
		$resArray=$this->hash_call("GetExpressCheckoutDetails",$nvpstr);
		$ack = strtoupper($resArray["ACK"]);
		
		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
			$session->set('token',$ppt);
			$session->set('PAYERID',$resArray['PAYERID']);
			$session->set('EMAIL',$resArray['EMAIL']);
			$session->set('PAYMENTREQUEST_0_CURRENCYCODE',$resArray['PAYMENTREQUEST_0_CURRENCYCODE']);
			$session->set('PAYMENTREQUEST_0_AMT',$resArray['PAYMENTREQUEST_0_AMT']);
			$session->set('PAYMENTREQUEST_0_INVNUM',$resArray['PAYMENTREQUEST_0_INVNUM']);
			$session->set('PAYMENTREQUEST_0_NOTIFYURL',$resArray['PAYMENTREQUEST_0_NOTIFYURL']);
			//setup CORRELATIONID with INVNUM
			$token = $resArray['TOKEN'];
			$q= 'UPDATE #__mue_usersubs SET usrsub_status="verified",usrsub_email="'.$resArray['EMAIL'].'" WHERE usrsub_id = '.$usid;
			$db->setQuery($q);
			$db->execute();
		} else  {
			$q='UPDATE #__mue_usersubs SET usrsub_status="error" WHERE usrsub_id = "'.$usid.'"';
			$db->setQuery($q);
			$db->execute();
			$this->error="An error has occured";
			return false;
		}
		return true;
	}
	
	function submitPayment($pinfo,$start) {
		$this->checkToken() or die(JText::_('JINVALID_TOKEN'));
		$input = JFactory::getApplication()->input;
		$db  = JFactory::getDBO();
		$user  = JFactory::getUser();
		$app=Jfactory::getApplication();
		$iid = $input->get('Itemid');
		$session=JFactory::getSession();

		if ($pinfo->discounted != -1) {
			$cost = $pinfo->discounted;
		} else {
			$cost = $pinfo->sub_cost;
		}
		
		$q = 'INSERT INTO #__mue_usersubs (usrsub_user,usrsub_sub,usrsub_status,usrsub_type,usrsub_ip,usrsub_start,usrsub_end,usrsub_coupon,usrsub_cost) ';
		$q .= 'VALUES ('.$user->id.','.$pinfo->sub_id.',"notyetstarted","paypal","'.$_SERVER['REMOTE_ADDR'].'",';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .= ',DATE_ADD(';
		if ($start) $q .= '"'.$start.'"';
		else $q .= 'NOW()';
		$q .=',INTERVAL '.$pinfo->sub_length.' '.strtoupper($pinfo->sub_period).')';
		$q .= ',"'.$db->escape($pinfo->discountcode).'"';
		$q .= ',"'.$db->escape(number_format($cost,2)).'"';
		$q .= ')';
		$db->setQuery($q); $db->execute();
		$purchaseid = $db->insertid();
		
		$ivn=$purchaseid."-s".$pinfo->sub_id."-u".$user->id;
		$session->set('ivn',$ivn);
		$currencyCodeType="USD";
		$paymentType="Sale";
		$L_PAYMENTREQUEST_0_NAME0 = $pinfo->sub_exttitle;
		$L_PAYMENTREQUEST_0_DESC0 = JURI::base()." Subscription";
		$L_PAYMENTREQUEST_0_AMT0 = $cost;
		$L_PAYMENTREQUEST_0_QTY0 = 1;
		if ($pinfo->sub_recurring) {
			$L_BILLINGTYPE0="RecurringPayments";
			$L_BILLINGAGREEMENTDESCRIPTION0=JURI::base()." Subscription";
		}
		
		$itemamt = 0.00;
		$itemamt = $L_PAYMENTREQUEST_0_AMT0;
		$amt = $itemamt;
		
		$returnURL =urlencode(JURI::base().'index.php?option=com_mue&view=subscribe&layout=ppconfirm&Itemid='.$iid.'&purchaseid='.$purchaseid.'&plan='.$pinfo->sub_id);
		$cancelURL =urlencode(JURI::base().'index.php?option=com_mue&view=subscribe&layout=ppcancel&Itemid='.$iid.'&purchaseid='.$purchaseid.'&plan='.$pinfo->sub_id);
		$ipnURL= urlencode(JURI::base().'components/com_mue/paypalipn.php');
		
		$nvpstr="";
		$nvpstr  = "&NOSHIPPING=1";
		$nvpstr .= "&ALLOWNOTE=0";
		$nvpstr .= "&SOLUTIONTYPE=Sole";
		$nvpstr .= "&L_PAYMENTREQUEST_0_NAME0=".$L_PAYMENTREQUEST_0_NAME0;
		$nvpstr .= "&L_PAYMENTREQUEST_0_DESC0=".$L_PAYMENTREQUEST_0_DESC0;
		$nvpstr .= "&L_PAYMENTREQUEST_0_AMT0=".$L_PAYMENTREQUEST_0_AMT0;
		$nvpstr .= "&L_PAYMENTREQUEST_0_QTY0=".$L_PAYMENTREQUEST_0_QTY0;
		//$nvpstr .= "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital";
		if ($pinfo->sub_recurring) $nvpstr .= "&MAXAMT=$MAXAMT&L_BILLINGTYPE0=$L_BILLINGTYPE0&L_BILLINGAGREEMENTDESCRIPTION0=$L_BILLINGAGREEMENTDESCRIPTION0";
		$nvpstr .= "&PAYMENTREQUEST_0_AMT=".(string)$itemamt;
		$nvpstr .= "&PAYMENTREQUEST_0_ITEMAMT=".(string)$itemamt;
		$nvpstr .= "&PAYMENTREQUEST_0_CURRENCYCODE=".$currencyCodeType;
		$nvpstr .= "&PAYMENTREQUEST_0_PAYMENTACTION=".$paymentType;
		$nvpstr .= "&PAYMENTREQUEST_0_INVNUM=".$ivn;
		$nvpstr .= "&PAYMENTREQUEST_0_NOTIFYURL=".$ipnURL;
		$nvpstr .= "&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL ;
		$nvpstr = $nvpstr;
		
		$resArray=$this->hash_call("SetExpressCheckout",$nvpstr);
		
		$ack = strtoupper($resArray["ACK"]);
		$token = $resArray['TOKEN'];
		if($ack=="SUCCESS"){
			// Redirect to paypal.com here
			$qt='UPDATE #__mue_usersubs SET usrsub_status="started" WHERE usrsub_id = '.(int)$purchaseid;
			$db->setQuery($qt);
			$db->execute();
			$token = urldecode($resArray["TOKEN"]);
			$payPalURL = $this->PAYPAL_URL.$token;
			$app->redirect($payPalURL);
		} else {
			$q='UPDATE #__mue_usersubs SET usrsub_status="notyetstarted" WHERE usrsub_id = "'.(int)$purchaseid.'"';
			$db->setQuery($q);
			$db->execute();
			$this->error="An error has occured";
			return false;
		
		}
		return true;
	}
	
	function ipnResponse() {
		$db  = JFactory::getDBO();
		$ralogtxt = "";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$ralogtxt .= "$key: ".urldecode($value)."\r\n";
		}
		
		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_type = $_POST['txn_type'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		$pid = (int)$_POST['invoice'];
		
		if (!$this->verifyIPN()) {
			// HTTP ERROR
			$ralogtxt .= "ERROR";
		} else {


			$ralogtxt .= "VERIFIED";
			if ($txn_type == "new_case") {
				$q1='UPDATE #__mue_usersubs SET usrsub_status="dispute" WHERE usrsub_id = "'.$pid.'"';
				$db->setQuery($q1);
				$db->execute();
				$qsubid = 'SELECT * FROM #__mue_usersubs WHERE usrsub_id = "'.$pid.'"';
				$db->setQuery($qsubid);
				$sinfo = $db->loadObject();
			} else if ($txn_type=="recurring_payment") {
				$ralogtxt .= "\r\nrecurring_payment";
				$rpid = $_POST['recurring_payment_id'];
				$qsubid = 'SELECT * FROM #__mue_usersubs WHERE usrsub_rpprofile = "'.$rpid.'"';
				$db->setQuery($qsubid);
				$sinfo = $db->loadObject();
				$pid = $sinfo->usrsub_id;
				$qplan = 'SELECT * FROM #__mue_subs WHERE sub_id = "'.$sinfo->usrsub_sub.'"';
				$db->setQuery($qplan);
				$pinfo = $db->loadObject();
				switch ($payment_status) {
					case "Completed":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="completed", usrsub_end = DATE_ADD(usrsub_end,INTERVAL '.$pinfo->sub_length.' '.strtoupper($pinfo->sub_period).') WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$ralogtxt .= $q2;
						if (!$db->execute()) $ralogtxt .= $db->error();
						break;
				}
			} else if ($txn_type=="recurring_payment_profile_cancel") {
				$ralogtxt .= "\r\nrecurring_payment";
				$rpid = $_POST['recurring_payment_id'];
				$qsubid = 'SELECT * FROM #__mue_usersubs WHERE usrsub_rpprofile = "'.$rpid.'"';
				$db->setQuery($qsubid);
				$sinfo = $db->loadObject();
				$pid = $sinfo->usrsub_id;
				$qplan = 'SELECT * FROM #__mue_subs WHERE sub_id = "'.$sinfo->usrsub_sub.'"';
				$db->setQuery($qplan);
				$pinfo = $db->loadObject();
				$profile_status=$_POST['profile_status'];
				$q2='UPDATE #__mue_usersubs SET usrsub_status="completed", usrsub_rpstatus = "'.$profile_status.'" WHERE usrsub_id = "'.$pid.'"';
				$db->setQuery($q2);
				if (!$db->execute()) $ralogtxt .= $db->error();
			} else {
				$qsubid = 'SELECT * FROM #__mue_usersubs WHERE usrsub_id = "'.$pid.'"';
				$db->setQuery($qsubid);
				$sinfo = $db->loadObject();
				switch ($payment_status) {
					case "Refunded":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="refunded" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Completed":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="completed" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Canceled_Reversal":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="canceled_reversal" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Denied":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="denied" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Expired":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="expired" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Failed":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="failed" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Pending":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="pending" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Reversed":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="reversed" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Processed":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="completed" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
					case "Voided":
						$q2='UPDATE #__mue_usersubs SET usrsub_transid = "'.$txn_id.'", usrsub_status="voided" WHERE usrsub_id = "'.$pid.'"';
						$db->setQuery($q2);
						$db->execute();
						break;
				}
			}

			$usernotes = "PayPal IPN Update Status on Sub ID #$pid to: $payment_status \r\n";
			$qud = 'UPDATE #__mue_usergroup SET userg_notes = CONCAT(userg_notes,"'.$db->escape($usernotes).'") WHERE userg_user = '.$sinfo->usrsub_user;
			$db->setQuery($qud);
			$db->execute();

			return $sinfo->usrsub_user;
					

		}

		return false;
		
	}
	function nvpHeader()
	{
		$nvpHeaderStr = "";
	
		if($this->AUTH_MODE) {
			$AuthMode = $this->AUTH_MODE;
		}
		else {
	
			if((!empty($this->API_USERNAME)) && (!empty($this->API_PASSWORD)) && (!empty($this->API_SIGNATURE)) && (!empty($this->SUBJECT))) {
				$AuthMode = "THIRDPARTY";
			}
	
			else if((!empty($this->API_USERNAME)) && (!empty($this->API_PASSWORD)) && (!empty($this->API_SIGNATURE))) {
				$AuthMode = "3TOKEN";
			}
	
			elseif (!empty($this->AUTH_TOKEN) && !empty($this->AUTH_SIGNATURE) && !empty($this->AUTH_TIMESTAMP)) {
				$AuthMode = "PERMISSION";
			}
			elseif(!empty($this->SUBJECT)) {
				$AuthMode = "FIRSTPARTY";
			}
		}
		switch($AuthMode) {
	
			case "3TOKEN" :
				$nvpHeaderStr = "&PWD=".urlencode($this->API_PASSWORD)."&USER=".urlencode($this->API_USERNAME)."&SIGNATURE=".urlencode($this->API_SIGNATURE);
				break;
			case "FIRSTPARTY" :
				$nvpHeaderStr = "&SUBJECT=".urlencode($this->SUBJECT);
				break;
			case "THIRDPARTY" :
				$nvpHeaderStr = "&PWD=".urlencode($this->API_PASSWORD)."&USER=".urlencode($this->API_USERNAME)."&SIGNATURE=".urlencode($this->API_SIGNATURE)."&SUBJECT=".urlencode($this->SUBJECT);
				break;
			case "PERMISSION" :
				$nvpHeaderStr = $this->formAutorization($this->AUTH_TOKEN,$this->AUTH_SIGNATURE,$this->AUTH_TIMESTAMP);
				break;
		}
		return $nvpHeaderStr;
	}
	
	/**
	 * hash_call: Function to perform the API call to PayPal using API signature
	 * @methodName is name of API  method.
	 * @nvpStr is nvp string.
	 * returns an associtive array containing the response from the server.
	 */
	function hash_call($methodName,$nvpStr)
	{
		//declaring of global variables
		$session=JFactory::getSession();
		// form header string
		$nvpheader=$this->nvpHeader();
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->API_ENDPOINT);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		//in case of permission APIs send headers as HTTPheders
		if(!empty($this->AUTH_TOKEN) && !empty($this->AUTH_SIGNATURE) && !empty($this->AUTH_TIMESTAMP))
		{
			$headers_array[] = "X-PP-AUTHORIZATION: ".$nvpheader;
	
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
			curl_setopt($ch, CURLOPT_HEADER, false);
		}
		else
		{
			$nvpStr=$nvpheader.$nvpStr;
		}

	
		//check if version is included in $nvpStr else include the version.
		if(strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
			$nvpStr = "&VERSION=" . urlencode("65.1") . $nvpStr;
		}
	
		$nvpreq="METHOD=".urlencode($methodName).$nvpStr;
	
		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
	
		//getting response from server
		$response = curl_exec($ch);
	
		//convrting NVPResponse to an Associative Array
		$nvpResArray=$this->deformatNVP($response);
		$nvpReqArray=$this->deformatNVP($nvpreq);
		$session->set('nvpReqArray',$nvpReqArray);
	
		if (curl_errno($ch)) {
			// moving to display page to display curl errors
			$this->error='<p>'.curl_error($ch).'<br><br>'.$nvpreq.'</p>';
			return false;
		} else {
			//closing the curl
			curl_close($ch);
		}
	
		return $nvpResArray;
	}
	
	/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
	 * It is usefull to search for a particular key and displaying arrays.
	 * @nvpstr is NVPString.
	 * @nvpArray is Associative Array.
	 */
	function deformatNVP($nvpstr)
	{
	
		$intial=0;
		$nvpArray = array();
	
	
		while(strlen($nvpstr)){
			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
	
			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
		}
		return $nvpArray;
	}

	function formAutorization($auth_token,$auth_signature,$auth_timestamp)
	{
		$authString="token=".$auth_token.",signature=".$auth_signature.",timestamp=".$auth_timestamp ;
		return $authString;
	}

	public function verifyIPN()
	{
		if ( ! count($_POST)) {
			return false;
		}
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode('=', $keyval);
			if (count($keyval) == 2) {
				// Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
				if ($keyval[0] === 'payment_date') {
					if (substr_count($keyval[1], '+') === 1) {
						$keyval[1] = str_replace('+', '%2B', $keyval[1]);
					}
				}
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}
		// Build the body of the verification post request, adding the _notify-validate command.
		$req = 'cmd=_notify-validate';
		$get_magic_quotes_exists = false;
		if (function_exists('get_magic_quotes_gpc')) {
			$get_magic_quotes_exists = true;
		}
		foreach ($myPost as $key => $value) {
			if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			} else {
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}
		// Post the data back to PayPal, using curl. Throw exceptions if errors occur.
		$ch = curl_init($this->IPN_URL);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		$res = curl_exec($ch);
		if ( ! ($res)) {
			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			curl_close($ch);
			return false;
		}
		$info = curl_getinfo($ch);
		$http_code = $info['http_code'];
		if ($http_code != 200) {
			return false;
		}
		curl_close($ch);
		// Check if PayPal verifies the IPN data, and if so, return true.
		if ($res == "VERIFIED") {
			return true;
		} else {
			return false;
		}
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}
}
