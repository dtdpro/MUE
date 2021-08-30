<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$config = MUEHelper::getConfig();
	?>
<h2 class="componentheading uk-article-title">Create Message</h2>
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

echo '<div id="mue-user-edit">';
echo '<form action="'.JRoute::_("index.php?option=com_mue&view=pm&layout=savemessage&mid=".$this->message->msg_id).'" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';

$subjectField = new stdClass();

echo '<div class="uk-form-row mue-user-edit-row mue-row0">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">';
echo 'To';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-value uk-form-controls-text">';
echo $this->message->name;
echo '</div>';
echo '</div>';

echo '<div class="uk-form-row mue-user-edit-row mue-row1">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">';
echo 'Subject';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-value">';
echo '<input name="msg_subject" id="msg_subject" value="';
if ($this->message->msg_subject) echo $this->message->msg_subject;
echo '" class="form-control uf_field input-sm uk-width-1-1" type="text" data-rule-required="true" data-msg-required="This Field is required">';
echo '</div>';
echo '</div>';


echo '<div class="uk-form-row mue-user-edit-row mue-row0">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">';
echo 'Message';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-value">';
echo '<textarea name="msg_body" id="msg_body" cols="70" rows="4" class="form-control uf_field input-sm uk-width-1-1" data-rule-required="true" data-msg-required="This Field is required"></textarea>';
echo '</div>';
echo '</div>';

echo '<div class="uk-form-row mue-user-edit-row">';
echo '<div class="uk-form-label mue-user-edit-label">';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-submit">';
echo '<input name="sendmessage" id="sendmessage" value="Send Message" type="submit" class="button uk-button">';
echo '</div></div>';


echo JHtml::_('form.token');
echo '</form>';

echo '</div>';

if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>