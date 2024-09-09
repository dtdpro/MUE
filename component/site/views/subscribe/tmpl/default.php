<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();

$user = JFactory::getUser();
$first=true;
?>
<script type="text/javascript">
    function selectPlan(planId) {
        if (prevPlan = jQuery("#planId").val()) {
            jQuery("#plan_"+prevPlan).removeClass('selected uk-button-primary').addClass('uk-button-default');
        }
        jQuery("#planId").val(planId);
        jQuery("#plan_"+planId).removeClass('uk-button-default').addClass('selected uk-button-primary');
        var url = '<?php echo JURI::base( true ); ?>/index.php?option=com_mue&view=subscribe&layout=cartops&tmpl=raw';
        jQuery.post( url, jQuery("#subform").serialize(),
            function( data ) {
                jQuery( "#mue-processors" ).empty().append( data );
            }
        );
    }
</script>
<?php
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_SUBSCRIBE_PAGE_TITLE').'</h2>';
if ($config->show_progbar) {
	echo '<div class="uk-progress"><div class="uk-progress-bar" style="width: 33%;"></div></div>';
}

if (!$user->id) {
	echo '<p align="center"><span style="color:#800000;font-weight:bolder;">'.$config->LOGIN_MSG.'</span></p>';
} else {
	echo $config->sub_page_content;
	$formtoken=JHTML::_( 'form.token' );

	echo '<div id="mue-plan-pick">';
	/*echo '<div class="mue-plan-pick-hdr">';
	echo 'Select your plan below';
	echo '</div>';*/

	echo '<hr>';

	echo '<div class="mue-plan-pick-row">';
	echo '<form action="'.JURI::base( true ).'/components/com_mue/subplans.php" method="post" name="subform" id="subform">';
	echo '<div class="uk-grid uk-grid-small" uk-grid>';
	foreach ($this->plans as $g) {
		echo '<div class="uk-width-1-1"><div id="plan_'.$g->sub_id.'" class="mue-plan-pick-item uk-width-1-1 uk-button uk-button-default';
		//if (count($this->plans) == 1) echo ' selected';
		echo '" onclick="selectPlan('.$g->sub_id.');">';
		echo '<b>';
		echo ''.$g->sub_exttitle.' - ';
		if ($g->discounted != -1 ) {
			echo '<strike>$' . number_format( $g->sub_cost, 2 ).'</strike> $'.number_format( $g->discounted, 2 );
		} else {
			echo '$' . number_format( $g->sub_cost, 2 );
		}
		echo '</b><br />' . $g->sub_desc;
		echo '</div></div>';
	}
	echo '</div>';
	echo '<input type="hidden" name="plan" id="planId" value="0">';
	echo $formtoken;
	echo '</form>';
	echo '</div>';
	echo '<div class="mue-plan-pick-submit">';
	echo '</div>';

    echo '<hr>';

	//Couponcode
	echo '<div class="uk-margin-top" uk-grid>';

	echo '<div class="uk-width-1-3">';
	echo '</div>';

	echo '<div class="uk-width-1-3">';

    echo '<form action="" method="post" name="addcode" id="addcode" class="box style uk-form uk-form-stacked">';

    // code field
	echo '<div class="uk-form-row uk-text-center">';
	echo '<label class="uk-form-label" for="discountcode">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_COUPON_CODE').'</label>';
	echo '<div class="uk-form-controls">';
    echo '<input name="discountcode" id="discountcode" value="'.$this->discountcode.'" class="pf_optselect uk-input" type="text" data-rule-required="true" data-msg-required="Enter Code">';
	echo '</div></div>';

    // coupon code submit
	echo '<div class="uk-form-row"><div class="uk-form-controls uk-form-controls-text">';
    echo '<input type="submit" name="submit" value="Apply Code" class="button uk-button uk-button-default uk-width-1-1 uk-margin-small-top" />';
    echo '</div></div>';

	echo '<input type="hidden" name="layout" value="addcode" />';

    echo '</form>';

    echo '</div>';

    echo '<div class="uk-width-1-3">';
	echo '</div>';

    echo '</div>';

	echo '<div class="mue-plan-pick-submit uk-margin-top" id="mue-processors">';

	if ($config->show_continue) {
		echo '<hr>';

		echo '<div class="uk-margin-top" uk-grid>';

		echo '<div class="uk-width-1-1 uk-text-center">';

		if ($this->return) $continuelink = $this->return;
		else $continuelink = JRoute::_('index.php?option=com_mue&view=user&layout=profile');
		echo '<a href="'.$continuelink.'"  class="button uk-button uk-button-default uk-width-1-1">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_CONTINUE').'</a>';

		echo '</div>';

		echo '</div>';
	}



	echo '</div>';

	echo '<div style="clear:both;"></div>';
	echo '</div>';



}

if (count($this->plans) == 1) {
	?>
    <script type="text/javascript">
        //loadPaymentOptions();
    </script>
<?php }

?>

