<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$cfg = MUEHelper::getConfig();
if ($this->retry) echo '<div id="system">';
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#regform").validate({
			errorClass:"uf_error",
			validClass:"uf_valid",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });

	});


</script>
<?php 
if ($this->retry) echo '<div id="mue-user-reg">';
echo '<form action="" method="post" name="regform" id="regform">';
if ($this->retry) echo '<div class="mue-user-reg-row mue-rowh"><div class="mue-user-reg-label">User Group</div><div class="mue-user-reg-hdr">'.$this->groupinfo[0]->ug_name.'</div></div>';
foreach($this->userfields as $f) {
	if ($ri==1) $ri=0;
	else $ri=1;
	echo '<div class="mue-user-reg-row mue-row'.($ri % 2).'">';
	echo '<div class="mue-user-reg-label">';
	if ($f->uf_req) echo "*";
	$sname = $f->uf_sname;
	//field title
	if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "mailchimp") echo $f->uf_name;
	echo '</div>';
	echo '<div class="mue-user-reg-value">';
	if ($f->uf_type == "mcbox" || $f->uf_type == "mlist") {
		if (!$f->uf_min && !$f->uf_max) echo '<em>(Select all that apply)</em><br />';
		if ($f->uf_min && !$f->uf_max) echo '<em>(Select at least '.$f->uf_min.')</em><br />';
		if (!$f->uf_min && $f->uf_max) echo '<em>(Select at most '.$f->uf_max.')</em><br />';
		if ($f->uf_min && $f->uf_max) echo '<em>(Select at least '.$f->uf_min.' and at most '.$f->uf_max.')</em><br />';
	}

	//Message
	if ($f->uf_type == "message") echo '<strong>'.$f->uf_name.'</strong>';
	
	//checkbox
	if ($f->uf_type=="cbox" || $f->uf_type=="mailchimp") {
		if (!empty($f->value)) $checked = ($f->value == '1') ? ' checked="checked"' : '';
		else $checked = '';
		echo '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class="uf_radio"';
		if ($f->uf_req && $f->uf_type=="cbox") { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
		echo $checked.'/>'."\n";
		echo '<label for="jform_'.$sname.'">';
		echo ' '.$f->uf_name.'</label><br />'."\n";
	}

	//multi checkbox
	if ($f->uf_type=="mcbox") {
		$first = true;
		foreach ($f->options as $o) {
			if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
			else $checked = '';
			echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="uf_radio" id="jform_'.$sname.$o->value.'"';
			if ($f->uf_req && $first) {
				echo ' data-rule-required="true"';
				if ($f->uf_min) echo ' data-rule-minlength="'.$f->uf_min.'"';
				if ($f->uf_max) echo ' data-rule-maxlength="'.$f->uf_max.'"';
				echo ' data-msg-required="This Field is required"';
				if ($f->uf_min) echo ' data-msg-minlength="Select at least '.$f->uf_min.'"';
				if ($f->uf_max) echo ' data-msg-maxlength="Select at most '.$f->uf_max.'"';
				$first=false;
			}
			echo $checked.'/>'."\n";
			echo '<label for="jform_'.$sname.$o->value.'">';
			echo ' '.$o->text.'</label><br />'."\n";
			
		}
	}

	//radio
	if ($f->uf_type=="multi") {
		$first=true;
		foreach ($f->options as $o) {
			if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
			else $checked = '';
			echo '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class="uf_radio"';
			if ($f->uf_req && $first) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; $first=false;}
			echo $checked.'/>'."\n";
			echo '<label for="jform_'.$sname.$o->value.'">';
			echo ' '.$o->text.'</label><br />'."\n";
			
		}
	}

	//dropdown
	if ($f->uf_type=="dropdown") {
		echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="uf_field uf_select" size="1"';
		if ($f->uf_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
		echo '>';
		foreach ($f->options as $o) {
			if (!empty($f->value)) $selected = in_array($o->value,$f->value) ? ' selected="selected"' : '';
			else $selected = '';
			echo '<option value="'.$o->value.'"'.$selected.'>';
			echo ' '.$o->text.'</option>';
		}
		echo '</select>';
	}
	
	//multilist
	if ($f->uf_type=="mlist") {
		echo '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="uf_field uf_mselect" size="4" multiple="multiple"';
		if ($f->uf_req) {
			echo ' data-rule-required="true"';
			if ($f->uf_min) echo ' data-rule-minlength="'.$f->uf_min.'"';
			if ($f->uf_max) echo ' data-rule-maxlength="'.$f->uf_max.'"';
			echo ' data-msg-required="This Field is required"';
			if ($f->uf_min) echo ' data-msg-minlength="Select at least '.$f->uf_min.'"';
			if ($f->uf_max) echo ' data-msg-maxlength="Select at most '.$f->uf_max.'"';
			$first=false;
		}
		echo '>';
		foreach ($f->options as $o) {
			if (!empty($f->value)) $selected = in_array($o->value,$f->value) ? ' selected="selected"' : '';
			else $selected = '';
			echo '<option value="'.$o->value.'"'.$selected.'>';
			echo ' '.$o->text.'</option>';
		}
		echo '</select>';
	}
	
	
	//text field, phone #, email, username
	if ($f->uf_type=="textbox" || $f->uf_type=="email" || $f->uf_type=="username" || $f->uf_type=="phone") {
		echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$f->value.'" class="uf_field" type="text"';
		if ($f->uf_req) { 
			echo ' data-rule-required="true"';
			if ($f->uf_min) echo ' data-rule-minlength="'.$f->uf_min.'"';
			if ($f->uf_max) echo ' data-rule-maxlength="'.$f->uf_max.'"';
			if ($f->uf_type=="email") echo ' data-rule-email="true"';
			if ($f->uf_match) echo ' data-rule-equalTo="#jform_'.$f->uf_match.'"';
			if ($f->uf_type == "username" && $f->uf_cms) echo ' data-rule-remote="'.JURI::base( true ).'/components/com_mue/helpers/chkuser.php"';
			if ($f->uf_type == "email" && !$f->uf_match && $f->uf_cms) echo ' data-rule-remote="'.JURI::base( true ).'/components/com_mue/helpers/chkemail.php"';
			echo ' data-msg-required="This Field is required"';
			if ($f->uf_min) echo ' data-msg-minlength="Min length '.$f->uf_min.' characters"';
			if ($f->uf_max) echo ' data-msg-maxlength="Max length '.$f->uf_max.' characters"';
			if ($f->uf_type=="email") echo ' data-msg-email="Email address must be valid"';
			if ($f->uf_match) echo ' data-msg-equalTo="Fields must match"';
			if ($f->uf_type=="username" && $f->uf_cms) echo ' data-msg-remote="Username already registered"';
			if ($f->uf_type=="email" && !$f->uf_match && $f->uf_cms) echo ' data-msg-remote="Email Already registered"';
		}
		echo '>';
	}
	
	//password
	if ($f->uf_type=="password") {
		echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" class="uf_field" size="20" type="password" ';
		echo 'data-rule-required="true" data-rule-minlength="8"';
		if ($f->uf_match) echo ' data-rule-equalTo="#jform_'.$f->uf_match.'"';
		echo ' data-msg-required="This Field is required" data-msg-minlength="Minimum length 8 characters"';
		if ($f->uf_match) echo ' data-msg-equalTo="Fields must match"';
		echo '>';
	}
	
	//text area
	if ($f->uf_type=="textar") {
		echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="uf_field"';
		if ($f->uf_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
		echo '>'.$f->value.'</textarea>';
	}
	
	//Yes no
	if ($f->uf_type=="yesno") {
		echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="uf_field" size="1">';
		$selected = ' selected="selected"';
		echo '<option value="1"';
		echo ($f->value == "1") ? $selected : '';
		echo '>Yes</option>';
		echo '<option value="0"';
		echo ($f->value == "0") ? $selected : '';
		echo '>No</option>';
		
		echo '</select>';
		
	}
	
	
	//Birthday
	if ($f->uf_type=="birthday") {
		$selected = ' selected="selected"';
		echo '<select id="jform_'.$sname.'_month" name="jform['.$sname.'_month]" class="uf_bday_month">';
		echo '<option value="01"'; echo (substr($f->value,0,2) == "01") ? $selected : ''; echo '>01 - January</option>';
		echo '<option value="02"'; echo (substr($f->value,0,2) == "02") ? $selected : ''; echo '>02 - February</option>';
		echo '<option value="03"'; echo (substr($f->value,0,2) == "03") ? $selected : ''; echo '>03 - March</option>';
		echo '<option value="04"'; echo (substr($f->value,0,2) == "04") ? $selected : ''; echo '>04 - April</option>';
		echo '<option value="05"'; echo (substr($f->value,0,2) == "05") ? $selected : ''; echo '>05 - May</option>';
		echo '<option value="06"'; echo (substr($f->value,0,2) == "06") ? $selected : ''; echo '>06 - June</option>';
		echo '<option value="07"'; echo (substr($f->value,0,2) == "07") ? $selected : ''; echo '>07 - July</option>';
		echo '<option value="08"'; echo (substr($f->value,0,2) == "08") ? $selected : ''; echo '>08 - August</option>';
		echo '<option value="09"'; echo (substr($f->value,0,2) == "09") ? $selected : ''; echo '>09 - September</option>';
		echo '<option value="10"'; echo (substr($f->value,0,2) == "10") ? $selected : ''; echo '>10 - October</option>';
		echo '<option value="11"'; echo (substr($f->value,0,2) == "11") ? $selected : ''; echo '>11 - November</option>';
		echo '<option value="12"'; echo (substr($f->value,0,2) == "12") ? $selected : ''; echo '>12 - December</option>';
		echo '</select>';
		echo '<select id="jform_'.$sname.'_day" name="jform['.$sname.'_day]" class="uf_bday_day">';
		for ($i=1;$i<=31;$i++) {
			if ($i<10) $val = "0".$i;
			else $val=$i;
			echo '<option value="'.$val.'"';
			echo (substr($f->value,2,2) == $val) ? $selected : '';
			echo '>'.$val.'</option>';
		}
		echo '</select>';	
	}
	

	//captcha
	if ($f->uf_type=="captcha") {
		echo '<img id="captcha_img" src="'.JURI::base(true).'/components/com_mue/lib/securimage/securimage_show.php" alt="CAPTCHA Image" />';
		echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="" class="uf_field" type="text"';
		if ($f->uf_req) { 
			echo ' data-rule-required="true"';
			echo ' data-msg-required="This Field is required"';
		}
		echo '>';
		echo '<span class="uf_note">';
		echo '<a href="#" onclick="document.getElementById(\'captcha_img\').src = \''.JURI::base(true).'/components/com_mue/lib/securimage/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
		echo '</span>';
	}
	
	if ($f->uf_note && $f->uf_type!="captcha") echo '<span class="uf_note">'.$f->uf_note.'</span>';

	echo '</div>';
	echo '<div class="mue-user-reg-error">';
	//if ($f->uf_type=="multi" || $f->uf_type=="mcbox") echo '<label id="jform_'.$sname.'-lbl" for="jform['.$sname.']" class="uf_error"></label>';
	//else echo '<label id="jform_'.$sname.'-lbl" for="jform_'.$sname.'" class="uf_error"></label>';
	echo '</div>';
	echo '</div>';
} 
echo '<div class="mue-user-reg-row">';
echo '<div class="mue-user-reg-label">';
echo '</div>';
echo '<div class="mue-user-reg-submit">';
echo '<input name="saveprofile" id="savereg" value="Submit Registration" type="submit" class="button">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="userreg">';
echo '<input type="hidden" name="layout" value="reguser">';
echo '<input type="hidden" name="jform[userGroupID]" value="'.$this->groupinfo[0]->ug_id.'">';
echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
if ($this->retry) echo '</div>';
if ($this->retry) echo '</div>';
?>