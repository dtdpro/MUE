<?php
// No direct access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'aclist.cancel' || document.formvalidator.isValid(document.getElementById('mue-form'))) {
			Joomla.submitform(task, document.getElementById('mue-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue');?>" id="mue-form" method="post" name="adminForm" class="form-validate">
	<?php
	if (JVersion::MAJOR_VERSION == 4) {
		echo '<div class="form-horizontal main-card">';
		echo HTMLHelper::_('uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
		echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'Subscription');
	} else {
		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general'));
		echo JHtml::_('bootstrap.addTab', 'myTab', 'general', 'Subscription');
	}

	//Membership Grouping
	echo '<h3>Subscripition Grouping</h3>';
    echo '<p>Active Camapign List Configuration<br><em>Only the first AC list field will have fields synced</em></p>';
	echo '<div class="form-horizontal">';
	
	//Sub Field
	echo '<div class="control-group"><div class="control-label"><label for="jform_acsubstatus" id="jform_acsubstatus-lbl">Subscriber Status</label></div>';
	echo '<div class="controls"><select name="jform[acsubstatus]" id="jform_acsubstatus" class="inputbox">';
	echo '<option value="">None</option>';
    echo JHtml::_('select.options', $this->list->list_textvars, null,null, $this->list->params->acsubstatus, true);
    echo '</select></div>';
	echo '</div>';

    //Sub Text Fields
    echo '<div class="control-group"><div class="control-label"><label for="jform_acsubtextyes" id="jform_acsubtextyes-lbl">Member Text</label></div>';
    echo '<div class="controls"><input type="text" name="jform[acsubtextyes]" id="jform_acsubtextyes" class="inputbox" value="'.$this->list->params->acsubtextyes.'"></div>';
    echo '</div>';

    echo '<div class="control-group"><div class="control-label"><label for="jform_acsubtextno" id="jform_acsubtextno-lbl">Non-Member Text</label></div>';
    echo '<div class="controls"><input type="text" name="jform[acsubtextno]" id="jform_acsubtextno" class="inputbox" value="'.$this->list->params->acsubtextno.'"></div>';
    echo '</div>';
	
	//Sub Since
	echo '<div class="control-group"><div class="control-label"><label for="jform_acsubsince" id="jform_acsubsince-lbl">Subscription Since</label></div>';
	echo '<div class="controls"><select name="jform[acsubsince]" id="jform_acsubsince" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_datevars, null,null, $this->list->params->acsubsince, true);
	echo '</select></div>';
	echo '</div>';
	
	//Sub Expires
	echo '<div class="control-group"><div class="control-label"><label for="jform_acsubexp" id="jform_acsubexp-lbl">Subscription Expires</label></div>';
	echo '<div class="controls"><select name="jform[acsubexp]" id="jform_acsubexp" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_datevars, null,null, $this->list->params->acsubexp, true);
	echo '</select></div>';
	echo '</div>';
	
	//Sub Pay Type 
	echo '<div class="control-group"><div class="control-label"><label for="jform_acsubplan" id="jform_acsubplan-lbl">Ending Subscription Plan</label></div>';
	echo '<div class="controls"><select name="jform[acsubplan]" id="jform_acsubplan" class="inputbox">';
	echo '<option value="">None</option>';
	echo JHtml::_('select.options', $this->list->list_textvars, null,null, $this->list->params->acsubplan, true);
	echo '</select></div>';
	echo '</div>';

	echo '</div>';
	echo '<div class="clr"></div>';

	//Fields
	if ( JVersion::MAJOR_VERSION == 4 ) {
		echo HTMLHelper::_('uitab.endTab');
		echo HTMLHelper::_('uitab.addTab', 'myTab', 'fields', 'Fields');
	} else {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'myTab', 'fields', 'Fields');
	}
	
	echo '<table class="adminlist table table-striped">';
	echo '<thead><tr><th>AC Field</th><th>Type</th><th>MUE Field</th></tr></thead><tbody>';
	foreach ($this->list->list_textvars as $k=>$v) {
        echo '<tr><td>'.$v.'</td>';
        echo '<td>text<input type="hidden" name="jform[acfieldtypes]['.$k.']" value="text"></td><td>';
       if ($k != $this->list->params->acsubstatus && $k != $this->list->params->acsubsince && $k != $this->list->params->acsubexp && $k != $this->list->params->acsubplan) {
            echo '<select name="jform[acvars]['.$k.']" id="jform_'.$k.'" class="inputbox">';
            echo '<option value="">None</option>';
            echo JHtml::_('select.options', $this->ufields, 'value', 'text', $this->list->params->acvars->$k, true);
            echo '</select>';
        } else {
            echo 'Used for Subscription';
        }
        echo '</td></tr>';

	}
	echo '</tbody></table>';
	echo '<div class="clr"></div>';

	if ( JVersion::MAJOR_VERSION == 4 ) {
		echo HTMLHelper::_('uitab.endTab');
		echo HTMLHelper::_( 'uitab.endTabSet' );
		echo '</div>';
	} else {
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_( 'bootstrap.endTabSet' );
	}
	
	?>
    <input type="hidden" name="field" value="<?php echo $this->list->uf_id;?>" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
