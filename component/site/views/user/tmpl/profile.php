<?php // no direct access
defined('_JEXEC') or die('Restricted access');
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USER_PROFILE_PAGE_TITLE').'</h2>';
$i=0;
$cfg=MUEHelper::getConfig();
$sub=false;
if ($cfg->subscribe){
	$sub=MUEHelper::getActiveSub();
	$numsubs=count(MUEHelper::getUserSubs());
}
echo $cfg->profile_top_content;
if ($sub) echo $cfg->profile_sub_content;
echo '<p>';
echo '<a href="'.JRoute::_("index.php?option=com_mue&view=user&layout=proedit").'" class="button uk-button uk-button-primary">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_EDIT').'</a> ';
if (!$this->one_group) echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=chggroup').'" class="button uk-button uk-button-default">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_CHANGE_GROUP').'</a> ';
echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=chgemail').'" class="button uk-button uk-button-default">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_CHANGE_EMAIL').'</a> ';
if ($cfg->subscribe){
	if ($numsubs) {
		if (!$sub) {
			echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button uk-button-default">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_RENEW_SUB').'</a> ';
		} else {
			if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile")&& $sub->daysLeft <= 10) echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button uk-button-default">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_RENEW_SUB').'</a> ';
		}
	} else {
		echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button uk-button-default">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_ADD_SUB').'</a> ';
	}
}
echo '</p>';

echo '<div class="uk-grid uk-grid-small" uk-grid>';

// User Group
if (!$this->one_group) {
	echo '<div class="uk-width-1-4 uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_USER_GROUP').'</div>';
	echo '<div class="uk-width-3-4">'.$this->userinfo->userGroupName.'</div>';
}

// User Subscription
if ($cfg->subscribe){
	if ($numsubs) {
		if (!$sub) {
			echo '<div class="uk-width-1-4 uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_SUB_STATUS').'</div><div class="uk-width-3-4">Expired</div>';
		} else {
			echo '<div class="uk-width-1-4 uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_SUB_STATUS').'</div><div class="uk-width-3-4">Active '.$sub->daysLeft. ' Day(s) Left</div>';
		}
	} else {
		echo '<div class="uk-width-1-4 uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_SUB_STATUS').'</div><div class="uk-width-3-4">None</div>';
	}
}

// Reg date
echo '<div class="uk-width-1-4 uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_REGISTERED_ON').'</div><div class="uk-width-3-4">'.$this->userinfo->registerDate.'</div>';

// Last Updated
echo '<div class="uk-width-1-4 uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_LAST_UPDATED').'</div><div class="uk-width-3-4">'.$this->userinfo->lastUpdated.'</div>';

// User Fields
foreach ($this->userfields as $f) {
	if ($f->uf_type != "password" && $f->uf_profile) {
		$field=$f->uf_sname;
		if ($f->uf_type == "html") {
			echo '<div class="uk-width-1-1">';
			echo $f->uf_note;
			echo '</div>';
		} else if ($f->uf_type == "message") {
            echo '<div class="uk-width-1-4 uk-text-bold"></div>';
		    echo '<div class="uk-width-3-4"><div class="uk-alert">'.$f->uf_name.'</div></div>';
        } else {
			echo '<div class="uk-width-1-4 uk-text-bold">' . $f->uf_name . '</div>';
			echo '<div class="uk-width-3-4">';
			if (property_exists($this->userinfo,$field)) echo $this->userinfo->$field;
			else echo '&nbsp;';
			echo '</div>';
		}
	}
}

echo '</div>';

echo $cfg->profile_bottom_content;
?>
