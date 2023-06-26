<?php
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_LOGIN_LOGIN_PAGE_TITLE').'</h2>';
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#loginform").validate({
			errorClass:"uf_error uk-form-danger",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });	
	    <?php if ($cecfg->show_loginreg) { ?>
	    jQuery("#regform").validate({
			errorClass:"uf_error uk-form-danger",
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
echo '<form action="" method="post" name="loginform" id="loginform" target="_top" class="uk-form uk-form-horizontal">';
echo '<div class="mue-login-login-row uk-form-row uk-margin-top">';
echo '<div class="mue-login-login-label uk-form-label uk-text-bold"></div>';
echo '<div class="mue-login-login-hdr uk-form-controls">';
echo JText::_('COM_MUE_LOGIN_LOGIN_INSTRUCTIONS');
echo '</div>';
echo '</div>';

//Username
echo '<div class="mue-login-login-row uk-form-row uk-margin-top">';
echo '<div class="mue-login-login-label uk-form-label uk-text-bold">'.JText::_('COM_MUE_LOGIN_LOGIN_USERNAME_LABEL').'</div>';
echo '<div class="mue-login-login-value uk-form-controls">';
echo '<input name="login_user" id="login_user" type="text" class="uf_login uk-width-1-1 uk-input" value=""';
echo ' data-rule-required="true" data-msg-required="Required">';
echo '</div>';
echo '<div class="mue-login-login-error"></div>';
echo '</div>';

//Password
echo '<div class="mue-login-login-row uk-form-row uk-margin-top">';
echo '<div class="mue-login-login-label uk-form-label uk-text-bold">'.JText::_('COM_MUE_LOGIN_LOGIN_PASSWORD_LABEL').'</div>';
echo '<div class="mue-login-login-value uk-form-controls">';
echo '<input name="login_pass" id="login_pass" type="password" class="uf_login uk-width-1-1 uk-input" value=""';
echo ' data-rule-required="true" data-msg-required="Required">';
echo '</div>';
echo '<div class="mue-login-login-error"></div>';
echo '</div>';

//Submit Button
echo '<div class="mue-login-login-row uk-form-row uk-margin-top">';
echo '<div class="mue-login-login-label uk-form-label uk-text-bold"></div>';
echo '<div class="mue-login-login-submit uk-form-controls">';
echo '<input type="submit" value="'.JText::_('COM_MUE_LOGIN_LOGIN_BUTTON_LOGIN').'" class="button uk-button" border="0" name="submit">';
echo '</div>';
echo '</div>';

//Lost Password/Register
echo '<div class="mue-login-login-row uk-form-row uk-margin-top">';
echo '<div class="mue-login-login-label uk-form-label uk-text-bold"></div>';
echo '<div class="mue-login-login-footer uk-form-controls">';

echo '<a href="'.JRoute::_('index.php?option=com_users&view=reset').'">'.JText::_('COM_MUE_LOGIN_RESET').'</a><br />';
echo '<a href="'.JRoute::_('index.php?option=com_users&view=remind').'">'.JText::_('COM_MUE_LOGIN_REMIND').'</a><br />';
$usersConfig = JComponentHelper::getParams('com_users');
if ($usersConfig->get('allowUserRegistration')) {
	echo '<a href="'.JRoute::_("index.php?option=com_mue&view=userreg&return=".base64_encode($this->redirurl)).'">'.JText::_('COM_MUE_LOGIN_LOGIN_LINK_REGISTER').'</a>';
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

echo '</div>';
?>
