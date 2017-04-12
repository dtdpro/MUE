

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
        if (task == 'ufield.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
            Joomla.submitform(task, document.getElementById('mue-form'));
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&uf_id='.(int) $this->item->uf_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
    <div class="row-fluid">
        <div class="span4 form-horizontal">
            <h4><?php echo JText::_( 'COM_MUE_UFIELD_DETAILS' ); ?></h4>
			<?php foreach($this->form->getFieldset('details') as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label;?></div>
                    <div class="controls"><?php echo $field->input;?></div>
                </div>
			<?php endforeach; ?>
        </div>
        <div class="span8 form-horizontal">
            <fieldset id="jform_fieldgroups" class="adminform">
                <legend>Field Groups</legend>

			        <?php
			        foreach ($this->gtypes as $ct) {
				        if (!empty($this->item->fieldgroups)) $checked = in_array($ct->ug_id,$this->item->fieldgroups) ? ' checked="checked"' : '';
				        else $checked = '';
				        ?>
                        <div class="control-group">
                            <div class="control-label"><label for="jform_fieldgroup<?php echo $ct->ug_id; ?>" class="pull-left"><?php echo ' '.$ct->ug_name; ?></label></div>
                            <div class="controls"><input type="checkbox" name="jform[fieldgroups][]" value="<?php echo (int) $ct->ug_id;?>" id="jform_fieldgroup<?php echo (int) $ct->ug_id;?>"<?php echo $checked;?> class="pull-left muegroup"/></div>
                        </div>
				        <?php
			        }
			        ?>
                <div class="clr clearfix"></div>
                <button type="button" class="jform-assignments-button" onclick="$$('.muegroup').each(function(el) { el.checked = false; });">Clear Selection</button>
                <button type="button" class="jform-assignments-button" onclick="$$('.muegroup').each(function(el) { el.checked = true; });">Select All</button>
            </fieldset>
            <h4><?php echo JText::_( 'COM_MUE_UFIELD_CONTENT' ); ?></h4>
	        <?php foreach($this->form->getFieldset('content') as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label;?></div>
                    <div class="controls"><?php echo $field->input;?></div>
                </div>
	        <?php endforeach; ?>
        </div>
    </div>

    <div>
        <input type="hidden" name="task" value="ufield.edit" />
		<?php echo JHtml::_('form.token'); ?>
    </div>
</form>


