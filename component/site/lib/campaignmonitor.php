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
	
	function getListObject($listid="") {
		require_once dirname(__FILE__).'/campaignmonitor/csrest_lists.php';
		return new CS_REST_Lists($listid,$this->apikey);
	}
	
	function getSubscriberObject($listid="") {
		require_once dirname(__FILE__).'/campaignmonitor/csrest_subscribers.php';
		return new CS_REST_Subscribers($listid,$this->apikey);
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
	
	function getListDetails($listid="") {
		if (!$listid) return false;
		$list = $this->getListObject($listid);
		$result = $list->get();
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code;
			return false;
		}
	}
	
	function getListStats($listid="") {
		if (!$listid) return false;
		$list = $this->getListObject($listid);
		$result = $list->get_stats();
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code;
			return false;
		}
	}
	
	function getListWebhooks($listid="") {
		if (!$listid) return false;
		$list = $this->getListObject($listid);
		$result = $list->get_webhooks();
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code;
			return false;
		}
	}
	
	function getListCustomFields($listid="") {
		if (!$listid) return false;
		$list = $this->getListObject($listid);
		$result = $list->get_custom_fields();
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code;
			return false;
		}
	}
	
	function addSubscriber($listid="",$subinfo="") {
		if (!$listid || !$subinfo) return false;
		$sub = $this->getListObject($listid);
		$result = $sub->add($subinfo);
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code;
			return false;
		}
	}
	
	
	
	
	
	
	
	
}