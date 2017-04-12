<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
?>

<?php
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USER_CHGGROUP_PAGE_TITLE').'</h2>';
echo '<div id="mue-user-edit">';
echo '<form action="" method="post" name="regform" id="regform" class="uk-form uk-form-horizontal">';
if ($ri==1) $ri=0;
else $ri=1;
echo '<div class="uk-form-row mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="uk-form-label mue-user-edit-label uk-text-bold">'.JText::_('COM_MUE_USER_CHGGROUP_LABEL_USER_GROUP').'</div>';
echo '<div class="uk-form-controls mue-user-edit-value">';

$first=true;
foreach ($this->groups as $o) {
	$checked = ($o->ug_id == $this->currentgroup) ? ' checked="checked"' : '';
	echo '<div class="radio"><input type="radio" name="newgroup" value="'.$o->ug_id.'" id="jform_'.$sname.$o->ug_id.'" class="uf_radio input-sm"';
	echo $checked.'/>'."\n";
	echo '<label for="jform_'.$sname.$o->ug_id.'">';
	echo ' '.$o->ug_name.'</label></div>'."\n";
	
}
echo '</div>';
echo '<div class="mue-user-edit-error">';
echo '</div>';
echo '</div>';
echo '<div class="uk-form-row mue-user-edit-row">';
echo '<div class="uk-form-label mue-user-edit-label">';
echo '</div>';
echo '<div class="uk-form-controls mue-user-edit-submit">';
echo '<input name="saveprofile" id="saveprofile" value="'.JText::_('COM_MUE_USER_CHGGROUP_BUTTON_SAVE').'" type="submit" class="button uk-button">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="user">';
echo '<input type="hidden" name="layout" value="savegroup">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>