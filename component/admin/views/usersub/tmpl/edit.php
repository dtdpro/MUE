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
		if (task == 'usersub.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
			Joomla.submitform(task, document.getElementById('mue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&usrsub_id='.(int) $this->item->usrsub_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_USERSUB_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
	<?php if ($this->item->usrsub_id) { ?>
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_USERSUB_HISTORY' ); ?></legend>
			
<?php 

foreach($this->history as $h) { 
	echo $h->usl_time.'<br /><br />'.nl2br(htmlentities($h->usl_resarray));
	echo '<hr size="1">';
} ?>
			
		</fieldset>
	</div>
	<?php } ?>
	<div>
		<input type="hidden" name="task" value="usersub.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

