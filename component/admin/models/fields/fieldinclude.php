<?php

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


class JFormFieldFieldInclude extends JFormField
{
	protected $type = 'FieldInclude';

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

		$html[] = '<select name="'.$this->name.'" class="inputbox" '.$attr.'>';
		$html[] = '<option value="">None</option>';
		// Build the query for the ordering list.
		$query = 'SELECT uf_id AS value, uf_name AS text' .
				' FROM #__mue_ufields' .
				' WHERE uf_type IN("cbox","yesno","mailchimp") ' .
				' ORDER BY ordering';
		$db->setQuery($query);
		$html[] = JHtml::_('select.options',$db->loadObjectList(),"value","text",$this->value);
		$html[] = '</select>';
		

		return implode($html);
	}
}
