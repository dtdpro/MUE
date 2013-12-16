<div id="system" class="uk-article">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();
	?>
<h2 class="componentheading"><?php echo "User Subscriptions"; ?></h2>
<?php 
echo $config->usersub_page_content;

$sub=MUEHelper::getActiveSub();
$numsubs=count(MUEHelper::getUserSubs());
if ($numsubs) {
if (!$sub) {
	echo '<p>Subscription Expired <a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">Renew Subscription</a></p>';
} else {
	echo $config->profile_sub_content;
	if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile") && $sub->daysLeft <= 10) {
		echo '<p>Subscription Expires in '.$sub->daysLeft. ' day(s) <a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">Renew Subscription</a></p>';
	}
}
} else {
	echo '<p>Subscription Required <a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">Add Subscription</a></p>';
}

if ($this->usersubs) {
	echo '<table width="100%" class="zebra">';
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
				case "accepted": echo "Accepted"; break;
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
				case "verified": echo ($sub->daysLeft > 0) ? $sub->daysLeft." Day(s) Left" : "Expired"; break;
			}
		} else {
			switch ($sub->usrsub_rpstatus) {
				case "ActiveProfile": echo 'Recurring <a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=cancelsub&sub='.$sub->usrsub_id).'" class="button uk-button">Cancel</a'; break;
				case "Cancelled": echo "Cancelled"; break;
			}
		}
		echo '</td><td>';
		if ($sub->usrsub_type == "paypal" || $sub->usrsub_type=="google" || $sub->usrsub_type=="check") {
			echo "$".number_format($sub->sub_cost,2);
			if ($sub->sub_recurring) echo '/'.$sub->sub_length.' '.$sub->sub_period.'(s)';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
} else echo '<p>At this time, you have not purchased any subscriptions.</p>';
?>
	</div>