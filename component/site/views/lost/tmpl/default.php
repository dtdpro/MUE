<?php
defined('_JEXEC') or die;
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
JHtml::_('behavior.keepalive');
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_LOST_PAGE_TITLE').'</h2>';

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#loginform").validate({
			errorClass:"uf_error uk-form-danger",
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
echo '<form action="" method="post" name="loginform" id="loginform" class="uk-form uk-form-horizontal">';
echo '<div class="mue-lost-lost-row" uk-form-row uk-margin-top>';
echo '<div class="mue-lost-lost-hdr uk-form-controls">';
echo JText::_('COM_MUE_LOST_INSTRUCTIONS');
echo '</div>';
echo '</div>';

//Email Address
echo '<div class="mue-lost-lost-row uk-form-row uk-margin-top">';
echo '<div class="mue-lost-lost-label uk-form-label uk-text-bold">'.JText::_('COM_MUE_LOST_LABEL_EMAIL').'</div>';
echo '<div class="mue-lost-lost-value uk-form-controls">';
echo '<input name="lost_email" id="lost_email" type="text" class="uf_lost uk-width-1-1 uk-input" value=""';
echo ' data-rule-required="true" data-msg-required="Required" data-rule-email="true" data-msg-email="'.JText::_('COM_MUE_LOST_VALID_EMAIL').'">';
echo '</div>';
echo '<div class="mue-lost-lost-error"></div>';
echo '</div>';

//Submit Button
echo '<div class="mue-lost-lost-row uk-form-row uk-margin-top">';
echo '<div class="mue-lost-lost-label uk-form-label uk-text-bold"></div>';
echo '<div class="mue-lost-lost-submit uk-form-controls">';
echo '<input type="submit" value="'.JText::_('COM_MUE_LOST_BUTTON_SUBMIT').'" class="button uk-button" border="0" name="submit">';
echo '</div>';
echo '</div>';



echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="lost">';
echo '<input type="hidden" name="layout" value="sendinfo">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>
