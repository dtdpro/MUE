<?php
class MailChimp {
	
	var $apikey="";
	var $listid="";
	var $datacenter='';
	var $error='';
	
	function MailChimp($apikey,$listid="") {
		$this->apikey=$apikey;
		$this->listid=$listid;
		if (strstr($apikey,"-")){
        	list($key, $dc) = explode("-",$apikey,2);
            $this->datacenter = $dc;
        }
	}
	
	function updateUser($email,$info=NULL,$send_welcome,$email_type="html",$list="") {
		if (!$list) $list = $this->listid;
		$replace_interests=true;
         
		$data = array(
		        'email_address'=>$email,
		        'apikey'=>$this->apikey,
		        'merge_vars' => $info,
		        'id' => $list,
		        'replace_interests' => $replace_interests,
		        'email_type' => $email_type
		    );
		$payload = json_encode($data);

		$result = $this->sendData("listUpdateMember", $payload);
		if ($result->error) { $this->error=$this->datacenter.": ".$result->code." ".$result->error; return false; }
		return true;
		
	}
	
	function subscribeUser($email,$info=NULL,$send_welcome,$email_type="html",$list="") {
		if (!$list) $list = $this->listid;
		$double_optin=false;
		$update_existing=true;
		$replace_interests=true;
         
		$data = array(
		        'email_address'=>$email,
		        'apikey'=>$this->apikey,
		        'merge_vars' => $info,
		        'id' => $list,
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
	
	function unsubscribeUser($email,$list="") {
		if (!$list) $list = $this->listid;
		$delete_member=false;
		$send_goodbye=false;
		$send_notify=false;
         
		$data = array(
		        'email_address'=>$email,
		        'apikey'=>$this->apikey,
		        'id' => $list,
		        'delete_member' => $delete_member,
		        'send_goodbye' => $send_goodbye,
		        'send_notify' => $send_notify
		    );
		$payload = json_encode($data);

		$result = $this->sendData("listUnsubscribe", $payload);
		if ($result->error) { $this->error=$this->datacenter.": ".$result->code." ".$result->error; return false; }
		return true;
	}
	
	function subStatus($email,$list="") {
		if (!$list) $list = $this->listid;
		$data = array(
	        'email_address'=>$email,
	        'apikey'=>$this->apikey,
	        'id' => $list
	    );
	    $payload = json_encode($data);
	    $result = $this->sendData("listMemberInfo", $payload);
	    if ($result->data[0]->error || $result->data[0]->status != "subscribed") return false;
	    else return $result->data[0];
	}
	
	function getLists($list="") {
		if (!$list) $list = $this->listid;
		$data = array(
	        'apikey'=>$this->apikey,
			'limit'=>"100"
	    );
		if ($list) {
			$filters=array("list_id"=>$list);
			$data["filters"]=$filters;
		}
	    $payload = json_encode($data);
	    $result = $this->sendData("lists", $payload);
	    return $result->data;
	}
	
	function getListInterestGroupings($list="") {
		if (!$list) $list = $this->listid;
		$data = array(
	        'apikey'=>$this->apikey,
			'id'=>$list
	    );
	    $payload = json_encode($data);
	    $result = $this->sendData("listInterestGroupings", $payload);
	    return $result;
	}
	
	function getListMergeVars($list="") {
		if (!$list) $list = $this->listid;
		$data = array(
	        'apikey'=>$this->apikey,
			'id'=>$list
	    );
	    $payload = json_encode($data);
	    $result = $this->sendData("listMergeVars", $payload);
	    return $result;
	}
	
	function getListMembers($list="",$limit=1500,$start=0) {
		if (!$list) $list = $this->listid;
		$data = array(
	        'apikey'=>$this->apikey,
			'id'=>$list,
			'limit'=>$limit,
			'start'=>$start
	    );
	    $payload = json_encode($data);
	    $result = $this->sendData("listMembers", $payload);
		if ($result->error) { $this->error=$this->datacenter.": ".$result->code." ".$result->error; return false; }
	    else  return $result->data;
	}
	
	function listBatchSubscribe($batch,$list="") {
		if (!$list) $list = $this->listid;
		$double_optin=false;
		$update_existing=true;
		$replace_interests=true;
		 
		$data = array(
				'apikey'=>$this->apikey,
				'batch' => $batch,
				'id' => $list,
				'double_optin' => $double_optin,
				'update_existing' => $update_existing,
				'replace_interests' => $replace_interests
		);
		$payload = json_encode($data);
	
		$result = $this->sendData("listBatchSubscribe", $payload);
		return $result;
	
	}
	
	function getListWebhooks($list="") {
		if (!$list) $list = $this->listid;
		$data = array(
	        'apikey'=>$this->apikey,
			'id'=>$list
	    );
	    $payload = json_encode($data);
	    $result = $this->sendData("listWebhooks", $payload);
	    return $result;
	}
	
	
	protected function sendData($method,$payload) {
		$submit_url = "https://".$this->datacenter.".api.mailchimp.com/1.3/?method=".$method;
		 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $submit_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		 
		$result = curl_exec($ch);
		curl_close ($ch);
		$data = json_decode($result);
		return $data;
	}
	
	public function getError() { return $this->error; }
	
}