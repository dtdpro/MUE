<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'ufield.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
			Joomla.submitform(task, document.getElementById('mue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&uf_id='.(int) $this->item->uf_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
<div class="row-fluid">
	<div class="width-30 fltlft span8">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UFIELD_DETAILS' ); ?></legend>
			<ul class="adminformlist treeselect">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
	<div class="width-70 fltlft span4">
		<fieldset id="jform_fieldgroups" class="adminform">
			<legend>Field Groups</legend>
			<ul class="checklist treeselect">
			<?php 
			foreach ($this->gtypes as $ct) {
				if (!empty($this->item->fieldgroups)) $checked = in_array($ct->ug_id,$this->item->fieldgroups) ? ' checked="checked"' : '';
				else $checked = '';
				?>
				<li class="pull-left row-fluid"><input type="checkbox" name="jform[fieldgroups][]" value="<?php echo (int) $ct->ug_id;?>" id="jform_fieldgroup<?php echo (int) $ct->ug_id;?>"<?php echo $checked;?> class="pull-left muegroup"/>
				<label for="jform_fieldgroup<?php echo $ct->ug_id; ?>" class="pull-left">
				<?php echo ' '.$ct->ug_name; ?></label></li>
				<?php 
			}
			?>
			</ul>
			<div class="clr clearfix"></div>
			<button type="button" class="jform-assignments-butto" onclick="$$('.muegroup').each(function(el) { el.checked = false; });">Clear Selection</button>
			<button type="button" class="jform-assignments-button" onclick="$$('.muegroup').each(function(el) { el.checked = true; });">Select All</button>
			</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UFIELD_CONTENT' ); ?></legend>
<?php foreach($this->form->getFieldset('content') as $field): ?>
				<?php echo '<div>'.$field->label.'<div class="clr"></div>'.$field->input.'</div>';?>
				<div class="clr"></div>
<?php endforeach; ?>
		</fieldset>
	</div>

		<input type="hidden" name="task" value="ufield.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

