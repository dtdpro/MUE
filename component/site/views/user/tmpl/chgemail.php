<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$user=JFactory::getUser();
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#regform").validate({
			errorClass:"uf_error",
			validClass:"uf_valid",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").parent("div").next("div") );
		    }
	    });

	});


</script>
<h2 class="componentheading uk-article-title">Change Email Address</h2>

<?php 
echo '<div id="mue-user-edit">';
echo '<form action="" method="post" name="regform" id="regform">';
echo '<div class="mue-user-edit-row mue-rowh"><div class="mue-user-edit-label"></div><div class="mue-user-edit-hdr">Change Email</div></div>';
if ($ri==1) $ri=0;
else $ri=1;

echo '<div class="mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="mue-user-edit-label">Current Email</div>';
echo '<div class="mue-user-edit-value">';
echo $user->email;
echo '</div>';
echo '<div class="mue-user-edit-error">';
echo '</div>';
echo '</div>';
if ($ri==1) $ri=0;
else $ri=1;
echo '<div class="mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="mue-user-edit-label">New Email</div>';
echo '<div class="mue-user-edit-value">';

echo '<div class=""><input name="newemail" id="newemail" class="form-control uf_field input-sm" type="text"';
		
			echo ' data-rule-required="true"';
			echo ' data-rule-email="true"';
			echo ' data-rule-remote="'.JURI::base( true ).'/components/com_mue/helpers/chkemail.php"';
			echo ' data-msg-required="This Field is required"';
			echo ' data-msg-email="Email address must be valid"';
			echo ' data-msg-remote="Email Already registered"';
		
		echo '></div>';

echo '</div>';
echo '<div class="mue-user-edit-error">';
echo '</div>';
echo '</div>';
if ($ri==1) $ri=0;
else $ri=1;
echo '<div class="mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="mue-user-edit-label">Confirm Email</div>';
echo '<div class="mue-user-edit-value">';

echo '<div class=""><input name="newemailc" id="newemailc" class="form-control uf_field input-sm" type="text"';

	echo ' data-rule-required="true"';
	echo ' data-rule-equalTo="#newemail"';
	echo ' data-msg-required="This Field is required"';
	echo ' data-msg-equalTo="Fields must match"';

echo '></div>';

echo '</div>';
echo '<div class="mue-user-edit-error">';
echo '</div>';
echo '</div>';

echo '<div class="mue-user-edit-row">';
echo '<div class="mue-user-edit-label">';
echo '</div>';
echo '<div class="mue-user-edit-submit">';
echo '<input name="saveprofile" id="saveprofile" value="Save Changed Email" type="submit" class="button uk-button">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="user">';
echo '<input type="hidden" name="layout" value="saveemail">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>