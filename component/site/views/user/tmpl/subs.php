<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();

echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USER_SUBS_PAGE_TITLE').'</h2>';

echo $config->usersub_page_content;

$sub=MUEHelper::getActiveSub();
$numsubs=count(MUEHelper::getUserSubs());
if ($numsubs) {
if (!$sub) {
	echo '<p><a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">'.JText::_('COM_MUE_USER_SUBS_BUTTON_RENEW_SUB').'</a></p>';
} else {
	echo $config->profile_sub_content;
	if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile") && $sub->daysLeft <= 10) {
		echo '<p>Subscription Expires in '.$sub->daysLeft. ' day(s) <a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">'.JText::_('COM_MUE_USER_SUBS_BUTTON_RENEW_SUB').'</a></p>';
	}
}
} else {
	echo '<p><a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">'.JText::_('COM_MUE_USER_SUBS_BUTTON_ADD_SUB').'</a></p>';
}

if ($this->usersubs) {
	echo '<table width="100%" class="uk-table uk-table-striped">';
	echo '<thead><tr><th>Plan</th><th>Begins</th><th>Expires</th><th>Method</th><th>Status</th><th>Cost</th></tr></thead><tbody>';
	foreach ($this->usersubs as $sub) {
		echo '<tr><td><b>';
		echo $sub->sub_exttitle;
		echo '</b></td>';
		echo '<td> '.date("M d, Y", strtotime($sub->usrsub_start)).'</td>';
		echo '<td> '.date("M d, Y", strtotime($sub->usrsub_end)).'</td>';
		echo '<td>';
		switch ($sub->usrsub_type) {
			case "paypal": echo "PayPal"; break;
			case "trial": echo "Trial"; break;
			case "redeem": echo "Code"; break;
			case "admin": echo "Admin"; break;
			case "google": echo "Google"; break;
			case "migrate": echo "Migrated"; break;
			case "check": echo '<a href="'.JRoute::_("index.php?option=com_mue&view=subscribe&layout=check&plan=".$sub->sub_id).'" title="View Pay by Check Information">Check</a>'; break;
		}
		echo '</td><td>';
		if (!$sub->sub_recurring || $sub->usrsub_type == 'check') {
			switch ($sub->usrsub_status) {
				case "notyetstarted": echo "Not Yet Started"; break;
				case "canceled": echo "Canceled"; break;
				case "verified": echo "Incomplete"; break;
				case "pending": echo "Pending"; break;
				case "started": echo "Started"; break;
				case "denied": echo "Denied"; break;
				case "refunded": echo "Refunded"; break;
				case "failed": echo "Failed"; break;
				case "pending": echo "Pending"; break;
				case "reversed": echo "Reversed"; break;
				case "canceled_reversal": echo "Canceled Dispute"; break;
				case "dispute": echo "Dispute"; break;
				case "expired": echo "Expired"; break;
				case "voided": echo "Voided"; break;
				case "completed": 
				case "accepted": echo ($sub->daysLeft > 0) ? $sub->daysLeft." Day(s) Left" : "Expired"; break;
			}
		} else {
			switch ($sub->usrsub_rpstatus) {
				case "ActiveProfile": echo 'Recurring <a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=cancelsub&sub='.$sub->usrsub_id).'" class="button uk-button">Cancel</a'; break;
				case "Cancelled": echo "Cancelled"; break;
			}
		}
		echo '</td><td>';
		if ($sub->usrsub_type == "paypal" || $sub->usrsub_type=="google" || $sub->usrsub_type=="check" || $sub->usrsub_type=="trial") {
			if ($sub->usrsub_type == "paypal" || $sub->usrsub_type=="google" || $sub->usrsub_type=="check") {
				echo "$".number_format($sub->usrsub_cost,2);
			}
			if ($sub->sub_recurring) echo '/'.$sub->sub_length.' '.$sub->sub_period.'(s)';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
} else echo JText::_('COM_MUE_USER_SUBS_MESSAGE_NO_SUBS');
?>
