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
	<div class="width-30 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UFIELD_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			<li><label id="jform_fieldgroups-lbl" for="jform_fieldgroups" class="hasTip" title="">Field Groups</label>
			<fieldset id="jform_fieldgroups" class="radio inputbox">
			<?php 
			foreach ($this->gtypes as $ct) {
				if (!empty($this->item->fieldgroups)) $checked = in_array($ct->ug_id,$this->item->fieldgroups) ? ' checked="checked"' : '';
				else $checked = '';
				?>
				<input type="checkbox" name="jform[fieldgroups][]" value="<?php echo (int) $ct->ug_id;?>" id="jform_fieldgroup<?php echo (int) $ct->ug_id;?>"<?php echo $checked;?>/>
				<label for="jform_fieldgroup<?php echo $ct->ug_id; ?>">
				<?php echo ' '.$ct->ug_name; ?></label><br /><br />
				<?php 
			}
			?></fieldset></li>
			</ul>
		</fieldset>
	</div>
	<div class="width-70 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UFIELD_CONTENT' ); ?></legend>
<?php foreach($this->form->getFieldset('content') as $field): ?>
				<?php echo '<div>'.$field->label.'<div class="clr"></div>'.$field->input.'</div>';?>
				<div class="clr"></div>
<?php endforeach; ?>
		</fieldset>
	</div>
	<div>
		<input type="hidden" name="task" value="ufield.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

