<?php
$path = JPATH_SITE.'/cache/';
$filename = 'MUE_Email_List' . '-' . date("Y-m-d").'.csv';
$contents = "";

$contents .= '"Name","email"';
$contents .= "\n";

foreach ($this->items as $i) {
	$contents .= '"'.$i->name.'",';
	$contents .= '"'.$i->email.'"'; 
	
	$contents .= "\n";
}
JFile::write($path.$filename,$contents);

 $app = JFactory::getApplication();
 $app->redirect('../cache/'.$filename);

