<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
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
		$html .= '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class="uf_radio uk-checkbox"';
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
			$html .= '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="uf_radio uk-checkbox" id="jform_'.$sname.$o->value.'"';
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
			$html .= '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class="uf_radio uk-radio"';
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
		
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-control uf_field uf_select input-sm uk-width-1-1 uk-select"';
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
		
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="form-control uf_field uf_mselect input-sm uk-select" size="4" multiple="multiple"';
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

		$html .=  '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$value.'" class="form-control uf_field input-sm uk-width-1-1 uk-input" type="text"';
		if ($field->uf_req) {
			$html .=  ' data-rule-required="true"';
			if ($field->uf_min) $html .=  ' data-rule-minlength="'.$field->uf_min.'"';
			if ($field->uf_max) $html .=  ' data-rule-maxlength="'.$field->uf_max.'"';
			if ($field->uf_type=="email") $html .=  ' data-rule-email="true"';
			if ($field->uf_match) $html .=  ' data-rule-equalTo="#jform_'.$field->uf_match.'"';
			$html .= ' data-msg-required="This Field is required"';
			if ($field->uf_min) $html .=  ' data-msg-minlength="Min length '.$field->uf_min.' characters"';
			if ($field->uf_max) $html .=  ' data-msg-maxlength="Max length '.$field->uf_max.' characters"';
			if ($field->uf_type=="email") $html .=  ' data-msg-email="Email address must be valid"';
			if ($field->uf_match) $html .=  ' data-msg-equalTo="Fields must match"';
		}
		$html .=  '>';

		return $html;
	}
	
	public static function password($field,$require=false)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<input name="jform['.$sname.']" id="jform_'.$sname.'" class="form-control uf_field input-sm uk-width-1-1 uk-input" size="20" type="password" ';
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
		
		$html .= '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="form-control uf_field input-sm uk-width-1-1 uk-textarea"';
		if ($field->uf_req) { $html .= ' data-rule-required="true" data-msg-required="This Field is required"'; }
		$html .= '>'.$value.'</textarea>';
	
		return $html;
	}
	
	public static function yesno($field,$value=null)
	{
		$sname = $field->uf_sname;
		$html = "";
		
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-control uf_field input-sm uk-select" size="1">';
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
		$html .= '<select id="jform_'.$sname.'_month" name="jform['.$sname.'_month]" class="form-control uf_bday_month input-sm uk-select uk-width-2-3">';
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
		$html .= '<select id="jform_'.$sname.'_day" name="jform['.$sname.'_day]" class="form-control uf_bday_day input-sm uk-select uk-width-1-3">';
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

    public static function country($field,$value=null)
    {
        $sname = $field->uf_sname;
        $html = "";

        $html .= '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-control uf_field uf_select input-sm uk-width-1-1 uk-select"';
        if ($field->uf_req) { $html .= ' data-rule-required="true" data-msg-required="This Field is required"'; }
        $html .= '>';
        foreach (JHtmlMUEFields::$country_list as $o) {
            if (!empty($value)) $selected = ($o == $value) ? ' selected="selected"' : '';
            else $selected = '';
            $html .= '<option value="'.$o.'"'.$selected.'>';
            $html .= ' '.$o.'</option>';
        }
        $html .= '</select>';

        return $html;
    }

	public static function timezone($field,$value=null)
	{
		$sname = $field->uf_sname;
		$selected = ' selected="selected"';
		$html = "";
		$html .= '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-control uf_field input-sm uk-select" size="1">';
		$html .= '<option value="">- Use Default -</option>';

		$zoneGroups = ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'];
		$zones = \DateTimeZone::listIdentifiers();

		$groups = [];

		// Build the group lists.
		foreach ($zones as $zone) {
			// Time zones not in a group we will ignore.
			if (strpos($zone, '/') === false) {
				continue;
			}

			// Get the group/locale from the timezone.
			list($group, $locale) = explode('/', $zone, 2);

			// Only use known groups.
			if (\in_array($group, $zoneGroups)) {
				// Initialize the group if necessary.
				if (!isset($groups[$group])) {
					$groups[$group] = [];
				}

				// Only add options where a locale exists.
				if (!empty($locale)) {
					$groups[$group][$zone] = ['zone' => $zone,'label'=>str_replace('_', ' ', $locale)];
				}
			}
		}

		// Sort the group lists.
		ksort($groups);

		foreach ($groups as &$location) {
			sort($location);
		}

		foreach ($groups as $groupName=>$location) {
			$html .= '<optgroup label="'.$groupName.'">';
			foreach ($location as $locale) {
				$html .= '<option value="'.$locale['zone'].'"';
				$html .= ( $value == $locale['zone'] ) ? $selected : '';
				$html .= '>'.$locale['label'].'</option>';
			}
			$html .= '</optgroup>';
		}

		$html .= '</select>';

		return $html;
	}

    public static $country_list = array(
        "United States",
        "Afghanistan",
        "Albania",
        "Algeria",
        "Andorra",
        "Angola",
        "Antigua and Barbuda",
        "Argentina",
        "Armenia",
        "Australia",
        "Austria",
        "Azerbaijan",
        "Bahamas",
        "Bahrain",
        "Bangladesh",
        "Barbados",
        "Belarus",
        "Belgium",
        "Belize",
        "Benin",
        "Bhutan",
        "Bolivia",
        "Bosnia and Herzegovina",
        "Botswana",
        "Brazil",
        "Brunei",
        "Bulgaria",
        "Burkina Faso",
        "Burundi",
        "Cambodia",
        "Cameroon",
        "Canada",
        "Cape Verde",
        "Central African Republic",
        "Chad",
        "Chile",
        "China",
        "Colombi",
        "Comoros",
        "Congo (Brazzaville)",
        "Congo",
        "Costa Rica",
        "Cote d'Ivoire",
        "Croatia",
        "Cuba",
        "Cyprus",
        "Czech Republic",
        "Denmark",
        "Djibouti",
        "Dominica",
        "Dominican Republic",
        "East Timor (Timor Timur)",
        "Ecuador",
        "Egypt",
        "El Salvador",
        "Equatorial Guinea",
        "Eritrea",
        "Estonia",
        "Ethiopia",
        "Fiji",
        "Finland",
        "France",
        "Gabon",
        "Gambia, The",
        "Georgia",
        "Germany",
        "Ghana",
        "Greece",
        "Grenada",
        "Guatemala",
        "Guinea",
        "Guinea-Bissau",
        "Guyana",
        "Haiti",
        "Honduras",
        "Hungary",
        "Iceland",
        "India",
        "Indonesia",
        "Iran",
        "Iraq",
        "Ireland",
        "Israel",
        "Italy",
        "Jamaica",
        "Japan",
        "Jordan",
        "Kazakhstan",
        "Kenya",
        "Kiribati",
        "Korea, North",
        "Korea, South",
        "Kuwait",
        "Kyrgyzstan",
        "Laos",
        "Latvia",
        "Lebanon",
        "Lesotho",
        "Liberia",
        "Libya",
        "Liechtenstein",
        "Lithuania",
        "Luxembourg",
        "Macedonia",
        "Madagascar",
        "Malawi",
        "Malaysia",
        "Maldives",
        "Mali",
        "Malta",
        "Marshall Islands",
        "Mauritania",
        "Mauritius",
        "Mexico",
        "Micronesia",
        "Moldova",
        "Monaco",
        "Mongolia",
        "Morocco",
        "Mozambique",
        "Myanmar",
        "Namibia",
        "Nauru",
        "Nepal",
        "Netherlands",
        "New Zealand",
        "Nicaragua",
        "Niger",
        "Nigeria",
        "Norway",
        "Oman",
        "Pakistan",
        "Palau",
        "Panama",
        "Papua New Guinea",
        "Paraguay",
        "Peru",
        "Philippines",
        "Poland",
        "Portugal",
        "Qatar",
        "Romania",
        "Russia",
        "Rwanda",
        "Saint Kitts and Nevis",
        "Saint Lucia",
        "Saint Vincent",
        "Samoa",
        "San Marino",
        "Sao Tome and Principe",
        "Saudi Arabia",
        "Senegal",
        "Serbia and Montenegro",
        "Seychelles",
        "Sierra Leone",
        "Singapore",
        "Slovakia",
        "Slovenia",
        "Solomon Islands",
        "Somalia",
        "South Africa",
        "Spain",
        "Sri Lanka",
        "Sudan",
        "Suriname",
        "Swaziland",
        "Sweden",
        "Switzerland",
        "Syria",
        "Taiwan",
        "Tajikistan",
        "Tanzania",
        "Thailand",
        "Togo",
        "Tonga",
        "Trinidad and Tobago",
        "Tunisia",
        "Turkey",
        "Turkmenistan",
        "Tuvalu",
        "Uganda",
        "Ukraine",
        "United Arab Emirates",
        "United Kingdom",
        "Uruguay",
        "Uzbekistan",
        "Vanuatu",
        "Vatican City",
        "Venezuela",
        "Vietnam",
        "Yemen",
        "Zambia",
        "Zimbabwe"
    );
}