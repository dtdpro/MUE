<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$cfg = MUEHelper::getConfig();
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#regform").validate({
            ignore: [],
			errorClass:"uf_error uk-form-danger",
			validClass:"uf_valid uk-form-success",
            errorElement: "div",
            errorPlacement: function(error, element) {
                error.appendTo( element.parent("div"));
                error.addClass("uk-alert uk-alert-danger uk-form-controls-text");
            },
            submitHandler: function (form) {
                if (typeof ga === 'function') {
                    ga('send', 'event', 'MUE', 'Registration', 'Registration Submission');
                }
                if (typeof gtag === 'function') {
                    gtag('event', 'Registration', {
                        'event_category': 'MUE',
                        'event_label': 'Registration Submission'
                    });
                }
                $(form).submit();
            }
	    });

	});

	function reCapChecked() {
        jQuery('#reCapChecked').val("checked");
    }


</script>
<?php
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USERREG_PAGE_TITLE').'</h2>';

// show progress bar if it and subscription are enabled
if ($cfg->show_progbar && $cfg->subscribe) {
    echo '<div class="uk-progress"><div class="uk-progress-bar" style="width: 0%;"></div></div>';
}
echo $cfg->REG_PAGE_CONTENT;
echo '<div id="mue-user-reg">';
echo '<form action="" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';

// Show selected group if more than one group exists
if (!$this->single_group) {
    echo '<div class="uk-form-row mue-user-reg-row mue-rowh"><div class="uk-form-label mue-user-reg-label uk-text-bold">'.JText::_('COM_MUE_USERREG_LABEL_USER_GROUP').'</div><div class="uk-form-controls uk-form-controls-text mue-user-reg-hdr">'.$this->groupinfo[0]->ug_name.'</div></div>';
}

// Preset row coloring
$ri=1;

// Display Fields
foreach($this->userfields as $f) {
	$sname = $f->uf_sname;

    // Start Row
	echo '<div class="uk-form-row uk-margin-top mue-user-reg-row mue-row'.($ri % 2).'">';

	// Field Label
	echo '<div class="uk-form-label mue-user-reg-label uk-text-bold">';
	if ($f->uf_req) echo "*";
	if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "cmlist" && $f->uf_type != "aclist") {
	    echo $f->uf_name;
	}
	echo '</div>';

	// Start Field
	echo '<div class="uk-form-controls mue-user-reg-value';
	if ($f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist" || $f->uf_type=="aclist" || $f->uf_type == "message") {
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
	if ($f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist" || $f->uf_type=="aclist") {
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
		echo JHtml::_('muefields.password',$f,true);
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

	if ($f->uf_note && $f->uf_type!="captcha") echo '<span class="uf_note">'.$f->uf_note.'</span>';

	// End Field
	echo '</div>';

	// End Row
	echo '</div>';

	if ($ri==1) $ri=0;
	else $ri=1;
}

// ReCAPTCHA
if ($cfg->rc_config == "visible" ) {
	echo '<div class="uk-form-row mue-user-reg-row uk-margin-top">';
	echo '<div class="uk-form-label mue-user-reg-label">';
	echo '</div>';
	echo '<div class="uk-form-controls mue-user-reg-value">';
	echo '<input type="hidden" id="reCapChecked" name="reCapChecked" value="" data-rule-required="true" data-msg-required="reCaptcha Required">';
	echo '<div class="g-recaptcha" data-callback="reCapChecked" data-sitekey="'.$cfg->rc_api_key.'"></div>';
	echo '</div></div>';
}

// Submit Button
echo '<div class="uk-form-row mue-user-reg-row uk-margin-top uk-margin-bottom">';
echo '<div class="uk-form-label mue-user-reg-label">';
echo '</div>';
echo '<div class="uk-form-controls mue-user-reg-submit">';
echo '<input name="saveprofile" id="savereg" value="'.JText::_('COM_MUE_USERREG_BUTTON_SUBMIT').'" type="submit" class="button uk-button uk-button-primary">';
echo '</div></div>';


echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="userreg">';
echo '<input type="hidden" name="layout" value="reguser">';
echo '<input type="hidden" name="jform[userGroupID]" value="'.$this->groupinfo[0]->ug_id.'">';
echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';

//if ($this->retry) {
	echo '</div>';
//}
?>