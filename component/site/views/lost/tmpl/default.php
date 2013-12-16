<div id="system" class="uk-article">
<?php

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
echo '<h2 class="componentheading">Lost username/password</h2>';

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#loginform").validate({
			errorClass:"uf_error",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });	
	});
</script>
<?php 
//Login Description
if (($this->params->get('lostdescription_show') == 1 && str_replace(' ', '', $this->params->get('lost_description')) != '')) {
	echo '<div class="mue-lost-desc">';
	echo $this->params->get('lost_description');
	echo '</div>';
}

//Login Form
echo '<div id="mue-lost-lost">';
echo '<form action="" method="post" name="loginform" id="loginform">';
echo '<div class="mue-lost-lost-row">';
echo '<div class="mue-lost-lost-hdr">';
echo 'Please enter the email address for your account. Your username and a new password will be sent to you.';
echo '</div>';
echo '</div>';

//Email Address
echo '<div class="mue-lost-lost-row">';
echo '<div class="mue-lost-lost-label">Email</div>';
echo '<div class="mue-lost-lost-value">';
echo '<input name="lost_email" id="lost_email" type="text" class="uf_lost" value=""';
echo ' data-rule-required="true" data-msg-required="Required" data-rule-email="true" data-msg-email="Email address must be valid">';
echo '</div>';
echo '<div class="mue-lost-lost-error"></div>';
echo '</div>';

//Submit Button
echo '<div class="mue-lost-lost-row">';
echo '<div class="mue-lost-lost-label"></div>';
echo '<div class="mue-lost-lost-submit">';
echo '<input type="submit" value="Send Information" class="button uk-button" border="0" name="submit">';
echo '</div>';
echo '</div>';



echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="lost">';
echo '<input type="hidden" name="layout" value="sendinfo">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';

?>
</div>
