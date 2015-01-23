<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$cfg = MUEHelper::getConfig();
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if ($this->retry) echo '<div id="system" class="uk-article">';
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#regform").validate({
			errorClass:"uf_error uk-form-danger",
			validClass:"uf_valid uk-form-success",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").parent("div").next("div") );
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
	if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "mailchimp" && $f->uf_type != "cmlist" && $f->uf_type != "brlist") echo $f->uf_name; 
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
	if ($f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist" || $f->uf_type=="brlist") {
		if ($f->uf_type == "cbox") $val=$f->value;
		else $val=false;
		echo JHtml::_('muefields.cbox',$f,$val);
	}

	//multi checkbox
	if ($f->uf_type=="mcbox") {
		echo JHtml::_('muefields.mcbox',$f,$f->value);
	}

	//radio
	if ($f->uf_type=="multi") {
		echo JHtml::_('muefields.multi',$f,$f->value);
	}

	//dropdown
	if ($f->uf_type=="dropdown") {
		echo JHtml::_('muefields.dropdown',$f,$f->value);
	}
	
	//multilist
	if ($f->uf_type=="mlist") {
		echo JHtml::_('muefields.mlist',$f,$f->value);
	}
	
	
	//text field, phone #, email, username
	if ($f->uf_type=="textbox" || $f->uf_type=="email" || $f->uf_type=="username" || $f->uf_type=="phone") {
		echo '<div class=""><input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$f->value.'" class="form-control uf_field input-sm" type="text"';
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
		echo '></div>';
	}
	
	//password
	if ($f->uf_type=="password") {
		echo JHtml::_('muefields.password',$f);
	}
	
	//text area
	if ($f->uf_type=="textar") {
		echo JHtml::_('muefields.textar',$f,$f->value);
	}
	
	//Yes no
	if ($f->uf_type=="yesno") {
		echo JHtml::_('muefields.yesno',$f,$f->value);
		
	}
	
	
	//Birthday
	if ($f->uf_type=="birthday") {
		echo JHtml::_('muefields.birthday',$f,$f->value);
	}
	

	//captcha
	if ($f->uf_type=="captcha") {
		echo '<div class=""><img id="captcha_img" src="'.JURI::base(true).'/components/com_mue/lib/securimage/securimage_show.php" alt="CAPTCHA Image" />';
		echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="" class="form-control uf_field input-sm" type="text"';
		if ($f->uf_req) { 
			echo ' data-rule-required="true"';
			echo ' data-msg-required="This Field is required"';
		}
		echo '>';
		echo '<span class="uf_note">';
		echo '<a href="#" onclick="document.getElementById(\'captcha_img\').src = \''.JURI::base(true).'/components/com_mue/lib/securimage/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
		echo '</span></div>';
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
echo '<input name="saveprofile" id="savereg" value="Submit Registration" type="submit" class="button uk-button">';
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