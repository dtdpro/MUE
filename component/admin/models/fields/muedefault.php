<?php

defined('JPATH_PLATFORM') or die;


class JFormFieldMUEDefault extends JFormField
{
	protected $type = 'MUEDefault';

	protected function getInput()
	{
		$type = $this->form->getValue('uf_type');
		
		$id	= (int) $this->form->getValue('uf_id');
		if (!$id) return '<input type="hidden" name="' . $name . '" value="0" />' . '<span class="readonly">Available Once Field Saved</span>';
		
		switch ($type) {
			case "multi":
			case "dropdown":
				$html = $this->getMultiChoiceOpts(false);
				break;
			case "mcbox":
			case "mlist":
				$html = $this->getMultiChoiceOpts(true);
				break;
			case "cbox":
			case "yesno":
				$html = $this->getChecked();
				break;
			case "mailchimp":
				$html = $this->getMailChimp();
				break;
			case "textbox":
			case "textar":
			default:
				$html = $this->getTextField();
				break;
		}
		
		return $html;
	}
	
	protected function getTextField() {
		// Initialize some field attributes.
		$size = ' size="60"';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		
		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';
	}
	
	protected function getMailchimp()
	{
		require_once(JPATH_ROOT.'/administrator/components/com_mue/helpers/mue.php');
		require_once(JPATH_ROOT.'/components/com_mue/lib/mailchimp.php');
		
		$app =& JFactory::getApplication('site');
		$db  =& JFactory::getDBO();
		$cfg=MUEHelper::getConfig();
		if (!$cfg->mckey) return $this->getTextField();
		$mc = new MailChimp($cfg->mckey);
		
		
		// Initialize variables.
		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
	
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
	
	
		$lists = $mc->getLists();
		$html[] = JHtml::_('select.genericlist',$lists,$this->name,$attr, "id","name",$this->value);
	
	
		return implode($html);
	}
	
	protected function getChecked()
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
	
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
	
		$options = array();
		$options[] = JHtml::_('select.option', "1","Yes");
		$options[] = JHtml::_('select.option', "0","No");
		
		$html[] = '<select name="'.$this->name.'" class="inputbox" '.$attr.'>';
		$html[] = JHtml::_('select.options',$options,"value","text",$this->value);
		$html[] = '</select>';
	
	
		return implode($html);
	}
	
	protected function getMultiChoiceOpts($multi = false)
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		if ($multi) {
			$attr .= ' multiple ';
			$attr .= ' size="10"';
		} else {
			
		}
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
	
		if (!$id) return $this->getTextField();
	
		// Build the query for the ordering list.
		$html[] = '<select name="'.$this->name.'" class="inputbox" '.$attr.'>';
		$html[] = '<option value="">None</option>';
		$query = 'SELECT opt_id AS value, opt_text AS text' .
				' FROM #__mue_ufields_opts' .
				' WHERE opt_field = ' . $id .
				' ORDER BY ordering';
		$db->setQuery($query);
		$html[] = JHtml::_('select.options',$db->loadObjectList(),"value","text",$this->value);
		$html[] = '</select>';
	
	
		return implode($html);
	}
}