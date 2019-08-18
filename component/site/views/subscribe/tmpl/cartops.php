<?php
$procount=0;
$free=false;
$config = MUEHelper::getConfig();

$procount++;
if ($this->pinfo->sub_cost == 0 || $this->pinfo->discounted == 0) {
	$free=true;
	$procount++;
	if ( $config->show_continue ) {
		$procount++;
	}
} else {
	if ( $config->paypal ) {
		$procount++;
	}
	if ( $config->paybycheck ) {
		$procount++;
	}
	if ( $config->show_continue ) {
		$procount++;
	}
}
if ($procount == 3) $colwid="33%";
if ($procount == 2) $colwid="50%";
if ($procount == 1) $colwid="100%";

$formtoken=JHTML::_( 'form.token' );
echo '<table width="100%" border="0" align="center">';
echo '<tr>';

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

if (!$free) {
//Couponcode

	if ( $config->paypal ) {
		echo '<td align="center" valign="middle" width="' . $colwid . '">';
		echo $config->paypal_msg;
		echo '<form action="" method="post" name="ppform" id="ppform">';
		echo '<input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" />';
		echo '<input type="hidden" name="layout" value="ppsubpay" />';
		echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
		echo $formtoken;
		echo '</form>';
		echo '</td>';
	}
	if ( $config->paybycheck ) {
		echo '<td align="center" valign="middle" width="' . $colwid . '">';
		echo '<form action="" method="post" name="checkform" id="checkform">';
		echo '<input type="submit" name="submit" value="Pay By Check" class="button uk-button" />';
		echo '<input type="hidden" name="layout" value="paybycheck" />';
		echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
		echo $formtoken;
		echo '</form>';
		echo '</td>';
	}
} else {
	// Free Subscription
	echo '<td align="center" valign="middle" width="' . $colwid . '">';
	echo '<form action="" method="post" name="checkform" id="checkform">';
	echo '<input type="submit" name="submit" value="Submit" class="button uk-button" />';
	echo '<input type="hidden" name="layout" value="freeofcharge" />';
	echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
	echo $formtoken;
	echo '</form>';
	echo '</td>';

}

if ($config->show_continue) {
	if ($this->return) $continuelink = $this->return;
	else $continuelink = JRoute::_('index.php?option=com_mue&view=user&layout=profile');
	echo '<td align="center" valign="middle" width="' . $colwid . '">';
	echo '<a href="'.$continuelink.'"  class="button uk-button">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_CONTINUE').'</a>';
	echo '</td>';
}

echo '</tr></table>';
