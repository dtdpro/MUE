<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$cfg = MUEHelper::getConfig();

?>
<script type="text/javascript">
	function submitGroup() {
		var form = jQuery("#regpickform" );
	    form.submit();
	}

</script>
<?php
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USERREG_PAGE_TITLE').'</h2>';
echo $cfg->REG_PAGE_CONTENT;
echo '<div id="mue-user-reg" class="uk-form uk-form-horizontal">';
$first = true;
echo '<div class="uk-form-row mue-user-reg-row mue-rowh">';
echo '<div class="uk-form-label mue-user-reg-label uk-text-bold">'.JText::_('COM_MUE_USERREG_LABEL_USER_GROUP').'</div>';
echo '<div class="uk-form-controls mue-user-reg-hdr">';
echo '<form action="'.JRoute::_("index.php?option=com_mue&view=userreg").'" method="post" name="regpickform" id="regpickform">';
echo '<select name="groupid" id="groupid" class="form-control required uf_field uf_select input-sm" onchange="submitGroup()">';
echo '<option value="">- Select Group -</option>';
foreach ($this->groups as $g) {
	echo '<option value="'.$g->ug_id.'">';
	echo $g->ug_name.'</option>'; 	
}
echo '</select>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="userreg">';
echo '<input type="hidden" name="layout" value="groupuser">';
echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
echo JHtml::_('form.token');
echo '</form>';
echo '</div>';
echo '</div>';
echo '<div style="clear:both;"></div>';
echo '<div id="mue-user-regform"></div>';
echo '</div>';
//echo '<div id="mue-userreg-form"></div>';
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>
