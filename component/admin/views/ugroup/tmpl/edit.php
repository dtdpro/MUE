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
		if (task == 'ugroup.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
			Joomla.submitform(task, document.getElementById('mue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&ug_id='.(int) $this->item->ug_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
<div class="row-fluid">
	<div class="width-60 fltlft span8">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UGROUP_DETAILS' ); ?></legend>
			<ul class="adminformlist treeselect">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
	<div class="width-40 fltlft span4">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_UGROUP_SETTINGS' ); ?></legend>
<?php foreach($this->form->getFieldset('settings') as $field): ?>
				<?php echo '<div>'.$field->label.'<div class="clr"></div>'.$field->input.'</div>';?>
				<div class="clr"></div>
<?php endforeach; ?>
		</fieldset>
        <fieldset class="adminform">
            <legend><?php echo JText::_( 'COM_MUE_UGROUP_EMAILTAGS' ); ?></legend>
			<p>
                <strong>{username}</strong> - Username<br>
                <strong>{fullname}</strong> - Users Full Name<br>
                <strong>{site_url}</strong> - Site user signed up at
            </p>
        </fieldset>
	</div>
		<input type="hidden" name="task" value="ugroup.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

