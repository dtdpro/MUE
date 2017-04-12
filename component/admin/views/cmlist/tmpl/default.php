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
	echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'cmlist-membership'));
	echo JHtml::_('bootstrap.addTab', 'myTab', 'cmlist-membership', "Subscription");
	
	//Membership Grouping
	echo '<div class="form-horizontal">';
	
	//Root Group
	echo '<div class="control-group"><div class="control-label"><label for="jform_msgroup_field" id="jform_msgroup_field-lbl">Subscription Field</label></div>';
	echo '<div class="controls"><select name="jform[msgroup][field]" id="jform_msgroup_field" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_msfields, 'Key', 'FieldName', $this->list->params->msgroup->field, true);
	echo '</select></div>';
	echo '</div>';

	//Non-Member Group
	echo '<div class="control-group"><div class="control-label"><label for="jform_msgroup_reg" id="jform_msgroup_reg-lbl">Non-Subscriber Option</label></div>';
	echo '<div class="controls"><select name="jform[msgroup][reg]" id="jform_msgroup_reg" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_msfields as $g) {
		echo '<option value="" disabled>'.$g->FieldName.'</option>';
		echo JHtml::_('select.options', $g->options, 'value', 'text', $this->list->params->msgroup->reg, true);
	}
	echo '</select></div>';
	echo '</div>';
	
	//Member Group
	echo '<div class="control-group"><div class="control-label"><label for="jform_msgroup_sub" id="jform_msgroup_sub-lbl">Subscriber Option</label></div>';
	echo '<div class="controls"><select name="jform[msgroup][sub]" id="jform_msgroup_sub" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_msfields as $g) {
		echo '<option value="" disabled>'.$g->FieldName.'</option>';
		echo JHtml::_('select.options', $g->options, 'value', 'text', $this->list->params->msgroup->sub, true);
	}
	echo '</select></div>';
	echo '</div>';

	// Sub Since
	echo '<div class="control-group"><div class="control-label"><label for="jform_sincedate" id="jform_sincedate-lbl">Subscription Since</label></div>';
	echo '<div class="controls"><select name="jform[sincedate]" id="jform_sincedate" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_datefields, 'Key', 'FieldName', $this->list->params->sincedate, true);
	echo '</select></div>';
	echo '</div>';

	// Sub Exp
	echo '<div class="control-group"><div class="control-label"><label for="jform_expdate" id="jform_expdate-lbl">Subscription Expires</label></div>';
	echo '<div class="controls"><select name="jform[expdate]" id="jform_expdate" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_datefields, 'Key', 'FieldName', $this->list->params->expdate, true);
	echo '</select></div>';
	echo '</div>';
	
	
	echo '</div>';
	echo '<div class="clr"></div>';

	//CM Fields
	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'myTab', 'cmlist-fields', "Fields");

	echo '<table class="adminlist table table-striped">';
	echo '<thead><tr><th>CM Field</th><th>Type</th><th>MUE Field</th></tr></thead><tbody>';
	foreach ($this->list->list_fields as $v) {
		if ($v->DataType != "Date") {
			$key = $v->Key;
			echo '<tr><td>' . $v->FieldName . '</td>';
			if ( $v->Key != $this->list->params->msgroup->field ) {
				echo '<td>' . $v->DataType . '<input type="hidden" name="jform[cmfieldtypes][' . $key . ']" value="' . $v->DataType . '"></td><td>';
			} else {
				echo '<td>&nbsp;</td><td>';
			}
			if ( $v->DataType == "Text" ) {
				echo '<select name="jform[cmfields][' . $key . ']" id="jform_' . $v->FieldName . '" class="inputbox">';
				echo '<option value="">None</option>';
				echo JHtml::_( 'select.options',$this->ufields->text,'value','text',$this->list->params->cmfields->$key,true );
				echo '</select>';
			}
			if ( $v->DataType == "MultiSelectOne" && $v->Key != $this->list->params->msgroup->field ) {
				echo '<select name="jform[cmfields][' . $key . ']" id="jform_' . $v->FieldName . '" class="inputbox">';
				echo '<option value="">None</option>';
				echo JHtml::_( 'select.options',$this->ufields->mso,'value','text',$this->list->params->cmfields->$key,true );
				echo '</select>';
			}
			if ( $v->DataType == "MultiSelectMany" ) {
				echo '<select name="jform[cmfields][' . $key . ']" id="jform_' . $v->FieldName . '" class="inputbox">';
				echo '<option value="">None</option>';
				echo JHtml::_( 'select.options',$this->ufields->msm,'value','text',$this->list->params->cmfields->$key,true );
				echo '</select>';
			}
			if ( $v->Key == $this->list->params->msgroup->field ) {
				echo 'Used for Membership Grouping';
			}
			echo '</td></tr>';
		}
	}
	echo '</tbody></table>';
	echo '<div class="clr"></div>';
	
	//Ops

	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'myTab', 'cmlist-ops', "Operations");

	echo '<p><button type="button" onclick="Joomla.submitform(\'cmlist.syncField\', this.form);">Sync MUE Field</button> This Process is very DB Intensive, use with care</p>';
	echo '<p><button type="button" onclick="Joomla.submitform(\'cmlist.syncList\', this.form);">Sync CM List</button> This Process is very DB Intensive, use with care. You should run Sync MUE Field First.</p>';
	echo '<div class="clr"></div>';
	
	//Webhooks
	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'myTab', 'cmlist-webhooks', "Webhooks");

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
	echo '<div class="clr"></div>';

	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.endTabSet');
	?>
	<div>
		<input type="hidden" name="field" value="<?php echo $this->list->uf_id;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
