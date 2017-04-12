<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
$params = $this->form->getFieldsets('params');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'uplan.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
            Joomla.submitform(task, document.getElementById('mue-form'));
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&sub_id='.(int) $this->item->sub_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
    <div class="row-fluid">
        <div class="span4 form-horizontal">
            <h4><?php echo JText::_( 'COM_MUE_UPLAN_DETAILS' ); ?></h4>
			<?php foreach($this->form->getFieldset('details') as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label;?></div>
                    <div class="controls"><?php echo $field->input;?></div>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="span8 form-horizontal">
            <h4><?php echo JText::_( 'COM_MUE_UPLAN_CONTENT' ); ?></h4>
	        <?php foreach($this->form->getFieldset('content') as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label;?></div>
                    <div class="controls"><?php echo $field->input;?></div>
                </div>
	        <?php endforeach; ?>
        </div>
    </div>

    <div>
        <input type="hidden" name="task" value="uplan.edit" />
		<?php echo JHtml::_('form.token'); ?>
    </div>
</form>
