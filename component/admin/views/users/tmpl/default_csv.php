<?php
//$path = JPATH_SITE.'/cache/';
$cfg=MUEHelper::getConfig();
$filename = 'MUE_Users_Report' . '-' . date("Y-m-d").'.csv';
$contents = "";

$contents .= '"User Id","Username","Name","email","User Group","Join Site","LastVisit","Last Update","Registered"';
if ($cfg->subscribe) $contents .= ',"Subscriber Since","Subcription Status"';
foreach ($this->fdata as $f) {
	$contents .= ',"'.$f->uf_name.'"';
}
$contents .= "\n";

foreach ($this->items as $i) {
	$contents .= '"'.$i->id.'",'; 
	$contents .= '"'.$i->username.'",'; 
	$contents .= '"'.preg_replace( "/\r|\n/", "", $i->name).'",';
	$contents .= '"'.$i->email.'",'; 
	$contents .= '"'.$this->usergroups[$i->userg_group].'",'; 
	$contents .= '"'.$i->userg_siteurl.'",'; 
	$contents .= '"'.$i->lastvisitDate.'",'; 
	$contents .= '"'.$i->lastUpdate.'",'; 
	$contents .= '"'.$i->registerDate.'"'; 
	if ($cfg->subscribe) {
		$contents .= ',"'.$i->member_since.'"'; 
		$contents .= ',"';
		if ($i->sub) {
			if ((int)$i->sub->daysLeft > 0) {
			
				switch ($i->sub->usrsub_status) {
					case "notyetstarted": $contents .=   "Not Yet Started"; break;
					case "verified": $contents .=   "Assessment"; break;
					case "canceled": $contents .=   "Canceled"; break;
					case "accepted": $contents .=   "Accepted"; break;
					case "pending": $contents .=   "Pending"; break;
					case "started": $contents .=   "Started"; break;
					case "denied": $contents .=   "Denied"; break;
					case "refunded": $contents .=   "Refunded"; break;
					case "failed": $contents .=   "Failed"; break;
					case "pending": $contents .=   "Pending"; break;
					case "reversed": $contents .=   "Reversed"; break;
					case "canceled_reversal": $contents .=   "Canceled Dispute"; break;
					case "expired": $contents .=   "Expired"; break;
					case "voided": $contents .=   "Voided"; break;
					case "completed": $contents .=   "Active"; break;
					case "dispute": $contents .=   "Dispute"; break;
				}
				$contents .=  ': '.$i->sub->daysLeft.'  Days Left';
			}
			else $contents .=  'Expired: '.abs((int)$i->sub->daysLeft).'  Days Ago';
		} else {
			$contents .=  'No Subscription';
		}
		$contents .= '"';
	}
	foreach ($this->fdata as $f) {
		$contents .= ',"';
		if (!$f->uf_cms) {
			$fn=$f->uf_sname;
			$udf = $this->udata->$fn;
			$uid = $i->id;
			if ($f->uf_type == 'multi' || $f->uf_type == 'dropdown' || $f->uf_type == 'mcbox' || $f->uf_type == 'mlist') {
				$ansarr=explode(" ",$udf[$uid]);
				foreach ($ansarr as $a) {
					$contents .= $this->adata[$a]." "; 
				}
			} else if ($f->uf_type == 'cbox' || $f->uf_type == 'yesno' || $f->uf_type == 'mailchimp') {
				$contents .= ($udf[$uid] == "1") ? "Yes" : "No";
			} else if ($f->uf_type == 'birthday') {
				if ($udf[$uid]) $contents .= date("F j",strtotime('2000-'.substr($udf[$uid],0,2)."-".substr($udf[$uid],2,2).''));
			} else{
				$contents .= $udf[$uid];
			}
		}
		$contents .= '"';
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

