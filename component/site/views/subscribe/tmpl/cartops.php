<?php
$procount=0;
$config = MUEHelper::getConfig();

if ($config->paypal) $procount++;
if ($config->paybycheck) $procount++;
if ($procount == 2) $colwid="50%";
if ($procount == 1) $colwid="100%";

$formtoken=JHTML::_( 'form.token' );
echo '<table width="70%" border="0" align="center">';
echo '<tr>';
if ($config->paypal) {
	echo '<td align="center" valign="middle" width="'.$colwid.'">';
	echo '<form action="" method="post" name="ppform" id="ppform">';
	echo '<input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" />';
	echo '<input type="hidden" name="layout" value="ppsubpay" />';
	echo '<input type="hidden" name="plan" value="'.$this->pinfo->sub_id.'" />';
	echo $formtoken;
	echo '</form>';
	echo '</td>';
}
if ($config->paybycheck) {
	echo '<td align="center" valign="middle" width="'.$colwid.'">';
	echo '<form action="" method="post" name="checkform" id="checkform">';
	echo '<input type="submit" name="submit" value="Pay By Check" class="button uk-button" />';
	echo '<input type="hidden" name="layout" value="paybycheck" />';
	echo '<input type="hidden" name="plan" value="'.$this->pinfo->sub_id.'" />';
	echo $formtoken;
	echo '</form>';
	echo '</td>';
}
echo '</tr></table>';
