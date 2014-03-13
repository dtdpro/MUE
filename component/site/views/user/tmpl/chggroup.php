<div id="system" class="uk-article">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');

?>
<h2 class="componentheading uk-article-title">User Profile Edit</h2>

<?php 
echo '<div id="mue-user-edit">';
echo '<form action="" method="post" name="regform" id="regform">';
echo '<div class="mue-user-edit-row mue-rowh"><div class="mue-user-edit-label"></div><div class="mue-user-edit-hdr">Change User Group</div></div>';
if ($ri==1) $ri=0;
else $ri=1;
echo '<div class="mue-user-edit-row mue-row'.($ri % 2).'">';
echo '<div class="mue-user-edit-label">User Group</div>';
echo '<div class="mue-user-edit-value">';

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
echo '<div class="mue-user-edit-row">';
echo '<div class="mue-user-edit-label">';
echo '</div>';
echo '<div class="mue-user-edit-submit">';
echo '<input name="saveprofile" id="saveprofile" value="Save User Group" type="submit" class="button uk-button">';
echo '</div></div>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="user">';
echo '<input type="hidden" name="layout" value="savegroup">';
echo JHtml::_('form.token');
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';

?>
</div>