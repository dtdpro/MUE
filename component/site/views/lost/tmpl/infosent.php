<?php
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
echo JText::_('COM_MUE_LOST_INFOSENT_INSTRUCTIONS');
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>