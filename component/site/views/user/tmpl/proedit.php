<?php // no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
?>
<h2 class="componentheading uk-article-title">User Profile Edit</h2>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#regform").validate({
			errorClass:"uf_error uk-form-danger",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").parent("div").next("div") );
		    }
	    });

	});


</script>
<?php 
echo '<div id="mue-user-edit">';
echo '<form action="" method="post" name="regform" id="regform">';
echo '<div class="mue-user-edit-row mue-rowh"><div class="mue-user-edit-label">User Group</div><div class="mue-user-edit-hdr">'.$this->userinfo->userGroupName.'</div></div>';
foreach($this->userfields as $f) {
	$sname = $f->uf_sname;
	if ($f->uf_change) {
		if ($ri==1) $ri=0;
		else $ri=1;
		echo '<div class="mue-user-edit-row mue-row'.($ri % 2).'">';
		echo '<div class="mue-user-edit-label">';
		if ($f->uf_req) echo "*";
		//field title
		if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "mailchimp" && $f->uf_type != "cmlist" && $f->uf_type != "brlist") echo $f->uf_name;
		echo '</div>';
		echo '<div class="mue-user-edit-value">';
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
			echo '<div class=""><input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->userinfo->$sname.'" class="form-control uf_field input-sm" type="text"';
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
		
		if ($f->uf_note) echo '<span class="uf_note">'.$f->uf_note.'</span>';
		echo '</div>';
		echo '<div class="mue-user-edit-error">';
		
		echo '</div>';
		echo '</div>';
	} else {
		if ($this->userinfo->$sname != '') {
			if ($ri==1) $ri=0;
			else $ri=1;
			echo '<div class="mue-user-edit-row mue-row'.($ri % 2).'">';
			echo '<div class="mue-user-edit-label">';
			if ($f->uf_req) echo "*";
			//field title
			if ($f->uf_type != "cbox") echo $f->uf_name;
			echo '</div>';
			echo '<div class="mue-user-edit-value">';
			echo $this->userinfo->$sname;
			echo '<input type="hidden" name="jform['.$sname.']" value="'.$this->userinfo->$sname.'">';
			echo '</div>';
			echo '<div class="mue-user-edit-error">';
			
			echo '</div>';
			echo '</div>';
		}
	}


} 
echo '<div class="mue-user-edit-row">';
echo '<div class="mue-user-edit-label">';
echo '</div>';
echo '<div class="mue-user-edit-submit">';
echo '<input name="saveprofile" id="saveprofile" value="Save Profile" type="submit" class="button uk-button">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="user">';
echo '<input type="hidden" name="layout" value="saveuser">';
echo '<input type="hidden" name="jform[userGroupID]" value="'.$this->userinfo->userGroupID.'">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>