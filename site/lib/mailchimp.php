<?php
class MailChimp {
	
	var $apikey="";
	var $listid="";
	var $datacenter='';
	var $errormsg='';
	
	function MailChimp($apikey,$listid) {
		$this->apikey=$apikey;
		$this->listid=$listid;
		if (strstr($apikey,"-")){
        	list($key, $dc) = explode("-",$apikey,2);
            $this->datacenter = $dc;
        }
	}
	
	function updateUser($email,$info=NULL,$send_welcome,$email_type="html") {
		$replace_interests=true;
         
		$data = array(
		        'email_address'=>$email,
		        'apikey'=>$this->apikey,
		        'merge_vars' => $info,
		        'id' => $this->listid,
		        'replace_interests' => $replace_interests,
		        'email_type' => $email_type
		    );
		$payload = json_encode($data);

		$result = $this->sendData("listUpdateMember", $payload);
		if ($result->error) { $this->error=$this->datacenter.": ".$result->code." ".$result->error; return false; }
		return true;
		
	}
	
	function subscribeUser($email,$info=NULL,$send_welcome,$email_type="html") {
		$double_optin=false;
		$update_existing=true;
		$replace_interests=true;
         
		$data = array(
		        'email_address'=>$email,
		        'apikey'=>$this->apikey,
		        'merge_vars' => $info,
		        'id' => $this->listid,
		        'double_optin' => $double_optin,
		        'update_existing' => $update_existing,
		        'replace_interests' => $replace_interests,
		        'send_welcome' => $send_welcome,
		        'email_type' => $email_type
		    );
		$payload = json_encode($data);

		$result = $this->sendData("listSubscribe", $payload);
		if ($result->error) { $this->error=$this->datacenter.": ".$result->code." ".$result->error; return false; }
		return true;
		
	}
	
	function unsubscribeUser($email) {
		$delete_member=false;
		$send_goodbye=false;
		$send_notify=false;
         
		$data = array(
		        'email_address'=>$email,
		        'apikey'=>$this->apikey,
		        'id' => $this->listid,
		        'delete_member' => $delete_member,
		        'send_goodbye' => $send_goodbye,
		        'send_notify' => $send_notify
		    );
		$payload = json_encode($data);

		$result = $this->sendData("listUnsubscribe", $payload);
		if ($result->error) { $this->error=$this->datacenter.": ".$result->code." ".$result->error; return false; }
		return true;
	}
	
	function subStatus($email) {
		$data = array(
	        'email_address'=>$email,
	        'apikey'=>$this->apikey,
	        'id' => $this->listid
	    );
	    $payload = json_encode($data);
	    $result = $this->sendData("listMemberInfo", $payload);
	    if ($result->data[0]->error || $result->data[0]->status != "subscribed") return false;
	    else return $result->data[0];
	}
	
	protected function sendData($method,$payload) {
		$submit_url = "http://".$this->datacenter.".api.mailchimp.com/1.3/?method=".$method;
		 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $submit_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));
		 
		$result = curl_exec($ch);
		curl_close ($ch);
		$data = json_decode($result);
		return $data;
	}
	
}