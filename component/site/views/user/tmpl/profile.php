<div id="system" class="uk-article">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
	?>
<h2 class="componentheading uk-article-title">User Profile</h2>
<?php 
$cfg=MUEHelper::getConfig();
$sub=false;
if ($cfg->subscribe){
	$sub=MUEHelper::getActiveSub();
	$numsubs=count(MUEHelper::getUserSubs());
}
echo $cfg->profile_top_content;
if ($sub) echo $cfg->profile_sub_content;
echo '<p>';
echo '<a href="'.JRoute::_("index.php?option=com_mue&view=user&layout=proedit").'" class="button uk-button">Edit Profile</a> ';
echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=chggroup').'" class="button uk-button">Change Group</a> ';
echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=chgemail').'" class="button uk-button">Change Email</a> ';
if ($cfg->subscribe){
	if ($numsubs) {
		if (!$sub) {
			echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">Renew Subscription</a> ';
		} else {
			if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile")&& $sub->daysLeft <= 10) echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button">Renew Subscription</a> ';
		}
	} else {
		echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">Add Subscription</a> ';
	}
}
echo '</p>';
echo '<div id="mue-user-info">';
echo '<div class="mue-user-info-row mue-rowh"><div class="mue-user-info-label">User Group</div><div class="mue-user-info-hdr">'.$this->userinfo->userGroupName.'</div></div>';
if ($i==1) $i=0; else $i=1;
if ($cfg->subscribe){
	if ($numsubs) {
		if (!$sub) {
			echo '<div class="mue-user-info-row mue-row'.($i % 2).'"><div class="mue-user-info-label">Subscription Status</div><div class="mue-user-info-value">Expired</div></div>';
		} else {
			echo '<div class="mue-user-info-row mue-row'.($i % 2).'"><div class="mue-user-info-label">Subscription Status</div><div class="mue-user-info-value">Active '.$sub->daysLeft. ' Day(s) Left';
			echo '</div></div>';
		}
	} else {
		echo '<div class="mue-user-info-row mue-row'.($i % 2).'"><div class="mue-user-info-label">Subscription Status</div><div class="mue-user-info-value">None</div></div>';
	}
}
if ($i==1) $i=0; else $i=1;
echo '<div class="mue-user-info-row mue-row'.($i % 2).'"><div class="mue-user-info-label">Registered on</div><div class="mue-user-info-value">'.$this->userinfo->registerDate.'</div></div>';
if ($i==1) $i=0; else $i=1;
echo '<div class="mue-user-info-row mue-row'.($i % 2).'"><div class="mue-user-info-label">Last Updated</div><div class="mue-user-info-value">'.$this->userinfo->lastUpdated.'</div></div>';
foreach ($this->userfields as $f) {
	if ($f->uf_type != "password" && $f->uf_profile) {
		if ($i==1) $i=0; else $i=1;
		$field=$f->uf_sname;
		echo '<div class="mue-user-info-row mue-row'.($i % 2).'">';
		echo '<div class="mue-user-info-label">'.$f->uf_name.'</div>';
		echo '<div class="mue-user-info-value">'.$this->userinfo->$field;
		echo '</div>';
		echo '</div>';
	}
}
echo '<div style="clear:both;"></div>';
echo '</div>';
echo $cfg->profile_bottom_content;
?>
</div>