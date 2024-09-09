<?php
use League\OAuth2\Client\Provider\GenericProvider;
use GuzzleHttp\Exception\BadResponseException;


class ActiveCampaign {
	private $apiKey = "";
	private $apiUrl = "";
	private $provider;
	private $error = '';

	public function __construct($apiKey, $apiUrl) {
		$this->apiKey = $apiKey;
		$this->apiUrl = $apiUrl;
		$this->provider = $this->getProvider();

	}

	public function getList($id) {
		$data = $this->getData('/api/3/lists/'.$id);

		return $data['list'];
	}

	public function getListsById() {
		$data = $this->getData('/api/3/lists?limit=100');

		$listsById = [];

		foreach ($data['lists'] as $l) {
			$listsById[$l['id']] = $l['name'];
		}

        if ($data['meta']['total'] > 100) {
            $data = $this->getData('/api/3/lists?limit=100&offset=100');

            foreach ($data['lists'] as $l) {
                $listsById[$l['id']] = $l['name'];
            }
        }

        return $listsById;
	}

	public function getContactListsIdsSubscribedTo($contactId) {
		$data = $this->getData("/api/3/contacts/".$contactId."/contactLists");

		$listIds=[];
		if (isset($data['contactLists'])) {
			foreach ($data['contactLists'] as $cl) {
				if ($cl['status'] == 1) {
					$listIds[] = $cl['list'];
				}
			}
		}

		return $listIds;
	}

	public function changeListSub($listId,$contactId,$subStatus) {
		// /api/3/contactLists
		$data['contactList']['list'] = $listId;
		$data['contactList']['contact'] = $contactId;
		if ($subStatus) {
			$data['contactList']['status'] = 1;
		} else {
			$data['contactList']['status'] = 2;
		}
		$data['contactList']['sourceid'] = 4;

		$response = $this->postData('/api/3/contactLists',$data);
	}

	public function getFieldsByTypeId() {
		$data = $this->getData('/api/3/fields?limit=100');

		$fieldsByTypeId = [];

		foreach ($data['fields'] as $f) {
			$fieldsByTypeId[$f['type']][$f['id']] = $f['title'];
		}

		return $fieldsByTypeId;
	}

	public function getContact($emailAddress) {
		$data = $this->getData("/api/3/contacts?email=".$emailAddress);

		if (isset($data['contacts'])) {
			if ( count($data['contacts']) ) {
				return $data['contacts'][0];
			}
		}

		return false;
	}

	public function updateContactFields($contactId,$fieldData) {
		$data = [];
		$contact = [];
		$contact['fieldValues'] = $fieldData;
		$data['contact'] = $contact;
		$response = $this->putData('/api/3/contacts/'.$contactId,$data);
	}

	public function updateContactEmail($contactId,$newEmailAddress) {
		$data = [];
		$contact = [];
		$contact['email'] = $newEmailAddress;
		$data['contact'] = $contact;
		$response = $this->putData('/api/3/contacts/'.$contactId,$data);
	}

	public function updateContactUserGroup($contactId,$fieldId,$group) {
		$data = [];
		$contact = [];
		$contact['fieldValues'] = [['field'=>$fieldId,'value'=>$group]];
		$data['contact'] = $contact;
		$response = $this->putData('/api/3/contacts/'.$contactId,$data);
	}

	public function syncContact($emailAddress,$firstName,$lastName,$fieldData) {
		$data = [];
		$contact = [];
		$contact['email'] = $emailAddress;
		$contact['firstName'] = $firstName;
		$contact['lastName'] = $lastName;
		$contact['fieldValues'] = $fieldData;
		$data['contact'] = $contact;
		$response = $this->postData('/api/3/contact/sync',$data);

		return $response;
	}

	protected function getData($url) {
		$request = $this->provider->getRequest('GET',$this->apiUrl.$url,[
			'headers' => [
				'api-token' => $this->apiKey,
				'Accept' => 'application/json'
			]
		]);
		return $this->provider->getParsedResponse( $request );
	}

	protected function postData($url,$data=[]) {
		$request = $this->provider->getRequest('POST',$this->apiUrl.$url,[
			'headers' => [
				'api-token' => $this->apiKey,
				'Accept' => 'application/json',
				'Content-Type' => 'application/json'
			],
			'body' => json_encode($data)

		]);

		try {
			$response = $this->provider->getParsedResponse( $request );
		} catch (BadResponseException $e) {
			$this->error = json_decode((string)$e->getResponse()->getBody(),true)['message'];
			return false;
		}

		return $response;
	}

	protected function putData($url,$data=[]) {
		$request = $this->provider->getRequest('PUT',$this->apiUrl.$url,[
			'headers' => [
				'api-token' => $this->apiKey,
				'Accept' => 'application/json',
				'Content-Type' => 'application/json'
			],
			'body' => json_encode($data)

		]);

		try {
			$response = $this->provider->getParsedResponse( $request );
		} catch (BadResponseException $e) {
			$this->error = json_decode((string)$e->getResponse()->getBody(),true)['message'];
			return false;
		}

		return $response;
	}

	private function getProvider() {
		return new GenericProvider([
			'urlAuthorize' => 'https://127.0.0.1',
			'urlAccessToken' => 'https://127.0.0.1',
			'urlResourceOwnerDetails' => 'none'
		]);
	}

	/**
	 * @return string
	 */
	public function getError(): string {
		return $this->error;
	}
}