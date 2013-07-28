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
			<button type="button" onclick="Joomla.submitform('cmlist.apply', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="Joomla.submitform('cmlist.save', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo 'Campaign Monitor List Configuration - '.$this->list->list_info->Title; ?>
		</div>
	</fieldset>

	<?php
	echo JHtml::_('tabs.start', 'cmlist_'.$this->list->uf_id.'_configuration', array('useCookie'=>1));
	echo JHtml::_('tabs.panel', "Membership Grouping", 'cmlist-membership');
	
	//Membership Grouping
	echo '<ul class="config-option-list">';
	
	//Root Group
	echo '<li><label for="jform_msgroup_field" id="jform_msgroup_field-lbl">Subscription Field</label>';
	echo '<select name="jform[msgroup][field]" id="jform_msgroup_field" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_msfields, 'Key', 'FieldName', $this->list->params->msgroup->field, true);
	echo '</select>';
	echo '</li>';

	//Non-Member Group
	echo '<li><label for="jform_msgroup_reg" id="jform_msgroup_reg-lbl">Non-Subscriber Option</label>';
	echo '<select name="jform[msgroup][reg]" id="jform_msgroup_reg" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_msfields as $g) {
		echo '<option value="" disabled>'.$g->FieldName.'</option>';
		echo JHtml::_('select.options', $g->options, 'value', 'text', $this->list->params->msgroup->reg, true);
	}
	echo '</select>';
	echo '</li>';
	
	//Member Group
	echo '<li><label for="jform_msgroup_sub" id="jform_msgroup_sub-lbl">Subscriber Option</label>';
	echo '<select name="jform[msgroup][sub]" id="jform_msgroup_sub" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_msfields as $g) {
		echo '<option value="" disabled>'.$g->FieldName.'</option>';
		echo JHtml::_('select.options', $g->options, 'value', 'text', $this->list->params->msgroup->sub, true);
	}
	echo '</select>';
	echo '</li>';
	
	
	echo '</ul>';
	echo '<div class="clr"></div>';

	echo JHtml::_('tabs.panel', "Fields", 'cmlist-membership');
	//Merge Vars
	echo '<ul class="config-option-list">';
	foreach ($this->list->list_fields as $v) {
		$key=$v->Key;
		if ($v->DataType == "Text") {
			echo '<li><label for="jform_'.$key.'" id="jform_'.$key.'-lbl">'.$v->FieldName.'</label>';
			echo '<select name="jform[cmfields]['.$key.']" id="jform_'.$v->FieldName.'" class="inputbox">';
			echo '<option value="">None</option>';
			echo JHtml::_('select.options', $this->ufields, 'value', 'text', $this->list->params->cmfields->$key, true);
			echo '</select>';
			echo '</li>';
		}
	}
	echo '</ul>';
	echo '<div class="clr"></div>';
	
	//Ops
	echo JHtml::_('tabs.panel', "Operations", 'cmlist-membership');
	echo '<p><button type="button" onclick="Joomla.submitform(\'cmlist.syncField\', this.form);">Sync MUE Field</button> This Process is very DB Intensive, use with care</p>';
	echo '<p><button type="button" onclick="Joomla.submitform(\'cmlist.syncList\', this.form);">Sync MC List</button> This Process is very DB Intensive, use with care. You should run Sync MUE Field First.</p>';
	echo '<div class="clr"></div>';
	
	//Webhooks
	echo JHtml::_('tabs.panel', "Webhooks", 'cmlist-membership');
	if ($this->list->list_webhooks) {
		echo '<p>';
		foreach ($this->list->list_webhooks as $h) {
			foreach ($h->Events as $event) { $events[] = $event; }
			echo '<strong>'.$h->Url.'</strong><br />ID: '.$h->WebhookID.'<br />Status: '.$h->Status.' Format: '.$h->PayloadFormat.'<br />Events: '.implode(", ",$events).'<br /><br />';
			
		}

		
		echo '</p>';
	} else {
		echo '<p><button type="button" onclick="Joomla.submitform(\'cmlist.addWebhook\', this.form);">Add Default Web Hook</button> This will set up the default web hook for MUE.</p>';
		echo '<p><strong>Use Webhook URL: </strong>'.str_replace("administrator/","",JURI::base()).'components/com_mue/helpers/cmhook.php</p>';
	}
	echo '<pre>';
	print_r($this->list);
	echo '</pre>';
	echo '<div class="clr"></div>';
	
	echo JHtml::_('tabs.end');
	?>
	<div>
		<input type="hidden" name="field" value="<?php echo $this->list->uf_id;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
