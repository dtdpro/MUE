<div id="continued">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();

$user =& JFactory::getUser();
$first=true;

echo '<h2 class="componentheading">Subscription</h2>';

if (!$user->id) {
	echo '<p align="center"><span style="color:#800000;font-weight:bolder;">'.$config->LOGIN_MSG.'</span></p>';
} else {
	echo $config->sub_page_content;
	$formtoken=JHTML::_( 'form.token' );
	if ($config->paypal) {
		echo '<div id="mue-plan-pick">';
		echo '<form action="" method="post" name="subform" id="subform">';
		echo '<div class="mue-plan-pick-hdr">';
		echo 'Select your plan below';
		echo '</div>';
		echo '<div class="mue-plan-pick-row">';
		foreach ($this->plans as $g) {
			echo '<input type="radio" name="plan" id="plan_'.$g->sub_id.'" value="'.$g->sub_id.'"';
			if ($first) { echo ' validate="{required:true, messages:{required:\'Please select a plan\'}}"'; $first=false; }
			echo ' class="mue-plan-pick-radio">';
			echo '<label class="mue-plan-pick-item" for="plan_'.$g->sub_id.'"><span><b>';
			echo ''.$g->sub_exttitle.'</b><br />'.$g->sub_desc.'</span></label>'; 	
		}
		echo '</div>';
		echo '<div class="mue-plan-pick-submit">';
		echo '</div>';
		echo '<div class="mue-plan-pick-submit">';
		echo '<input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" />';
		echo '<input type="hidden" name="layout" value="ppsubpay" />';
		echo '</div>';
		echo $formtoken;
		echo '</form>';
		echo '<div style="clear:both;"></div>';
		echo '</div>';
	}
	if ($config->redemption) {
		echo '<div id="mue-plan-pick">';
		echo '<form action="" method="post" name="redeemcodecheckout" id="redeemcodecheckout">';
		echo '<div class="mue-plan-pick-hdr">';
		echo 'Redeem your code below';
		echo '</div>';
		echo '<div id="mue-plan-pick-row">';
		echo '<input type="text" name="redeemcode" class="field_purchase" validate="{required:true, messages:{required:\'Please enter a code to redeem\'}}" /></div>';
		echo '<div class="mue-plan-pick-submit">';
		echo '</div>';
		echo '<div class="mue-plan-pick-submit">';
		echo '<input type="submit" name="submit" value="Redeem Code" class="button" />';
		echo '<input type="hidden" name="layout" value="redeem" />';
		echo $formtoken;
		echo '</form>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '</div>';
	}
	
}

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery.metadata.setType("attr", "validate");
		jQuery("#subform").validate({
			errorClass:"uf_pickerror",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });
		jQuery(".mue-plan-pick-item").click(function(){
			var parent = jQuery(this).parents('.mue-plan-pick-row');
			jQuery('.mue-plan-pick-item',parent).removeClass('selected');
			jQuery(this).addClass('selected');
		});
	
	});
</script>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery.metadata.setType("attr", "validate");
		jQuery("#redeemcodecheckout").validate({
			errorClass:"uf_pickerror",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });	
	});
</script>
</div>
