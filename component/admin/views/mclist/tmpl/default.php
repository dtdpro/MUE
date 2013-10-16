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
			<?php echo 'MailChimp List Configuration - '.$this->list->list_info['name']; ?>
		</div>
	</fieldset>

	<?php
	
	if (version_compare(JVERSION, '3.0.0', '>=')) {
		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'mclist-membership'));
		echo JHtml::_('bootstrap.addTab', 'myTab', 'mclist-membership', "Grouping");
	} else {
		echo JHtml::_('tabs.start', 'mclist_'.$this->list->uf_id.'_configuration', array('useCookie'=>1));
		echo JHtml::_('tabs.panel', "Grouping", 'mclist-membership');
	}
	

	//Membership Grouping
	echo '<h3>Subscripition Grouping</h3>';
	echo '<ul class="config-option-list">';
	
	//Root Group
	echo '<li><label for="jform_mcrgroup" id="jform_mcrgroup-lbl">Field</label>';
	echo '<select name="jform[mcrgroup]" id="jform_mcrgroup" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_msvars, 'tag', 'name', $this->list->params->mcrgroup, true);
	echo '</select>';
	echo '</li>';

	//Non-Member Group
	echo '<li><label for="jform_mcreggroup" id="jform_mcreggroup-lbl">Non-Subscriber</label>';
	echo '<select name="jform[mcreggroup]" id="jform_mcreggroup" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_msvars as $g) {
		echo '<option value="" disabled>'.$g->name.'</option>';
		echo JHtml::_('select.options', $g->options, 'value', 'text', $this->list->params->mcreggroup, true);
	}
	echo '</select>';
	echo '</li>';
	
	//Member Group
	echo '<li><label for="jform_mcsubgroup" id="jform_mcsubgroup-lbl">Subscriber</label>';
	echo '<select name="jform[mcsubgroup]" id="jform_mcsubgroup" class="inputbox">';
	echo '<option value="">None</option>';
	foreach ($this->list->list_msvars as $g) {
		echo '<option value="" disabled>'.$g->name.'</option>';
		echo JHtml::_('select.options', $g->options, 'value', 'text', $this->list->params->mcsubgroup, true);
	}
	echo '</select>';
	echo '</li>';
	
	echo '</ul>';
	echo '<div class="clr"></div>';
	if ($this->list->list_igroups) {
		echo '<h3>Interest Grouping</h3>';
		echo '<ul class="config-option-list">';

		//Interest Group
		echo '<li><label for="jform_mcigroup" id="jform_mcigroup-lbl">Interest Group</label>';
		echo '<select name="jform[mcigroup]" id="jform_mcigroup" class="inputbox">';
		echo '<option value="">None</option>';
		echo JHtml::_('select.options', $this->list->list_igroups, 'name', 'name', $this->list->params->mcigroup, true);
		echo '</select>';
		echo '</li>';
	
		//Interest Group Group
		echo '<li><label for="jform_mcigroups" id="jform_mcigroups-lbl">Groups</label>';
		if ($this->list->params->mcigroup) {
			foreach ($this->list->list_igroups as $g) {
				if ($g['name'] == $this->list->params->mcigroup) $igroup = $g;
			}
			echo '<select name="jform[mcigroups][]" id="jform_mcigroups" class="inputbox"';
			if ($igroup['form_field'] == "checkboxes") echo ' multiple="multiple" size="'.count($igroup['groups']).'"';
			echo '>';
			echo JHtml::_('select.options', (object)$igroup['groups'], 'name', 'name', $this->list->params->mcigroups, true);
			echo '</select>';
		} else {
			echo 'Set <strong>Interest Group</strong> first';
		}
		echo '</li></ul>';
		echo '<div class="clr"></div>';
	} else {
		echo '<input type="hidden" name="jform[mcigroup]" value="">';
		echo '<input type="hidden" name="jform[mcigroups][]" value="">';
	}
	//Merge Vars
	
	if (version_compare(JVERSION, '3.0.0', '>=')) {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'myTab', 'mclist-mergevars', "Merge Vars");
	} else {
		echo JHtml::_('tabs.panel', "Merge Vars", 'mclist-mergevars');
	}
	
	echo '<table class="adminlist table table-striped">';
	echo '<thead><tr><th>MC Var</th><th>Type</th><th>MUE Field</th></tr></thead><tbody>';
	foreach ($this->list->list_mvars as $v) {
		$tag=$v['tag'];
		if ($v['tag'] != "FNAME" && $v['tag'] != "LNAME" && $v['tag'] != "EMAIL") {
			echo '<tr><td>'.$v['name'].'</td>';
			echo '<td>'.$v['field_type'].'<input type="hidden" name="jform[mcfieldtypes]['.$v['tag'].']" value="'.$v['field_type'].'"></td><td>';
			if ($v['tag'] != $this->list->params->mcrgroup) {
				echo '<select name="jform[mcvars]['.$v['tag'].']" id="jform_'.$v['tag'].'" class="inputbox">';
				echo '<option value="">None</option>';
				echo JHtml::_('select.options', $this->ufields, 'value', 'text', $this->list->params->mcvars->$tag, true);
				echo '</select>';
			} else {
				echo 'Used for Membership Grouping';
			}
			echo '</td></tr>';
		}
	}
	echo '</tbody></table>';
	echo '<div class="clr"></div>';
	
	//Ops
	if (version_compare(JVERSION, '3.0.0', '>=')) {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'myTab', 'mclist-ops', "Operations");
	
	} else {
		echo JHtml::_('tabs.panel', "Operations", 'mclist-ops');
	}
	echo '<p><button type="button" onclick="Joomla.submitform(\'mclist.syncList\', this.form);">Sync MC List</button> This Process is very DB Intensive, use with care. You should run Sync MUE Field First.</p>';
	echo '<div class="clr"></div>';
	
	//Webhooks
	if (version_compare(JVERSION, '3.0.0', '>=')) {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'myTab', 'mclist-webhooks', "Webhooks");
	
	} else {
		echo JHtml::_('tabs.panel', "Webhooks", 'mclist-webhooks');
	}
	$webhook_url = str_replace("administrator/","",JURI::base()).'components/com_mue/helpers/mchook.php';
	$haswebhook=false;
	if ($this->list->list_webhooks) {
		echo '<p>';
		foreach ($this->list->list_webhooks as $h) {
			foreach ($h['actions'] as $act=>$on) { if ($on) $acts[] = $act; }
			foreach ($h['sources'] as $src=>$on) { if ($on) $sources[] = $src; }
			echo '<strong>'.$h['url'].'</strong><br />Actions: '.implode(", ",$acts).'<br />Sources: '.implode(", ",$sources).'<br /><br />';

			if ($webhook_url == $h['url']) $haswebhook=true;
		}
		echo '</p>';
	} 
	if (!$haswebhook) echo '<p><button type="button" onclick="Joomla.submitform(\'mclist.addWebhook\', this.form);">Add Default Web Hook</button> This will set up the default web hook for MUE.</p>';
	echo '<p><strong>Webhook URL: </strong>'.$webhook_url.'</p>';
		
	echo '<div class="clr"></div>';
	
	if (version_compare(JVERSION, '3.0.0', '>=')) {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.endTabSet');
	} else {
		echo JHtml::_('tabs.end');
	}
	
	?>
	<div>
		<input type="hidden" name="field" value="<?php echo $this->list->uf_id;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
