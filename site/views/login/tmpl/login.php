<div id="system">
<?php

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
echo '<h2 class="componentheading">User Login</h2>';
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#loginform").validate({
			errorClass:"uf_error",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });	
	    <?php if ($cecfg->show_loginreg) { ?>
	    jQuery("#regform").validate({
			errorClass:"uf_error",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });	
	    <?php } ?>
	});
</script>
<?php 
echo '<div id="mue-login">';
//Login Description
if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '')) {
	echo '<div class="mue-login-desc">';
	echo $this->params->get('login_description');
	echo '</div>';
}

//Login Form
echo '<div id="mue-login-login">';
echo '<form action="" method="post" name="loginform" id="loginform" target="_top">';
echo '<div class="mue-login-login-row">';
echo '<div class="mue-login-login-label"></div>';
echo '<div class="mue-login-login-hdr">';
echo 'Login below';
echo '</div>';
echo '</div>';

//Username
echo '<div class="mue-login-login-row">';
echo '<div class="mue-login-login-label">Username</div>';
echo '<div class="mue-login-login-value">';
echo '<input name="login_user" id="login_user" type="text" class="uf_login" value=""';
echo ' data-rule-required="true" data-msg-required="Required">';
echo '</div>';
echo '<div class="mue-login-login-error"></div>';
echo '</div>';

//Password
echo '<div class="mue-login-login-row">';
echo '<div class="mue-login-login-label">Password</div>';
echo '<div class="mue-login-login-value">';
echo '<input name="login_pass" id="login_pass" type="password" class="uf_login" value=""';
echo ' data-rule-required="true" data-msg-required="Required">';
echo '</div>';
echo '<div class="mue-login-login-error"></div>';
echo '</div>';

//Submit Button
echo '<div class="mue-login-login-row">';
echo '<div class="mue-login-login-label"></div>';
echo '<div class="mue-login-login-submit">';
echo '<input type="submit" value="Login" class="button" border="0" name="submit">';
echo '</div>';
echo '</div>';

//Lost Password/Register
echo '<div class="mue-login-login-row">';
echo '<div class="mue-login-login-label"></div>';
echo '<div class="mue-login-login-footer">';
echo '<a href="'.JRoute::_("index.php?option=com_mue&view=lost").'">Lost Username/Password</a><br />';
$usersConfig = JComponentHelper::getParams('com_users');
if ($usersConfig->get('allowUserRegistration')) {
	echo '<a href="'.JRoute::_("index.php?option=com_mue&view=userreg&return=".base64_encode($this->redirurl)).'">Register/Subscribe</a>';
}
echo '</div>';
echo '</div>';

echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="login">';
echo '<input type="hidden" name="layout" value="logmein">';
echo '<input type="hidden" name="return" value="'.base64_encode($this->redirurl).'" />';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';





?>
</div>
