<?php
$procount=0;
$free=false;
$config = MUEHelper::getConfig();

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

// Payment Options
echo '<table width="100%" border="0" align="center">';
echo '<tr>';

if (!$free) {
	if ( $config->paypal ) {
		echo '<td align="center" valign="top" width="' . $colwid . '">';
		echo $config->paypal_msg;
		echo '<form action="" method="post" name="ppform" id="ppform">';
		echo '<input type="image" name="submit" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" />';
		echo '<input type="hidden" name="layout" value="ppsubpay" />';
		echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
		echo $formtoken;
		echo '</form>';
		echo '</td>';
	}
	if ( $config->paybycheck ) {
		echo '<td align="center" valign="top" width="' . $colwid . '">';
		echo '<form action="" method="post" name="checkform" id="checkform">';
		echo '<input type="submit" name="submit" value="Pay By Check" class="button uk-button uk-button-primary uk-button-large" />';
		echo '<input type="hidden" name="layout" value="paybycheck" />';
		echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
		echo $formtoken;
		echo '</form>';
		echo '</td>';
	}
} else {
	// Free Subscription
	echo '<td align="center" valign="top" width="' . $colwid . '">';
	echo '<form action="" method="post" name="checkform" id="checkform">';
	echo '<input type="submit" name="submit" value="Submit" class="button uk-button uk-button-primary uk-button-large" />';
	echo '<input type="hidden" name="layout" value="freeofcharge" />';
	echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
	echo $formtoken;
	echo '</form>';
	echo '</td>';

}
echo '</tr></table>';

if ($config->show_continue) {
	echo '<table width="100%" border="0" align="center">';
	echo '<tr>';
	if ($this->return) $continuelink = $this->return;
	else $continuelink = JRoute::_('index.php?option=com_mue&view=user&layout=profile');
	echo '<td align="center" valign="top" width="' . $colwid . '">';
	echo '<a href="'.$continuelink.'"  class="button uk-button uk-button-default">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_CONTINUE').'</a>';
	echo '</td>';
	echo '</tr></table>';
}

