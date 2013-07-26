<?php
class CampaignMonitor {
	
	var $apikey="";
	var $clientid="";
	var $error='';
	
	function CampaignMonitor($apikey,$clientid	) {
		$this->apikey=$apikey;
		$this->clientid=$clientid;
	}
	
	function getClientObject() {
		require_once dirname(__FILE__).'/campaignmonitor/csrest_clients.php';
		return new CS_REST_Clients($this->clientid,$this->apikey);
	}
	
	function getLists() {
		$client = $this->getClientObject();
		$result = $client->get_lists();
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code;
			return false;
		}
	}
	
}