<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$config = MUEHelper::getConfig();
	?>
<h2 class="componentheading uk-article-title"><?php echo "User Messages"; ?></h2>
<?php

echo '<p>';
echo '<a href="'.JRoute::_("index.php?option=com_mue&view=userdir").'" class="button uk-button">'.JText::_('COM_MUE_USER_DIRECTORY_BUTTON').'</a> ';
echo '</p>';

?>
<h3>Inbox</h3>
<?php
if ($this->messages) {
	echo '<table width="100%" class="uk-table uk-table-striped">';
	echo '<thead><tr><th width="20%">From</th><th>Subject</th><th width="25%">Date</th></tr></thead><tbody>';
	foreach ($this->messages as $message) {
		echo '<tr>';
		echo '<td><strong>';
		echo $message->name;
		echo '</strong></td><td>';
		echo '<a href="'.JRoute::_("index.php?option=com_mue&view=pm&layout=message&mid=".$message->msg_id).'">';
		echo $message->msg_subject;
		echo '</a>';
		if ($message->msg_status == "new") echo ' <div class="uk-badge">New</div>';
		echo '</td><td>';
		echo date("M j, Y g:i A", strtotime($message->msg_date));
		echo '</td></tr>';
	}
	echo '</tbody>';
	echo '</table>';
} else echo '<p>At this time, you do not have any messages.</p>';
?>
<h3>Sent</h3>
<?php
if ($this->sentMessages) {
	echo '<table width="100%" class="uk-table uk-table-striped">';
	echo '<thead><tr><th width="20%">To</th><th>Subject</th><th width="25%">Date</th></tr></thead><tbody>';
	foreach ($this->sentMessages as $message) {
		echo '<tr>';
		echo '<td><strong>';
		echo $message->name;
		echo '</strong></td><td>';
		echo '<a href="'.JRoute::_("index.php?option=com_mue&view=pm&layout=sentmessage&mid=".$message->msg_id).'">';
		echo $message->msg_subject;
		echo '</a>';
		echo '</td><td>';
		echo date("M j, Y g:i A", strtotime($message->msg_date));
		echo '</td></tr>';
	}
	echo '</tbody>';
	echo '</table>';
} else echo '<p>At this time, you do not have any messages.</p>';

if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>