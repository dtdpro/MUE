<?php
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
echo 'Information sent';
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>