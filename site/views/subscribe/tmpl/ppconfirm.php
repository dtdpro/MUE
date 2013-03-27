<div id="system">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$config = MUEHelper::getConfig();

$session=JFactory::getSession();
$user =& JFactory::getUser();
echo '<h2 class="componentheading">Complete Subscription</h2>';
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=subscribe&layout=ppverify&purchaseid='.$this->usid.'&plan='.$this->pinfo->sub_id); ?>" method="POST">
			<p style="padding-left:10px;">Your payment has been verified, please press <b>Complete Subscription</b> to finalize your payment and charge your account for the following plan:<br /><br />
			<?php 
			
			echo '<b>'.$this->pinfo->sub_exttitle.'</b><br />'.$this->pinfo->sub_desc; 
			$btntext = "Complete Subscription $".$session->get('PAYMENTREQUEST_0_AMT');
			if ($this->pinfo->sub_recurring) $btntext .= " Recurring Every ".$this->pinfo->sub_length." ".$this->pinfo->sub_period.'(s)';
			
			?>
			
			
			<br /><br />
			<input type="submit" value="<?php  echo $btntext; ?>" class="button"  /><br /><br /></p>
		</form>

</div>
