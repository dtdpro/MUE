<?php // no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USER_PROEDIT_PAGE_TITLE').'</h2>';
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		var validator = jQuery("#regform").validate({
            errorClass:"uf_error uk-form-danger",
            validClass:"uf_valid uk-form-success",
            ignore: ".ignore",
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.appendTo(element.parent("div"));
                error.addClass("uk-alert uk-alert-danger uk-form-controls-text");
            },
            onsubmit: false
        });

        jQuery("#regform").submit(function( event ) {
            event.preventDefault();
            if (validator.form()) {
                jQuery("#saveprofile").attr("disabled", true);
                jQuery("#saveprofile").prop("value", "Saving, please wait...");
                jQuery("#regform")[0].submit();
            }
        });

	});


</script>
<?php 
echo '<div id="mue-user-edit">';
echo '<form action="" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';
if (!$this->one_group) echo '<div class="uk-form-row uk-margin-top mue-user-edit-row mue-rowh"><div class="uk-form-label mue-user-edit-label uk-text-bold">'.JText::_('COM_MUE_USER_PROEDIT_LABEL_USER_GROUP').'</div><div class="uk-form-controls uk-form-controls-text mue-user-edit-hdr">'.$this->userinfo->userGroupName.'</div></div>';
$ri=0;
foreach($this->userfields as $f) {
	$sname = $f->uf_sname;

    if ($f->uf_type == "html") {
        echo $f->uf_note;
        continue;
    }

	if ($f->uf_change) {
		if ($ri==1) $ri=0;
		else $ri=1;
		echo '<div class="uk-form-row uk-margin-top mue-user-edit-row mue-row'.($ri % 2).'">';
		echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">';
		if ($f->uf_req) echo "*";
		//field title
		if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "cmlist" && $f->uf_type != "aclist") echo $f->uf_name;
		echo '</div>';
		echo '<div class="uk-form-controls mue-user-edit-value';
		if ($f->uf_type=="cbox" || $f->uf_type=="cmlist" || $f->uf_type=="aclist" || $f->uf_type == "message") {
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
		if ($f->uf_type=="cbox" || $f->uf_type=="cmlist" || $f->uf_type=="aclist") {
			echo JHtml::_('muefields.cbox',$f,$this->userinfo->$sname);
		}
	
		//multi checkbox
		if ($f->uf_type=="mcbox") {
			echo JHtml::_('muefields.mcbox',$f,$this->userinfo->$sname);
		}
	
		//radio
		if ($f->uf_type=="multi") {
			echo JHtml::_('muefields.multi',$f,$this->userinfo->$sname);
		}
	
		//dropdown
		if ($f->uf_type=="dropdown") {
			echo JHtml::_('muefields.dropdown',$f,$this->userinfo->$sname);
		}
		
		//multilist
		if ($f->uf_type=="mlist") {
			echo JHtml::_('muefields.mlist',$f,$this->userinfo->$sname);
		}
		
		//text field, phone #, email, username
		if ($f->uf_type=="textbox" || $f->uf_type=="email" || $f->uf_type=="username" || $f->uf_type=="phone") {
			echo JHtml::_('muefields.textbox',$f,$this->userinfo->$sname);
		}
		
		//password
		if ($f->uf_type=="password") {
			echo JHtml::_('muefields.password',$f);
		}
		
		//text area
		if ($f->uf_type=="textar") {
			echo JHtml::_('muefields.textar',$f,$this->userinfo->$sname);
		}
		
		//Yes no
		if ($f->uf_type=="yesno") {
			echo JHtml::_('muefields.yesno',$f,$this->userinfo->$sname);
			
		}
	
		//Birthday
		if ($f->uf_type=="birthday") {
			echo JHtml::_('muefields.birthday',$f,$this->userinfo->$sname);
		}

        //Country
        if ($f->uf_type=="country") {
            echo JHtml::_('muefields.country',$f,$this->userinfo->$sname);
        }

		//Timezone
		if ($f->uf_type=="timezone") {
			echo JHtml::_('muefields.timezone',$f,$this->userinfo->timezone);
		}
		
		if ($f->uf_note) echo '<span class="uf_note uk-text-small">'.$f->uf_note.'</span>';
		if ($f->uf_type=="timezone")  echo '<span class="uf_note uk-text-small">Default Timezone is: '.$this->defaultTimezone.'</span>';
		echo '</div>';
		echo '</div>';
	} else {
		if ($this->userinfo->$sname != '') {
			if ($ri==1) $ri=0;
			else $ri=1;
			echo '<div class="uk-form-row uk-margin-top mue-user-edit-row mue-row'.($ri % 2).'">';
			echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">';
			if ($f->uf_req) echo "*";
			//field title
			if ($f->uf_type != "cbox") echo $f->uf_name;
			echo '</div>';
			echo '<div class="uk-form-controls uk-form-controls-text mue-user-edit-value">';
			echo $this->userinfo->$sname;
			echo '<input type="hidden" name="jform['.$sname.']" value="'.$this->userinfo->$sname.'">';
			echo '</div>';
			echo '</div>';
		}
	}


} 
echo '<div class="uk-form-row uk-margin-top uk-margin-bottom mue-user-edit-row">';
echo '<div class="uk-form-label mue-user-edit-label">';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-submit">';
echo '<input name="saveprofile" id="saveprofile" value="'.JText::_('COM_MUE_USER_PROEDIT_BUTTON_SAVE').'" type="submit" class="button uk-button uk-button-primary">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="user">';
echo '<input type="hidden" name="layout" value="saveuser">';
echo '<input type="hidden" name="jform[userGroupID]" value="'.$this->userinfo->userGroupID.'">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
?>