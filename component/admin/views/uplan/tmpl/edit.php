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
		if (task == 'uplan.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
			Joomla.submitform(task, document.getElementById('mue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&sub_id='.(int) $this->item->sub_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
	<div class="width-30 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UPLAN_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
	<div class="width-70 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UPLAN_CONTENT' ); ?></legend>
<?php foreach($this->form->getFieldset('content') as $field): ?>
				<?php echo '<div>'.$field->label.'<div class="clr"></div>'.$field->input.'</div>';?>
				<div class="clr"></div>
<?php endforeach; ?>
		</fieldset>
	</div>
	<div>
		<input type="hidden" name="task" value="uplan.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

