<?php

// No direct access
defined('_JEXEC') or die;

$template = JFactory::getApplication()->getTemplate();

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.id('component-form'))) {
			Joomla.submitform(task, document.getElementById('component-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue');?>" id="mue-form" method="post" name="adminForm" class="form-validate">
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('mclist.apply', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="Joomla.submitform('mclist.save', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo 'MailChimp List Configuration - '.$this->list->list_info->name; ?>
		</div>
	</fieldset>

	<?php
	echo JHtml::_('tabs.start', 'mclist_'.$this->list->uf_id.'_configuration', array('useCookie'=>1));
	echo JHtml::_('tabs.panel', "Membership Grouping", 'mclist-membership');
	//Membership Grouping
	echo '<ul class="config-option-list">';
	
	//Root Group
	echo '<li><label for="jform_mcrgroup" id="jform_mcrgroup-lbl">Root Group</label>';
	echo '<select name="jform[mcrgroup]" id="jform_mcrgroup" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_igroups, 'name', 'name', $this->list->params->mcrgroup, true);
	echo '</select>';
	echo '</li>';

	//Non-Member Group
	echo '<li><label for="jform_mcreggroup" id="jform_mcreggroup-lbl">Non-Subscriber Group</label>';
	echo '<select name="jform[mcreggroup]" id="jform_mcreggroup" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_igroups as $g) {
		echo '<option value="" disabled>'.$g->name.'</option>';
		echo JHtml::_('select.options', $g->groups, 'name', 'name', $this->list->params->mcreggroup, true);
	}
	echo '</select>';
	echo '</li>';
	
	//Member Group
	echo '<li><label for="jform_mcsubgroup" id="jform_mcsubgroup-lbl">Subscriber Group</label>';
	echo '<select name="jform[mcsubgroup]" id="jform_mcsubgroup" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_igroups as $g) {
		echo '<option value="" disabled>'.$g->name.'</option>';
		echo JHtml::_('select.options', $g->groups, 'name', 'name', $this->list->params->mcsubgroup, true);
	}
	echo '</select>';
	echo '</li>';
	
	
	echo '</ul>';
	echo '<div class="clr"></div>';

	echo JHtml::_('tabs.panel', "Merge Vars", 'mclist-membership');
	//Merge Vars
	echo '<ul class="config-option-list">';
	foreach ($this->list->list_mvars as $v) {
		$tag=$v->tag;
		if ($v->tag != "FNAME" && $v->tag != "LNAME" && $v->tag != "EMAIL") {
			echo '<li><label for="jform_'.$v->tag.'" id="jform_'.$v->tag.'-lbl">'.$v->name.'</label>';
			echo '<select name="jform[mcvars]['.$v->tag.']" id="jform_'.$v->tag.'" class="inputbox">';
			echo '<option value="">None</option>';
			echo JHtml::_('select.options', $this->ufields, 'value', 'text', $this->list->params->mcvars->$tag, true);
			echo '</select>';
			echo '</li>';
		}
	}
	echo '</ul>';
	echo '<div class="clr"></div>';
	
	//Ops
	echo JHtml::_('tabs.panel', "Operations", 'mclist-membership');
	echo '<p><button type="button" onclick="Joomla.submitform(\'mclist.syncField\', this.form);">Sync MUE FIeld</button> This Process is very DB Intensive, use with care</p>';
	echo '<div class="clr"></div>';
	
	echo JHtml::_('tabs.end');
	?>
	<div>
		<input type="hidden" name="field" value="<?php echo $this->list->uf_id;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>