<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
?>

	<div class="row-fluid">
		<div class="span12 form-horizontal">
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

