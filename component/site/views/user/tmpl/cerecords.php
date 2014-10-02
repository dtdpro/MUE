<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$config = MUEHelper::getConfig();
	?>
<h2 class="componentheading uk-article-title"><?php echo "User CE Records"; ?></h2>
<?php 

$sub=MUEHelper::getActiveSub();

if ($this->userrecs) {
	echo '<table width="100%" class="zebra uk-table uk-table-striped">';
	echo '<thead><tr><th>Program</th><th>Completed</th><th>Status</th><th>Credits</th><th>Certificate</th></tr></thead><tbody>';
	$total_credits = 0;
	foreach ($this->userrecs as $course) {
		echo '<tr><td><b>';
		echo $course->course_title;
		echo '</b></td><td>';
		if ($course->sess_end != "0000-00-00 00:00:00") echo date("F d, Y", strtotime($course->sess_end));
		echo '</td><td>';
		switch ($course->sess_pstatus) {
			case "incomplete": echo "Incomplete"; break;
			case "pass": echo "Pass"; break;
			case "fail": echo "Fail"; break;
			default: echo "Complete"; break;
		}
		echo '</td><td>';
		if ($course->sess_pstatus == "pass") {
			echo number_format($course->course_credits,2);
			$total_credits = $total_credits + floatval($course->course_credits);
		}
		echo '</td><td> ';
		if ($course->sess_pstatus == "pass" && $course->course_hascert) {
			echo '<a href="'.JURI::base( true ).'/components/com_mcme/gencert.php?certid='.$course->ci_id.'" target="_blank" class="button uk-button">Download</a>';
		}
		echo '</td></tr>';
	}
	echo '</tbody>';
	echo '<tfoot><tr><td colspan="5"><strong>Total Credits: '.number_format($total_credits,2).'</strong></td></tr></tfoot>';
	echo '</table>';
} else echo '<p>At this time, you have not completed any CE programs.</p>';

if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>