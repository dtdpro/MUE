<?php // no direct access
defined('_JEXEC') or die('Restricted access');
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
?>
<?php
echo '<h2 class="componentheading uk-article-title">'.JText::_('COM_MUE_USERACTIVATION_PAGE_TITLE').'</h2>';
echo '<p>'.$this->completeMessage.'</p>';

if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>
