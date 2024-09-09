<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

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
    <div class="span12 form-horizontal col-md-12">
        <h4>Message Details</h4>
        <div class="control-group">
            <div class="control-label">Date</div>
            <div class="controls"><?php echo $this->item->msg_date;?></div>
        </div>
        <div class="control-group">
            <div class="control-label">To</div>
            <div class="controls"><?php echo $this->item->toName;?></div>
        </div>
        <div class="control-group">
            <div class="control-label">From</div>
            <div class="controls"><?php echo $this->item->fromName;?></div>
        </div>
        <div class="control-group">
            <div class="control-label">Subject</div>
            <div class="controls"><?php echo $this->item->msg_subject;?></div>
        </div>
        <div class="control-group">
            <div class="control-label">Message</div>
            <div class="controls"><?php echo $this->item->msg_body;?></div>
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

