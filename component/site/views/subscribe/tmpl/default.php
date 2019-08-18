<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$config = MUEHelper::getConfig();

$user = JFactory::getUser();
$first=true;
?>
<script type="text/javascript">
    function selectPlan(planId) {
        if (prevPlan = jQuery("#planId").val()) {
            jQuery("#plan_"+prevPlan).removeClass('selected');
        }
        jQuery("#planId").val(planId);
        jQuery("#plan_"+planId).addClass('selected');
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
	echo '<div class="mue-plan-pick-hdr">';
	echo 'Select your plan below';
	echo '</div>';
	echo '<div class="mue-plan-pick-row">';
	echo '<form action="'.JURI::base( true ).'/components/com_mue/subplans.php" method="post" name="subform" id="subform">';
	echo '<div class="uk-grid" data-uk-grid-margin>';
	foreach ($this->plans as $g) {
		echo '<div class="uk-width-1-1"><div id="plan_'.$g->sub_id.'" class="mue-plan-pick-item uk-width-1-1 uk-button';
		if (count($this->plans) == 1) echo ' selected';
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
	echo '<div class="mue-plan-pick-submit" id="mue-processors">';

	echo '<table width="100%" border="0" align="center">';
	echo '<tr>';
	$procount=1;
    if ($config->show_continue) { $procount++; }
	if ($procount == 2) $colwid="50%";
	if ($procount == 1) $colwid="100%";


	//Couponcode
	echo '<td align="center" valign="middle" width="' . $colwid . '">';
	echo '<div class="mue-subscribe-coupon">';
	echo '<form action="" method="post" name="addcode" id="addcode" class="box style uk-fomr uk-form-stacked">';
	echo '<div class="uk-form-row">';
	echo '<div class="uk-form-label"><label for="discountcode">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_COUPON_CODE').'</label></div>';
	echo '<div class="uk-form-controls"><input name="discountcode" id="discountcode" value="'.$this->discountcode.'" class="pf_optselect" type="text"';
	echo ' data-rule-required="true" data-msg-required="Enter Code"';
	echo ' data-rule-remote="'.JURI::base( true ).'/components/com_mue/helpers/chkcode.php"';
	echo ' data-msg-remote="Invalid code"';
	echo '></div></div>';
	echo '<div class="uk-form-row"><div class="uk-form-controls uk-form-controls-text"><input type="submit" name="submit" value="Apply Code" class="button uk-button" /></div></div>';
	echo '<input type="hidden" name="layout" value="addcode" />';
	echo '</form>';
	echo '</div>';
	echo '</td>';

	if ($config->show_continue) {
		if ($this->return) $continuelink = $this->return;
		else $continuelink = JRoute::_('index.php?option=com_mue&view=user&layout=profile');
		echo '<td align="center" valign="middle" width="' . $colwid . '">';
		echo '<a href="'.$continuelink.'"  class="button uk-button">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_CONTINUE').'</a>';
		echo '</td>';
	}

	echo '</tr></table>';
	echo '</div>';
	echo '<div style="clear:both;"></div>';
	echo '</div>';



}

if (count($this->plans) == 1) {
	?>
    <script type="text/javascript">
        loadPaymentOptions();
    </script>
<?php }

if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>

