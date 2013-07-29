<?php
class CampaignMonitor {
	
	public $apikey="";
	public $clientid="";
	public $error='';
	
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
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
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
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
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
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function getActiveSubscribers($listid="",$page=1,$limit=500) {
		if (!$listid) return false;
		$list = $this->getListObject($listid);
		$result = $list->get_active_subscribers('',$page,$limit);
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
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
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
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
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function addListWebhook($listid="",$webhook="") {
		if (!$listid || !$webhook) return false;
		$list = $this->getListObject($listid);
		$result = $list->create_webhook($webhook);
		if($result->was_successful()) {
			return true;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function addSubscriber($listid="",$subinfo="") {
		if (!$listid || !$subinfo) return false;
		$sub = $this->getSubscriberObject($listid);
		$result = $sub->add($subinfo);
		if($result->was_successful()) {
			return true;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function updateSubscriber($listid="",$email="",$subinfo="") {
		if (!$listid || !$subinfo || !$email) return false;
		$sub = $this->getSubscriberObject($listid);
		$result = $sub->update($email,$subinfo);
		if($result->was_successful()) {
			return true;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function removeSubscriber($listid="",$email="") {
		if (!$listid || !$email) return false;
		$sub = $this->getSubscriberObject($listid);
		$result = $sub->unsubscribe($email);
		if($result->was_successful()) {
			return true;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function getSubscriberDetails($listid="",$email="") {
		if (!$listid || !$email) return false;
		$sub = $this->getSubscriberObject($listid);
		$result = $sub->get($email);
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	function importSubscribers($listid="",$subscribers=array(),$resubscribe=true) {
		if (!$listid || !$resubscribe) return false;
		$sub = $this->getSubscriberObject($listid);
		$result = $sub->import($subscribers,$resubscribe);
		if($result->was_successful()) {
			return $result->response;
		} else {
			$this->error = 'Failed with code '.$result->http_status_code.' '.print_r($result->response,true);
			return false;
		}
	}
	
	
	
	
	
	
	
	
}