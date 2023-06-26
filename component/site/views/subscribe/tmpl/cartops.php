<?php
$procount=0;
$free=false;
$config = MUEHelper::getConfig();

if ($this->pinfo->sub_cost == 0 || $this->pinfo->discounted == 0) {
	$free=true;
	$procount++;
} else {
	if ( $config->paypal ) {
		$procount++;
	}
	if ( $config->paybycheck ) {
		$procount++;
	}
}

$formtoken=JHTML::_( 'form.token' );

// Payment Options
echo '<hr>';
echo '<div class="uk-margin-top" uk-grid>';

if (!$free) {
	if ($procount == 2) {
		echo '<div class="uk-width-1-6">';
		echo '</div>';
	} else {
		echo '<div class="uk-width-1-3">';
		echo '</div>';
	}

	if ( $config->paypal ) {
		echo '<div class="uk-width-1-3 uk-text-center">';
		echo $config->paypal_msg;
		echo '<form action="" method="post" name="ppform" id="ppform">';
		echo '<input type="image" name="submit" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" />';
		echo '<input type="hidden" name="layout" value="ppsubpay" />';
		echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
		echo $formtoken;
		echo '</form>';
		echo '</div>';
	}
	if ( $config->paybycheck ) {
		echo '<div class="uk-width-1-3">';
		echo '<form action="" method="post" name="checkform" id="checkform">';
		echo '<input type="submit" name="submit" value="Pay By Check" class="button uk-button uk-button-primary uk-button-large uk-width-1-1" />';
		echo '<input type="hidden" name="layout" value="paybycheck" />';
		echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
		echo $formtoken;
		echo '</form>';
		echo '</div>';
	}

	if ($procount == 2) {
		echo '<div class="uk-width-1-6">';
		echo '</div>';
	} else {
		echo '<div class="uk-width-1-3">';
		echo '</div>';
	}
} else {
	// Free Subscription

	echo '<div class="uk-width-1-3">';
	echo '</div>';

	echo '<div class="uk-width-1-3">';
	echo '<form action="" method="post" name="checkform" id="checkform">';
	echo '<input type="submit" name="submit" value="Submit" class="button uk-button uk-button-primary uk-button-large uk-width-1-1" />';
	echo '<input type="hidden" name="layout" value="freeofcharge" />';
	echo '<input type="hidden" name="plan" value="' . $this->pinfo->sub_id . '" />';
	echo $formtoken;
	echo '</form>';
	echo '</div>';

	echo '<div class="uk-width-1-3">';
	echo '</div>';

}
echo '</div>';

if ($config->show_continue) {
	echo '<hr>';

	echo '<div class="uk-margin-top" uk-grid>';

	echo '<div class="uk-width-1-3">';
	echo '</div>';

	echo '<div class="uk-width-1-3 uk-text-center">';

	if ($this->return) $continuelink = $this->return;
	else $continuelink = JRoute::_('index.php?option=com_mue&view=user&layout=profile');
	echo '<a href="'.$continuelink.'"  class="button uk-button uk-button-default uk-width-1-1">'.JText::_('COM_MUE_SUBSCRIBE_LABEL_CONTINUE').'</a>';

	echo '</div>';

	echo '<div class="uk-width-1-3">';
	echo '</div>';

	echo '</div>';
}

