<?php
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

class MUEModelAclist extends JModelLegacy
{
	protected function populateState()
	{
		$field = JRequest::getInt('field');
		$this->setState('aclist.field', $field);
	}
	
	function getUFields() {
		$app = JFactory::getApplication('administrator');
		$query = $this->_db->getQuery(true);
		$query->select('uf_sname AS value, CONCAT(uf_name," [",uf_sname,"]") AS text');
		$query->from('#__mue_ufields');
		$query->where('uf_type NOT IN ("brlist","cmlist","mailchimp","captcha")');
		$query->where('uf_cms = 0');
		$query->where('published = 1');
		$query->order('ordering');
		$this->_db->setQuery($query);
		$data=$this->_db->loadObjectList();
		
		$fields = array();
		$fields[] = JHtml::_('select.option', "user_group","MUE User Group [user_group]");
		$fields[] = JHtml::_('select.option', "site_url","Site URL [site_url]");
		$fields[] = JHtml::_('select.option', "username","Username [username]");
		foreach ($data as $d) {
			$fields[] = JHtml::_('select.option', $d->value,$d->text);
		}
		return $fields;
	}

	function getList()
	{
		// load config
		$cfg=MUEHelper::getConfig();

		// get list field
		$field = $this->getState('aclist.field');
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__mue_ufields');
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		$listField = $db->loadObject();

		// Load up AC connector
		require_once(JPATH_ROOT.'/components/com_mue/lib/activecampaign.php');

		$acClient = new ActiveCampaign($cfg->ackey,$cfg->acurl);

        // get fields
		$fields = $acClient->getFieldsByTypeId();

		// setup fields and list info for view
		$listField->list_textvars = $fields['text'];
		$listField->list_datevars = $fields['date'];

		// load params to object
		if (property_exists($listField, 'params'))
		{
			$registry = new JRegistry();
			$registry->loadString($listField->params);
			$listField->params = $registry->toObject();
		}

		return $listField;
	}

	public function save($data,$field)
	{
		$parameter = new JRegistry;
		$parameter->loadArray($data);
		$params = (string)$parameter;
		$pdata = $parameter->toObject();

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__mue_ufields');
		$query->set('params = '.$db->quote($params));
		$query->where('uf_id = '.$field);
		$db->setQuery($query);
		if ($db->query()) { return true; }
		else { $this->setError($db->getError()); return false; }
	}
}
