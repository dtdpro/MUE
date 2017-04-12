<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$cfg = MUEHelper::getConfig();
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if (($this->retry || $this->show_header) && $this->params->get('divwrapper',1)) echo '<div id="system" class="uk-article">';
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#regform").validate({
			errorClass:"uf_error uk-form-danger",
			validClass:"uf_valid uk-form-success",
            errorElement: "div",
            errorPlacement: function(error, element) {
                error.appendTo( element.parent("div"));
                error.addClass("uk-alert uk-alert-danger uk-form-controls-text");
            }
	    });

	});


</script>
<?php
if ($this->show_header) {
	echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USERREG_PAGE_TITLE').'</h2>';
	if ($cfg->show_progbar && $cfg->subscribe) {
		echo '<div class="uk-progress"><div class="uk-progress-bar" style="width: 0%;"></div></div>';
	}
	echo $cfg->REG_PAGE_CONTENT;
	echo '<div id="mue-user-reg">';
    echo '<form action="" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';
} else if ($this->retry){
	echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USERREG_PAGE_TITLE').'</h2>';
	echo $cfg->REG_PAGE_CONTENT;
	echo '<div id="mue-user-reg">';
	echo '<form action="" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';
	echo '<div class="uk-form-row mue-user-reg-row mue-rowh"><div class="uk-form-label mue-user-reg-label uk-text-bold">'.JText::_('COM_MUE_USERREG_LABEL_USER_GROUP').'</div><div class="uk-form-controls uk-form-controls-text mue-user-reg-hdr">'.$this->groupinfo[0]->ug_name.'</div></div>';
}
foreach($this->userfields as $f) {
	if ($ri==1) $ri=0;
	else $ri=1;
	echo '<div class="uk-form-row mue-user-reg-row mue-row'.($ri % 2).'">';
	echo '<div class="uk-form-label mue-user-reg-label uk-text-bold">';
	if ($f->uf_req) echo "*";
	$sname = $f->uf_sname;
	//field title
	if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "mailchimp" && $f->uf_type != "cmlist" && $f->uf_type != "brlist") echo $f->uf_name; 
	echo '</div>';
	echo '<div class="uk-form-controls mue-user-reg-value';
	if ($f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist" || $f->uf_type=="brlist" || $f->uf_type == "message") {
	    echo ' uk-form-controls-text';
	}
	echo '">';
	if ($f->uf_type == "mcbox" || $f->uf_type == "mlist") {
		if (!$f->uf_min && !$f->uf_max) echo '<em>(Select all that apply)</em><br />';
		if ($f->uf_min && !$f->uf_max) echo '<em>(Select at least '.$f->uf_min.')</em><br />';
		if (!$f->uf_min && $f->uf_max) echo '<em>(Select at most '.$f->uf_max.')</em><br />';
		if ($f->uf_min && $f->uf_max) echo '<em>(Select at least '.$f->uf_min.' and at most '.$f->uf_max.')</em><br />';
	}

	//Message
	if ($f->uf_type == "message") echo '<div class="uk-alert">'.$f->uf_name.'</div>';
	
	//checkbox
	if ($f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist" || $f->uf_type=="brlist") {
		if ($f->uf_type == "cbox") $val=$f->value;
		else $val = $cfg->mailing_list;
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
		echo JHtml::_('muefields.textbox',$f,$f->value);
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
	echo '</div>';
} 
echo '<div class="uk-form-row mue-user-reg-row">';
echo '<div class="uk-form-label mue-user-reg-label">';
echo '</div>';
echo '<div class="uk-form-controls mue-user-reg-submit">';
echo '<input name="saveprofile" id="savereg" value="'.JText::_('COM_MUE_USERREG_BUTTON_SUBMIT').'" type="submit" class="button uk-button">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="userreg">';
echo '<input type="hidden" name="layout" value="reguser">';
echo '<input type="hidden" name="jform[userGroupID]" value="'.$this->groupinfo[0]->ug_id.'">';
echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
if ($this->retry || $this->show_header) {
	echo '</div>';
	if ($this->params->get('divwrapper',1)) echo '</div>';
}
?>