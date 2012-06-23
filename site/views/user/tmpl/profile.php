<div id="system">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
	?>
<h2 class="componentheading">User Profile</h2>
<?php 
echo '<p><a href='.JRoute::_("index.php?option=com_mue&view=user&layout=proedit").'" class="button">';
echo 'Edit Profile</a>';

echo '<div id="mue-user-info">';
echo '<div class="mue-user-info-row"><div class="mue-user-info-label">User Group</div><div class="mue-user-info-hdr">'.$this->userinfo->userGroupName.'</div></div>';
echo '<div class="mue-user-info-row"><div class="mue-user-info-label">Registered on</div><div class="mue-user-info-value">'.$this->userinfo->registerDate.'</div></div>';
echo '<div class="mue-user-info-row"><div class="mue-user-info-label">Last Updated</div><div class="mue-user-info-value">'.$this->userinfo->lastUpdated.'</div></div>';
foreach ($this->userfields as $f) {
	if ($f->uf_type != "password" && $f->uf_profile) {
		$field=$f->uf_sname;
		echo '<div class="mue-user-info-row">';
		echo '<div class="mue-user-info-label">'.$f->uf_name.'</div>';
		echo '<div class="mue-user-info-value">'.$this->userinfo->$field.'</div>';
		echo '</div>';
	}
}
echo '<div style="clear:both;"></div>';
echo '</div>';
?>
</div>