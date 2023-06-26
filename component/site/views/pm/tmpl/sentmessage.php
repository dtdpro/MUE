<?php // no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$config = MUEHelper::getConfig();
	?>
<h2 class="componentheading uk-article-title"><?php echo "User Message"; ?></h2>
<?php

if ($this->message) {
	echo '<p class="uk-text-right">';;
	echo '<a href="'.JRoute::_("index.php?option=com_mue&view=pm&layout=messages").'" class="button uk-button uk-button-primary">Message List</a> ';
	echo '</p>';
	echo '<div id="mue-user-info" class="uk-form uk-form-horizontal">';
	echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row0"><div class="uk-form-label mue-user-info-label uk-text-bold">Date</div><div class="uk-form-controls uk-form-controls-text mue-user-info-hdr">'.HTMLHelper::_('date', $this->message->msg_date, "M j, Y g:i A").'</div></div>';
	echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row1"><div class="uk-form-label mue-user-info-label uk-text-bold">To</div><div class="uk-form-controls uk-form-controls-text mue-user-info-hdr">'.$this->message->name.'</div></div>';
	echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row0"><div class="uk-form-label mue-user-info-label uk-text-bold">Subject</div><div class="uk-form-controls uk-form-controls-text mue-user-info-hdr">'.$this->message->msg_subject.'</div></div>';
	echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row1"><div class="uk-form-label mue-user-info-label uk-text-bold">Message</div><div class="uk-form-controls uk-form-controls-text mue-user-info-hdr">'.$this->message->msg_body.'</div></div>';
    echo '</div>';
}

?>