<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$config = MUEHelper::getConfig();

$session=JFactory::getSession();
$user = JFactory::getUser();
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_SUBSCRIBE_PPCONFIRM_PAGE_TITLE').'</h2>';
if ($config->show_progbar) {
	if ( true ) {
		echo '<div class="uk-progress"><div class="uk-progress-bar" style="width: 66%;"></div></div>';
	}
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=subscribe&layout=ppverify&purchaseid='.$this->usid.'&plan='.$this->pinfo->sub_id); ?>" method="POST">
			<?php
			if ($this->pinfo->sub_recurring) {
				echo '<div class="uk-alert uk-alert-danger box-warning">This subscription reoccurs every '.$this->pinfo->sub_length." ".$this->pinfo->sub_period.'(s) for $'.$session->get('PAYMENTREQUEST_0_AMT').'</div>';
			}

			echo '<p style="padding-left:10px;">Your payment has been verified, please press <b>Complete Subscription</b> to finalize your payment and charge your account for the following plan:<br /><br />';

			echo '<b>'.$this->pinfo->sub_exttitle.'</b><br />'.$this->pinfo->sub_desc; 
			$btntext = "Complete Subscription $".$session->get('PAYMENTREQUEST_0_AMT');
			if ($this->pinfo->sub_recurring) $btntext .= " Recurring Every ".$this->pinfo->sub_length." ".$this->pinfo->sub_period.'(s)';
			
			?>
			
			
			<br /><br />
			<input type="submit" value="<?php  echo $btntext; ?>" class="button uk-button"  /><br /><br /></p>
		</form>

<?php 
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>
