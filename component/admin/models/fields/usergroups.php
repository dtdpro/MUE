<?php

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldUserGroups extends JFormField
{
	protected $type = 'UserGroups';

	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


		// Build the query for the ordering list.
		$query = 'SELECT ug_id AS value, ug_name AS text' .
				' FROM #__mue_ugroups' .
				' ORDER BY ug_name';
		$db->setQuery($query);
		$html[] = JHtml::_('select.genericlist',$db->loadObjectList(),$this->name,$attr, "value","text",$this->value);
		

		return implode($html);
	}
}
