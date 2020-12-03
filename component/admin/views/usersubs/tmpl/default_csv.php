<?php
//$path = JPATH_SITE.'/cache/';
$cfg=MUEHelper::getConfig();
$filename = 'MUE_User_Subscriptions_Report' . '-' . date("Y-m-d").'.csv';
$contents = "";

$contents .= '"Subscription Id","Plan","Name","Username","Email","Type","Coupon Code","Subscribed At","Start","End","Pay Status"';
$contents .= "\n";

foreach ($this->items as $i) {
	$contents .= '"'.$i->usrsub_id.'",';
	$contents .= '"'.$i->sub_exttitle.'",';
	$contents .= '"'.preg_replace( "/\r|\n/", "", $i->user_name).'",';
	$contents .= '"'.$i->username.'",';
	$contents .= '"'.$i->user_email.'",';

	switch ($i->usrsub_type) {
		case "paypal": $contents .= "PayPal"; break;
		case "redeem": $contents .= "Code"; break;
		case "admin": $contents .= "Admin"; break;
		case "google": $contents .= "Google"; break;
		case "migrate": $contents .= "Migrated"; break;
		case "check": $contents .= "Check"; break;
		case "trial": $contents .= "Trial/Free"; break;
	}
	$contents .= ",";

	$contents .= '"'.$i->usrsub_coupon.'",';
	$contents .= '"'.$i->usrsub_time.'",';
	$contents .= '"'.$i->usrsub_start.'",';
	$contents .= '"'.$i->usrsub_end.'",';

	switch ($i->usrsub_status) {

		case "notyetstarted": $contents .= "Not Yet Started"; break;
		case "verified": $contents .= "Verified"; break;
		case "canceled": $contents .= "Canceled"; break;
		case "accepted": $contents .= "Accepted"; break;
		case "pending": $contents .= "Pending"; break;
		case "started": $contents .= "Started"; break;
		case "denied": $contents .= "Denied"; break;
		case "refunded": $contents .= "Refunded"; break;
		case "failed": $contents .= "Failed"; break;
		case "pending": $contents .= "Pending"; break;
		case "reversed": $contents .= "Reversed"; break;
		case "canceled_reversal": $contents .= "Canceled Dispute"; break;
		case "expired": $contents .= "Expired"; break;
		case "voided": $contents .= "Voided"; break;
		case "completed": $contents .= "Completed"; break;
		case "dispute": $contents .= "Dispute"; break;
		case "error": $contents .= "Error"; break;
	}

	$contents .= "\n";
}

JResponse::clearHeaders();
JResponse::setHeader("Pragma","public");
JResponse::setHeader('Cache-Control', 'no-cache, must-revalidate', true);
JResponse::setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true);
JResponse::setHeader('Content-Type', 'text/csv', true);
JResponse::setHeader('Content-Description', 'File Transfer', true);
JResponse::setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"', true);
JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
JResponse::sendHeaders();
echo $contents;
exit();
//JFile::write($path.$filename,$contents);
//$app = JFactory::getApplication();
//$app->redirect('../cache/'.$filename);

