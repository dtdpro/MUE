<?php 
$config = MUEHelper::getConfig();
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
$user=JFactory::getUser();
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_SUBSCRIBE_CHECK_PAGE_TITLE').'</h2>';
echo '<p>';
if (!$this->print) echo '<a href="'.JRoute::_('index.php?option=com_mue&view=user&layout=subs').'" class="button uk-button">View All Subscription(s)</a>';
if (!$this->print) echo ' <a href="'.JRoute::_('index.php?option=com_mue&view=subscribe&layout=check&plan='.$this->pinfo->sub_id.'&tmpl=component&print=1').'" target="_blank" class="button uk-button">Print</a>';
else echo '<a href="javascript:print()" class="button uk-button">Print</a>';
echo '</p>';

echo '<p>';
echo '<b>Name:</b> '.$user->name;
echo '<br /><b>Email:</b> '.$user->email;
echo '<br /><b>Username:</b> '.$user->username;
echo '<br /><br /><b>Subscription Cost:</b> $'.number_format($this->pinfo->sub_cost,2);
echo '</p>';

echo $config->paybycheck_content;

if ($this->params->get('divwrapper',1)) { echo '</div>'; }

?>