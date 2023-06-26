<?php
defined('_JEXEC') or die('Restricted access');

// Load CSV Exporter
require JPATH_COMPONENT."/vendor/autoload.php";

$cfg=MUEHelper::getConfig();
$filename = 'MUE_User_Subscriptions_Report' . '-' . date("Y-m-d").'.csv';

// Basic Headings
$headers = ["Subscription Id","Plan","Name","Username","Email","Type","Coupon Code","Subscribed At","Start","End","Pay Status"];

// Data Rows
$dataRows = [];
foreach ($this->items as $i) {
	$dataRow = [];

	$dataRow[] = $i->usrsub_id;
	$dataRow[] = $i->sub_exttitle;
	$dataRow[] = preg_replace( "/\r|\n/", "", $i->user_name);
	$dataRow[] = $i->username;
	$dataRow[] = $i->user_email;

	$type = "";
	switch ($i->usrsub_type) {
		case "paypal": $type = "PayPal"; break;
		case "redeem": $type = "Code"; break;
		case "admin": $type = "Admin"; break;
		case "google": $type = "Google"; break;
		case "migrate": $type = "Migrated"; break;
		case "check": $type = "Check"; break;
		case "trial": $type = "Trial/Free"; break;
	}
	$dataRow[] = $type;

	$dataRow[] = $i->usrsub_coupon;
	$dataRow[] = $i->usrsub_time;
	$dataRow[] = $i->usrsub_start;
	$dataRow[] = $i->usrsub_end;



	$status = "";
	switch ($i->usrsub_status) {

		case "notyetstarted": $status =  "Not Yet Started"; break;
		case "verified": $status =  "Verified"; break;
		case "canceled": $status =  "Canceled"; break;
		case "accepted": $status =  "Accepted"; break;
		case "pending": $status =  "Pending"; break;
		case "started": $status =  "Started"; break;
		case "denied": $status =  "Denied"; break;
		case "refunded": $status =  "Refunded"; break;
		case "failed": $status =  "Failed"; break;
		case "pending": $status =  "Pending"; break;
		case "reversed": $status =  "Reversed"; break;
		case "canceled_reversal": $status =  "Canceled Dispute"; break;
		case "expired": $status =  "Expired"; break;
		case "voided": $status =  "Voided"; break;
		case "completed": $status =  "Completed"; break;
		case "dispute": $status =  "Dispute"; break;
		case "error": $status =  "Error"; break;
	}

	$dataRow[] = $status;

	$dataRows[] = $dataRow;
}

// HTTP Headers
$app = JFactory::getApplication();
$app->clearHeaders();
$app->setHeader( "Pragma", "public" );
$app->setHeader( 'Cache-Control', 'no-cache, must-revalidate', true );
$app->setHeader( 'Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true );
$app->setHeader( 'Content-Type', 'text/csv', true );
$app->setHeader( 'Content-Description', 'File Transfer', true );
$app->setHeader( 'Content-Disposition', 'attachment; filename="' . $filename . '"', true );
$app->setHeader( 'Content-Transfer-Encoding', 'binary', true );
$app->sendHeaders();

// Create CSV Writer
$csv = \League\Csv\Writer::createFromString();

// insert the Headings
$csv->insertOne($headers);

// insert all the records
$csv->insertAll($dataRows);

// CSV content
echo $csv->toString();

// stop
$app->close();
