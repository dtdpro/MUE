<?php
defined('_JEXEC') or die('Restricted access');

// Load CSV Exporter
require JPATH_COMPONENT."/vendor/autoload.php";

$cfg=MUEHelper::getConfig();

// FIlename
$filename = 'MUE_Users_Report' . '-' . date("Y-m-d").'.csv';

// Basic Headings
$headers = ["User Id","Username","Name","email","User Group","Join Site","LastVisit","Last Update","Registered"];

// Subscription Headings
if ($cfg->subscribe) {
	$headers[] = "Subscriber Since";
	$headers[] = "Subcription Status";
	$headers[] = "Days Left/Ago";
}

// Field Headings
foreach ($this->fdata as $f) {
	$headers[] = $f->uf_name;
}

// Data Rows
$dataRows = [];

// Itrate Users
foreach ($this->items as $i) {
	$dataRow = [];

	$dataRow[] = $i->id;
	$dataRow[] = $i->username;
	$dataRow[] = preg_replace( "/\r|\n/", "", $i->name);
	$dataRow[] = $i->email;
	if (isset($this->usergroups[$i->userg_group])) $dataRow[] = $this->usergroups[$i->userg_group];
	else $dataRow[] = "";
	$dataRow[] = $i->userg_siteurl;
	$dataRow[] = $i->lastvisitDate;
	$dataRow[] = $i->lastUpdate;
	$dataRow[] = $i->registerDate;

	if ($cfg->subscribe) {
		$dataRow[] = $i->member_since;
		if ($i->sub) {
			if ((int)$i->sub->daysLeft > 0) {
				switch ($i->sub->usrsub_status) {
					case "notyetstarted": $dataRow[] = "Not Yet Started"; break;
					case "verified": $dataRow[] = "Assessment"; break;
					case "canceled": $dataRow[] = "Canceled"; break;
					case "accepted": $dataRow[] = "Accepted"; break;
					case "pending": $dataRow[] = "Pending"; break;
					case "started": $dataRow[] = "Started"; break;
					case "denied": $dataRow[] = "Denied"; break;
					case "refunded": $dataRow[] = "Refunded"; break;
					case "failed": $dataRow[] = "Failed"; break;
					case "pending": $dataRow[] = "Pending"; break;
					case "reversed": $dataRow[] = "Reversed"; break;
					case "canceled_reversal": $dataRow[] = "Canceled Dispute"; break;
					case "expired": $dataRow[] =  "Expired"; break;
					case "voided": $dataRow[] = "Voided"; break;
					case "completed": $dataRow[] = "Active"; break;
					case "dispute": $dataRow[] = "Dispute"; break;
				}
				$dataRow[] = $i->sub->daysLeft;
			}
			else {
				$dataRow[] = "Expired";
				$dataRow[] = abs((int)$i->sub->daysLeft);
			}
		} else {
			$dataRow[] = "No Subscription";
			$dataRow[] = 0;
		}
	}
	foreach ($this->fdata as $f) {
		if (!$f->uf_cms) {
			$fn=$f->uf_sname;
			$udf = $this->udata->$fn;
			$uid = $i->id;
			if ($f->uf_type == 'multi' || $f->uf_type == 'dropdown' || $f->uf_type == 'mcbox' || $f->uf_type == 'mlist') {
				if (isset($udf[$uid])) {
					$ansarr      = explode( " ", $udf[ $uid ] );
					$rowcontents = "";
					foreach ( $ansarr as $a ) {
						$rowcontents .= $this->adata[ $a ] . " ";
					}
					$dataRow[] = $rowcontents;
				} else {
					$dataRow[] = "";
				}
			} else if ($f->uf_type == 'cbox' || $f->uf_type == 'yesno' || $f->uf_type == 'mailchimp') {
				if (isset($udf[$uid])) $dataRow[] = ($udf[$uid] == "1") ? "Yes" : "No";
				else $dataRow[] = "";
			} else if ($f->uf_type == 'birthday') {
				if ($udf[$uid]) $dataRow[] = date("F j",strtotime('2000-'.substr($udf[$uid],0,2)."-".substr($udf[$uid],2,2).''));
				else $dataRow[] = "";
			} else{
				if (isset($udf[$uid])) $dataRow[] = $udf[$uid];
				else $dataRow[] = "";
			}
		}
	}
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



