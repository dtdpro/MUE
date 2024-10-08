<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$user=JFactory::getUser();
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
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USER_CHGEMAIL_PAGE_TITLE').'</h2>';
echo '<div id="mue-user-edit">';
echo '<form action="" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';
$ri=1;
if ($ri==1) $ri=0;
else $ri=1;

echo '<div class="uk-form-row uk-margin-top mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">Current Email</div>';
echo '<div class="uk-form-controls uk-form-controls-text mue-user-edit-value">';
echo $user->email;
echo '</div>';
echo '<div class="mue-user-edit-error">';
echo '</div>';
echo '</div>';
if ($ri==1) $ri=0;
else $ri=1;
echo '<div class="uk-form-row uk-margin-top mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">New Email</div>';
echo '<div class="uk-form-controls mue-user-edit-value">';

echo '<div class=""><input name="newemail" id="newemail" class="form-control uf_field input-sm" type="text"';
		
			echo ' data-rule-required="true"';
			echo ' data-rule-email="true"';
			echo ' data-msg-required="This Field is required"';
			echo ' data-msg-email="Email address must be valid"';
		
		echo '></div>';

echo '</div>';
echo '<div class="mue-user-edit-error">';
echo '</div>';
echo '</div>';
if ($ri==1) $ri=0;
else $ri=1;
echo '<div class="uk-form-row uk-margin-top mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">Confirm Email</div>';
echo '<div class="uk-form-controls mue-user-edit-value">';

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

echo '<div class="uk-form-row uk-margin-top uk-margin-bottom mue-user-edit-row">';
echo '<div class="uk-form-label mue-user-edit-label">';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-submit">';
echo '<input name="saveprofile" id="saveprofile" value="'.JText::_('COM_MUE_USER_CHGEMAIL_BUTTON_SAVE').'" type="submit" class="button uk-button uk-button-primary">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="user">';
echo '<input type="hidden" name="layout" value="saveemail">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
?>