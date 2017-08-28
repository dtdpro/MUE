<?php
defined('_JEXEC') or die;

abstract class JHtmlMUEFields
{
	public static function cbox($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		if (!empty($value)) {
			$checked = ($value == '1') ? ' checked="checked"' : '';
		}
		else {
			$checked = '';
		}
		$html .= '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class="uf_radio"';
		if ($field->uf_req && $field->uf_type=="cbox") { 
			$html .=  ' data-rule-required="true" data-msg-required="This Field is required"'; 
		}
		$html .=  $checked.'/>'."\n";
		$html .=  '<label for="jform_'.$sname.'">';
		$html .=  ' '.$field->uf_name.'</label>'."\n";
	
		return $html;
	}
	
	public static function mcbox($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$first = true;
		foreach ($field->options as $o) {
			if (!empty($value)) $checked = in_array($o->value,$value) ? ' checked="checked"' : '';
			else $checked = '';
			$html .= '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="uf_radio" id="jform_'.$sname.$o->value.'"';
			if ($field->uf_req && $first) {
				$html .= ' data-rule-required="true"';
				if ($field->uf_min) $html .= ' data-rule-minlength="'.$field->uf_min.'"';
				if ($field->uf_max) $html .= ' data-rule-maxlength="'.$field->uf_max.'"';
				$html .= ' data-msg-required="This Field is required"';
				if ($field->uf_min) $html .= ' data-msg-minlength="Select at least '.$field->uf_min.'"';
				if ($field->uf_max) $html .= ' data-msg-maxlength="Select at most '.$field->uf_max.'"';
				$first=false;
			}
			$html .= $checked.'/>'."\n";
			$html .= '<label for="jform_'.$sname.$o->value.'">';
			$html .= ' '.$o->text.'</label>'."\n";
		
		}
		
		return $html;
		
	}
	
	public static function multi($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$first=true;
		foreach ($field->options as $o) {
			if (!empty($value)) $checked = in_array($o->value,$value) ? ' checked="checked"' : '';
			else $checked = '';
			$html .= '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class="uf_radio"';
			if ($field->uf_req && $first) { $html .= ' data-rule-required="true" data-msg-required="This Field is required"'; $first=false;}
			$html .= $checked.'/>'."\n";
			$html .= '<label for="jform_'.$sname.$o->value.'">';
			$html .= ' '.$o->text.'</label>'."\n";
				
		}
	
		return $html;
	}
	
	public static function dropdown($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-control uf_field uf_select input-sm uk-width-1-1"';
		if ($field->uf_req) { $html .= ' data-rule-required="true" data-msg-required="This Field is required"'; }
		$html .= '>';
		foreach ($field->options as $o) {
			if (!empty($value)) $selected = in_array($o->value,$value) ? ' selected="selected"' : '';
			else $selected = '';
			$html .= '<option value="'.$o->value.'"'.$selected.'>';
			$html .= ' '.$o->text.'</option>';
		}
		$html .= '</select>';
	
		return $html;
	}
	
	public static function mlist($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="form-control uf_field uf_mselect input-sm" size="4" multiple="multiple"';
		if ($field->uf_req) {
			$html .= ' data-rule-required="true"';
			if ($field->uf_min) $html .= ' data-rule-minlength="'.$field->uf_min.'"';
			if ($field->uf_max) $html .= ' data-rule-maxlength="'.$field->uf_max.'"';
			$html .= ' data-msg-required="This Field is required"';
			if ($field->uf_min) $html .= ' data-msg-minlength="Select at least '.$field->uf_min.'"';
			if ($field->uf_max) $html .= ' data-msg-maxlength="Select at most '.$field->uf_max.'"';
			$first=false;
		}
		$html .= '>';
		foreach ($field->options as $o) {
			if (!empty($value)) $selected = in_array($o->value,$value) ? ' selected="selected"' : '';
			else $selected = '';
			$html .= '<option value="'.$o->value.'"'.$selected.'>';
			$html .= ' '.$o->text.'</option>';
		}
		$html .= '</select>';
	
		return $html;
	}

	public static function textbox($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";

		$html .=  '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$value.'" class="form-control uf_field input-sm uk-width-1-1" type="text"';
		if ($field->uf_req) {
			$html .=  ' data-rule-required="true"';
			if ($field->uf_min) $html .=  ' data-rule-minlength="'.$field->uf_min.'"';
			if ($field->uf_max) $html .=  ' data-rule-maxlength="'.$field->uf_max.'"';
			if ($field->uf_type=="email") $html .=  ' data-rule-email="true"';
			if ($field->uf_match) $html .=  ' data-rule-equalTo="#jform_'.$field->uf_match.'"';
			if ($field->uf_type == "username" && $field->uf_cms) $html .=  ' data-rule-remote="'.JURI::base( true ).'/components/com_mue/helpers/chkuser.php"';
			if ($field->uf_type == "email" && !$field->uf_match && $field->uf_cms) $html .=  ' data-rule-remote="'.JURI::base( true ).'/components/com_mue/helpers/chkemail.php"';
			$html .= ' data-msg-required="This Field is required"';
			if ($field->uf_min) $html .=  ' data-msg-minlength="Min length '.$field->uf_min.' characters"';
			if ($field->uf_max) $html .=  ' data-msg-maxlength="Max length '.$field->uf_max.' characters"';
			if ($field->uf_type=="email") $html .=  ' data-msg-email="Email address must be valid"';
			if ($field->uf_match) $html .=  ' data-msg-equalTo="Fields must match"';
			if ($field->uf_type=="username" && $field->uf_cms) $html .=  ' data-msg-remote="The username you have entered is already registered."';
			if ($field->uf_type=="email" && !$field->uf_match && $field->uf_cms) $html .=  ' data-msg-remote="The email address you have entered is already registered."';
		}
		$html .=  '>';

		return $html;
	}
	
	public static function password($field,$require=false)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<input name="jform['.$sname.']" id="jform_'.$sname.'" class="form-control uf_field input-sm uk-width-1-1" size="20" type="password" ';
		if ($require) {
			$html .= ' data-rule-required="true"';
			$html .= ' data-msg-required="This Field is required"';
		}
		$html .= 'data-rule-minlength="8"';
		if ($field->uf_match) $html .= ' data-rule-equalTo="#jform_'.$field->uf_match.'"';
		$html .= ' data-msg-minlength="Minimum length 8 characters"';
		if ($field->uf_match) $html .= ' data-msg-equalTo="Fields must match"';
		$html .= '>';
	
		return $html;
	}
	
	public static function textar($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="form-control uf_field input-sm uk-width-1-1"';
		if ($field->uf_req) { $html .= ' data-rule-required="true" data-msg-required="This Field is required"'; }
		$html .= '>'.$value.'</textarea>';
	
		return $html;
	}
	
	public static function yesno($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-control uf_field input-sm" size="1">';
		$selected = ' selected="selected"';
		$html .= '<option value="1"';
		$html .= ($value == "1") ? $selected : '';
		$html .= '>Yes</option>';
		$html .= '<option value="0"';
		$html .= ($value == "0") ? $selected : '';
		$html .= '>No</option>';
			
		$html .= '</select>';
	
		return $html;
	}
	
	public static function birthday($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$selected = ' selected="selected"';
		$html .= '<select id="jform_'.$sname.'_month" name="jform['.$sname.'_month]" class="form-control uf_bday_month input-sm">';
		$html .= '<option value="01"'; $html .= (substr($value,0,2) == "01") ? $selected : ''; $html .= '>01 - January</option>';
		$html .= '<option value="02"'; $html .= (substr($value,0,2) == "02") ? $selected : ''; $html .= '>02 - February</option>';
		$html .= '<option value="03"'; $html .= (substr($value,0,2) == "03") ? $selected : ''; $html .= '>03 - March</option>';
		$html .= '<option value="04"'; $html .= (substr($value,0,2) == "04") ? $selected : ''; $html .= '>04 - April</option>';
		$html .= '<option value="05"'; $html .= (substr($value,0,2) == "05") ? $selected : ''; $html .= '>05 - May</option>';
		$html .= '<option value="06"'; $html .= (substr($value,0,2) == "06") ? $selected : ''; $html .= '>06 - June</option>';
		$html .= '<option value="07"'; $html .= (substr($value,0,2) == "07") ? $selected : ''; $html .= '>07 - July</option>';
		$html .= '<option value="08"'; $html .= (substr($value,0,2) == "08") ? $selected : ''; $html .= '>08 - August</option>';
		$html .= '<option value="09"'; $html .= (substr($value,0,2) == "09") ? $selected : ''; $html .= '>09 - September</option>';
		$html .= '<option value="10"'; $html .= (substr($value,0,2) == "10") ? $selected : ''; $html .= '>10 - October</option>';
		$html .= '<option value="11"'; $html .= (substr($value,0,2) == "11") ? $selected : ''; $html .= '>11 - November</option>';
		$html .= '<option value="12"'; $html .= (substr($value,0,2) == "12") ? $selected : ''; $html .= '>12 - December</option>';
		$html .= '</select>';
		$html .= '<select id="jform_'.$sname.'_day" name="jform['.$sname.'_day]" class="form-control uf_bday_day input-sm">';
		for ($i=1;$i<=31;$i++) {
			if ($i<10) $val = "0".$i;
			else $val=$i;
			$html .= '<option value="'.$val.'"';
			$html .= (substr($value,2,2) == $val) ? $selected : '';
			$html .= '>'.$val.'</option>';
		}
		$html .=  '</select>';
	
		return $html;
	}
}