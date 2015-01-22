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
			<button type="button" onclick="Joomla.submitform('brlist.apply', this.form);">
				<?php echo JText::_('JAPPLY');?></button>
			<button type="button" onclick="Joomla.submitform('brlist.save', this.form);">
				<?php echo JText::_('JSAVE');?></button>
			<button type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo 'Bronto Mail List Configuration - '.$this->list->list_info->name; ?>
		</div>
	</fieldset>

	<?php
	

	echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'brlist-membership'));
	echo JHtml::_('bootstrap.addTab', 'myTab', 'brlist-membership', "Subscription");

	

	//Membership Grouping
	echo '<h3>Subscripition Grouping</h3>';
	echo '<ul class="config-option-list">';
	
	//Sub Field
	echo '<li><label for="jform_brsubstatus" id="jform_mcsubstatus-lbl">Subscriber</label>';
	echo '<select name="jform[brsubstatus]" id="jform_brsubstatus" class="inputbox">';
	echo '<option value="">None</option>';
    echo JHtml::_('select.options', $this->list->list_tvars, 'id', 'name', $this->list->params->brsubstatus, true);
    echo '</select>';
	echo '</li>';

    //Sub Text Fields
    echo '<li><label for="jform_brsubtextyes" id="jform_brsubtextyes-lbl">Member Text</label>';
    echo '<input type="text" name="jform[brsubtextyes]" id="jform_brsubtextyes" class="inputbox" value="'.$this->list->params->brsubtextyes.'">';
    echo '</li>';
    echo '<li><label for="jform_brsubtextno" id="jform_brsubtextno-lbl">Non-Member Text</label>';
    echo '<input type="text" name="jform[brsubtextno]" id="jform_brsubtextno" class="inputbox" value="'.$this->list->params->brsubtextno.'">';
    echo '</li>';
	
	//Sub Since
	echo '<li><label for="jform_brsubsince" id="jform_brsubsince-lbl">Subscription Since</label>';
	echo '<select name="jform[brsubsince]" id="jform_brsubsince" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_datevars, 'id', 'name', $this->list->params->brsubsince, true);
	echo '</select>';
	echo '</li>';
	
	//Sub Expires
	echo '<li><label for="jform_brsubexp" id="jform_brsubexp-lbl">Subscription Expires</label>';
	echo '<select name="jform[brsubexp]" id="jform_brsubexp" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_datevars, 'id', 'name', $this->list->params->brsubexp, true);
	echo '</select>';
	echo '</li>';
	
	//Sub Pay Type 
	echo '<li><label for="jform_brsubpaytype" id="jform_brsubpaytype-lbl">Subscription Pay Type</label>';
	echo '<select name="jform[brsubpaytype]" id="jform_brsubpaytype" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_tvars, 'id', 'name', $this->list->params->brsubpaytype, true);
	echo '</select>';
	echo '</li>';

	echo '</ul>';
	echo '<div class="clr"></div>';

	//Fields
	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'myTab', 'brlist-fields', "Fields");

	
	echo '<table class="adminlist table table-striped">';
	echo '<thead><tr><th>Bronto Field</th><th>Type</th><th>MUE Field</th></tr></thead><tbody>';
	foreach ($this->list->list_mvars->iterate() as $v) {
        $fid = $v->id;
			echo '<tr><td>'.$v->name.'</td>';
			echo '<td>'.$v->type.'<input type="hidden" name="jform[brfieldtypes]['.$v->id.']" value="'.$v->type.'"></td><td>';
			if ($v->id != $this->list->params->mcrgroup && $v->id != $this->list->params->mcsubsince && $v->id != $this->list->params->mcsubexp && $v->id != $this->list->params->mcsubpaytype) {
				echo '<select name="jform[brvars]['.$v->id.']" id="jform_'.$v->id.'" class="inputbox">';
				echo '<option value="">None</option>';
				echo JHtml::_('select.options', $this->ufields, 'value', 'text', $this->list->params->brvars->$fid, true);
				echo '</select>';
			} else {
				echo 'Used for Subscription';
			}
			echo '</td></tr>';

	}
	echo '</tbody></table>';
	echo '<div class="clr"></div>';
	
	//Ops
	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'myTab', 'brlist-ops', "Operations");

	echo '<p><button type="button" onclick="Joomla.submitform(\'brlist.syncList\', this.form);">Sync Bronto Contacts</button> This will overwrite contact details, but will not change list assignments. This Process is very DB and API Intensive, use with care.</p>';
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
