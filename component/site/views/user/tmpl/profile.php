<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
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
echo '<a href="'.JRoute::_("index.php?option=com_mue&view=user&layout=proedit").'" class="button uk-button">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_EDIT').'</a> ';
if (!$this->one_group) echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=chggroup').'" class="button uk-button">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_CHANGE_GROUP').'</a> ';
echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=chgemail').'" class="button uk-button">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_CHANGE_EMAIL').'</a> ';
if ($cfg->subscribe){
	if ($numsubs) {
		if (!$sub) {
			echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_RENEW_SUB').'</a> ';
		} else {
			if ((!$sub->sub_recurring || $sub->usrsub_rpstatus != "ActiveProfile")&& $sub->daysLeft <= 10) echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_RENEW_SUB').'</a> ';
		}
	} else {
		echo '<a href="'.JRoute::_('index.php?option=com_mue&view=subscribe').'" class="button uk-button">'.JText::_('COM_MUE_USER_PROFILE_BUTTON_ADD_SUB').'</a> ';
	}
}
echo '</p>';
echo '<div id="mue-user-info" class="uk-form uk-form-horizontal">';
if (!$this->one_group) echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-rowh"><div class="uk-form-label mue-user-info-label uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_USER_GROUP').'</div><div class="uk-form-controls uk-form-controls-text mue-user-info-hdr">'.$this->userinfo->userGroupName.'</div></div>';
if ($i==1) $i=0; else $i=1;
if ($cfg->subscribe){
	if ($numsubs) {
		if (!$sub) {
			echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row'.($i % 2).'"><div class="uk-form-label mue-user-info-label uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_SUB_STATUS').'</div><div class="uk-form-controls uk-form-controls-text mue-user-info-value">Expired</div></div>';
		} else {
			echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row'.($i % 2).'"><div class="uk-form-label mue-user-info-label uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_SUB_STATUS').'</div><div class="uk-form-controls uk-form-controls-text mue-user-info-value">Active '.$sub->daysLeft. ' Day(s) Left';
			echo '</div></div>';
		}
	} else {
		echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row'.($i % 2).'"><div class="uk-form-label mue-user-info-label uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_SUB_STATUS').'</div><div class="uk-form-controls uk-form-controls-text mue-user-info-value">None</div></div>';
	}
}
if ($i==1) $i=0; else $i=1;
echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row'.($i % 2).'"><div class="uk-form-label mue-user-info-label uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_REGISTERED_ON').'</div><div class="uk-form-controls uk-form-controls-text mue-user-info-value">'.$this->userinfo->registerDate.'</div></div>';
if ($i==1) $i=0; else $i=1;
echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row'.($i % 2).'"><div class="uk-form-label mue-user-info-label uk-text-bold">'.JText::_('COM_MUE_USER_PROFILE_LABEL_LAST_UPDATED').'</div><div class="uk-form-controls uk-form-controls-text mue-user-info-value">'.$this->userinfo->lastUpdated.'</div></div>';
foreach ($this->userfields as $f) {
	if ($f->uf_type != "password" && $f->uf_profile) {
		if ($i==1) $i=0; else $i=1;
		$field=$f->uf_sname;
		echo '<div class="uk-form-row uk-margin-top mue-user-info-row mue-row'.($i % 2).'">';
		if ($f->uf_type == "message") {
            echo '<div class="uk-form-label mue-user-info-label uk-text-bold"></div>';
		    echo '<div class="uk-form-controls uk-form-controls-text mue-user-info-value"><div class="uk-alert">'.$f->uf_name.'</div>';
        } else {
			echo '<div class="uk-form-label mue-user-info-label uk-text-bold">' . $f->uf_name . '</div>';
			echo '<div class="uk-form-controls uk-form-controls-text mue-user-info-value">';
			if ($this->userinfo->$field) echo $this->userinfo->$field;
			else echo '&nbsp;';
		}
		echo '</div>';
		echo '</div>';
	}
}
echo '<div style="clear:both;"></div>';
echo '</div>';
echo $cfg->profile_bottom_content;
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>
