<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
if (JVersion::MAJOR_VERSION == 3) JHtml::_('formbehavior.chosen', 'select');


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$params = $this->form->getFieldsets('params');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'ufield.cancel' || document.formvalidator.isValid(document.getElementById('mue-form'))) {
            Joomla.submitform(task, document.getElementById('mue-form'));
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&uf_id='.(int) $this->item->uf_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">

	<?php
	if (JVersion::MAJOR_VERSION >= 4) {
		echo '<div class="form-horizontal main-card">';
		echo HTMLHelper::_('uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
		echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'Details');
	} else {
		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general'));
		echo JHtml::_('bootstrap.addTab', 'myTab', 'general', 'Details');
	}
	?>

    <div class="row-fluid row">
        <div class="span4 form-horizontal col-md-4">
            <fieldset id="jform_fieldgroups" class="options-form">
                <legend><?php echo JText::_( 'COM_MUE_UFIELD_DETAILS' ); ?></legend>
                <?php foreach($this->form->getFieldset('details') as $field): ?>
                    <div class="control-group">
                        <div class="control-label"><?php echo $field->label;?></div>
                        <div class="controls"><?php echo $field->input;?></div>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        </div>
        <div class="span8 col-md-8">
            <div class="form-horizontal">
                <fieldset id="jform_fieldgroups" class="options-form">
                    <legend>Field Groups</legend>
                    <div>

                        <?php
                        foreach ($this->gtypes as $ct) {
                            if (!empty($this->item->fieldgroups)) $checked = in_array($ct->ug_id,$this->item->fieldgroups) ? ' checked="checked"' : '';
                            else $checked = '';
                            ?>
                            <div class="control-group">
                                <div class="controls">
                                    <label class="form-check-label checkbox" for="jform_fieldgroup<?php echo $ct->ug_id; ?>">
                                        <input class="form-check-input" type="checkbox" name="jform[fieldgroups][]" value="<?php echo (int) $ct->ug_id;?>" id="jform_fieldgroup<?php echo (int) $ct->ug_id;?>"<?php echo $checked;?> rel="group_1_1">
                                        <span class="text-muted"></span>–&nbsp;<?php echo $ct->ug_name; ?>
                                    </label>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </fieldset>
            </div>
            <div class="form-vertical">
                <fieldset id="jform_fieldgroups" class="options-form">
                    <legend><?php echo JText::_( 'COM_MUE_UFIELD_CONTENT' ); ?></legend>
                    <?php foreach($this->form->getFieldset('content') as $field): ?>
                        <div class="control-group">
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            </div>
        </div>
    </div>

	<?php
	if ( JVersion::MAJOR_VERSION >= 4 ) {
		echo HTMLHelper::_('uitab.endTab');
		echo HTMLHelper::_( 'uitab.endTabSet' );
		echo '</div>';
	} else {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_( 'bootstrap.endTabSet' );
	}
	?>

    <input type="hidden" name="task" value="ufield.edit" />
    <?php echo JHtml::_('form.token'); ?>

</form>


