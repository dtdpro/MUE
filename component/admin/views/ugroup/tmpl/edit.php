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
        if (task == 'ugroup.cancel' || document.formvalidator.isValid(document.getElementById('mue-form'))) {
            Joomla.submitform(task, document.getElementById('mue-form'));
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&ug_id='.(int) $this->item->ug_id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">

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
        <div class="width-60 fltlft span8 col-md-8">
            <fieldset class="options-form form-vertical">
                <legend><?php echo JText::_( 'COM_MUE_UGROUP_DETAILS' ); ?></legend>
                    <?php foreach($this->form->getFieldset('details') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
            </fieldset>
        </div>
        <div class="width-40 fltlft span4 col-md-4">
            <fieldset class="options-form form-horizontal">
                <legend><?php echo JText::_( 'COM_MUE_UGROUP_SETTINGS' ); ?></legend>
                    <?php foreach($this->form->getFieldset('settings') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
            </fieldset>
            <fieldset class="options-form form-horizontal">
                <legend><?php echo JText::_( 'COM_MUE_UGROUP_EMAILTAGS' ); ?></legend>
                <p>
                    <strong>{username}</strong> - Username<br>
                    <strong>{fullname}</strong> - Users Full Name<br>
                    <strong>{site_url}</strong> - Site user signed up at<br>
                    <strong>{actlink}</strong> - Activation Link
                </p>
            </fieldset>
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

    <input type="hidden" name="task" value="ugroup.edit" />
    <?php echo JHtml::_('form.token'); ?>

</form>

