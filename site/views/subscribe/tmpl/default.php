<div id="system">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();

$user =& JFactory::getUser();
$first=true;
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery.metadata.setType("attr", "validate");
		jQuery(".mue-plan-pick-item").click(function(){
			var parent = jQuery(this).parents('.mue-plan-pick-row');
			jQuery('.mue-plan-pick-item',parent).removeClass('selected');
			jQuery(this).addClass('selected');
		});
		jQuery(':radio').live('click',function(e){
			
			/* Send the data using post and put the results in a div */
			loadPaymentOptions();
		});
	
	});
	function loadPaymentOptions() {
		var url = '<?php echo JURI::base( true ); ?>/components/com_mue/subplans.php';
		jQuery.post( url, jQuery("#subform").serialize(),
			function( data ) {
				jQuery( "#mue-processors" ).empty().append( data );
			}
		);
	}
</script>
<?php 
echo '<h2 class="componentheading">Subscription</h2>';

if (!$user->id) {
	echo '<p align="center"><span style="color:#800000;font-weight:bolder;">'.$config->LOGIN_MSG.'</span></p>';
} else {
	echo $config->sub_page_content;
	$formtoken=JHTML::_( 'form.token' );
	
	echo '<div id="mue-plan-pick">';
	echo '<div class="mue-plan-pick-hdr">';
	echo 'Select your plan below';
	echo '</div>';
	echo '<div class="mue-plan-pick-row">';
	echo '<form action="/components/com_mue/subplans.php" method="post" name="subform" id="subform">';
	foreach ($this->plans as $g) {
		echo '<input type="radio" name="plan" id="plan_'.$g->sub_id.'" value="'.$g->sub_id.'"';
		if ($first) { echo ' validate="{required:true, messages:{required:\'Please select a plan\'}}"'; $first=false; }
		if (count($this->plans) == 1) echo ' checked="checked"';
		echo ' class="mue-plan-pick-radio">';
		echo '<label class="mue-plan-pick-item';
		if (count($this->plans) == 1) echo ' selected';
		echo '" for="plan_'.$g->sub_id.'"><span><b>';
		echo ''.$g->sub_exttitle.'</b><br />'.$g->sub_desc.'</span></label>'; 	
	}
	echo $formtoken;
	echo '</form>';
	echo '</div>';
	echo '<div class="mue-plan-pick-submit">';
	echo '</div>';
	echo '<div class="mue-plan-pick-submit" id="mue-processors">';
	echo '</div>';
	echo '<div style="clear:both;"></div>';
	echo '</div>';


	
}

if (count($this->plans) == 1) {
	?>
<script type="text/javascript">
	loadPaymentOptions();
</script>
<?php } ?>

</div>
