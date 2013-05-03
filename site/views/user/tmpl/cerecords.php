<div id="system">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();
	?>
<h2 class="componentheading"><?php echo "User CE Records"; ?></h2>
<?php 

$sub=MUEHelper::getActiveSub();

if ($this->usercerts) {
	echo '<table width="100%" class="zebra">';
	echo '<thead><tr><th>Program</th><th>Issue Date</th><th>Credits</th><th></th></tr></thead><tbody>';
	$total_credits = 0;
	foreach ($this->usercerts as $course) {
		echo '<tr><td><b>';
		echo $course->course_certtitle;
		echo '</b></td><td>';
		echo date("F d, Y", strtotime($course->ci_issuedon));
		echo '</td><td>';
		echo number_format($course->course_credits,2);
		$total_credits = $total_credits + floatval($course->course_credits);
		echo '</td><td> ';
		echo '<a href="'.JURI::base( true ).'/components/com_mcme/gencert.php?certid='.$course->ci_id.'" target="_blank" class="button">Download Certificate</a>';
		echo '</td></tr>';
	}
	echo '</tbody>';
	echo '<tfoot><tr><td colspan="5"><strong>Total Credits: '.number_format($total_credits,2).'</strong></td></tr></tfoot>';
	echo '</table>';
} else echo '<p>At this time, you have not completed any CE programs.</p>';
?>
	</div>