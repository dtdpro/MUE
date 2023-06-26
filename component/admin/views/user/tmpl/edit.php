<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
if (JVersion::MAJOR_VERSION == 3) JHtml::_('formbehavior.chosen', 'select');


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
    {
        if (task == 'user.cancel' || document.formvalidator.isValid(document.getElementById('mue-form'))) {
            Joomla.submitform(task, document.getElementById('mue-form'));
        }
    }

</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&usr_id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
	<?php if (JVersion::MAJOR_VERSION == 4) { ?><div class="form-horizontal main-card"><?php } ?>
		<?php
		if (JVersion::MAJOR_VERSION == 4) {
			echo HTMLHelper::_('uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
			echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'User Details');
		} else {
			echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general'));
			echo JHtml::_('bootstrap.addTab', 'myTab', 'general', 'User Details');
		}
		?>


        <div class="row-fluid row">
	<div class="width-50 fltlft span6 col-md-6">
		<fieldset class="adminform form-horizontal">
			<legend><?php echo JText::_( 'COM_MUE_USER_DETAILS' ); ?></legend>
			
				<div class="control-group">
					<div class="control-label">
						<label id="jform_usergroup-lbl" for="jform_usergroup" class="hasTip" title="Group::Users' Group">MUE Group</label>
					</div>
					<div class="controls">
						<select id="jform_usergroup" name="jform[usergroup]" class="form-select inputbox" size="1">
						<?php echo JHtml::_('select.options',$this->usergroups,"value","text",$this->item->usergroup); ?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label data-original-title="<strong>Require Password Reset</strong><br />Setting this option to yes requires the user to reset their password the next time they log into the site." id="jform_requireReset-lbl" for="jform_requireReset" class="hasTooltip" title="">Require Password Reset</label>
					</div>
					<div class="controls"><?php 
						echo '<select id="jform_requireReset" name="jform[requireReset]" class="form-select inputbox" size="1">';
						$selected = ' selected="selected"';
						echo '<option value="0"';
						echo ($this->item->requireReset == "0") ? $selected : '';
						echo '>No</option>';
						echo '<option value="1"';
						echo ($this->item->requireReset == "1") ? $selected : '';
						echo '>Yes</option>';
						
						echo '</select>';
						?>
					</div>
				</div>
				<?php foreach($this->fields as $f) {
					echo '<div class="control-group">';
					$sname = $f->uf_sname;
					//field title
					echo '<div class="control-label">';
					echo '<label id="jform_'.$sname.'-lbl" for="jform_'.$sname.'" class="hasTip" title="'.$f->uf_name.'::">'.$f->uf_name.'</label>';
					echo '</div>';
					echo '<div class="controls">';
					//multi checkbox
					if ($f->uf_type=="mcbox" || $f->uf_type=="mlist") {
						echo '<fieldset id="jform_'.$sname.'" class="radio inputbox">';
						foreach ($f->options as $o) {
							if (!empty($this->item->$sname)) $checked = in_array($o->value,$this->item->$sname) ? ' checked="checked"' : '';
							else $checked = '';
							echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" id="jform_'.$sname.$o->value.'"'.$checked.'/>'."\n";
							echo '<label for="jform_'.$sname.$o->value.'">';
							echo ' '.$o->text.'</label><br /><br />'."\n";
							
						}
						echo '</fieldset>';
					}
	
					//dropdown, radio
					if ($f->uf_type=="multi" || $f->uf_type=="dropdown") {
						echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-select inputbox" size="1">';
						foreach ($f->options as $o) {
							if (!empty($this->item->$sname)) $selected = ($o->value == $this->item->$sname) ? ' selected="selected"' : '';
							else $selected = '';
							echo '<option value="'.$o->value.'"'.$selected.'>';
							echo ' '.$o->text.'</option>';
						}
						echo '</select>';
					}
					
					//text field, phone #, email, username, birthday
					if ($f->uf_type=="textbox" || $f->uf_type=="email" || $f->uf_type=="username" || $f->uf_type=="phone" || $f->uf_type=="birthday") {
						echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->item->$sname.'" class="form-control inputbox" size="70" type="text">';
					}
					
					//password
					if ($f->uf_type=="password") {
						echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->item->$sname.'" class="form-control inputbox" size="20" type="password">';
					}
					
					//text area
					if ($f->uf_type=="textar") {
						echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="form-control inputbox">'.$this->item->$sname.'</textarea>';
					}
					
					//Yes no
					if ($f->uf_type=="yesno" || $f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist" || $f->uf_type=="brlist") {
						echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-select inputbox" size="1">';
						$selected = ' selected="selected"';
						echo '<option value="0"';
						echo ($this->item->$sname == "0") ? $selected : '';
						echo '>No</option>';
						echo '<option value="1"';
						echo ($this->item->$sname == "1") ? $selected : '';
						echo '>Yes</option>';
						
						echo '</select>';
					}

					//password
					if ($f->uf_type=="timezone") {
						echo 'Editable with Joomla user manager';
					}

					//password
					if ($f->uf_type=="aclist" || $f->uf_type=="mclist") {
						echo 'User editable only';
					}

					echo '</div>';
					echo '</div>';
				} ?>
			
		</fieldset>
	</div>
	<div class="width-50 fltlft span6 col-md-6 form-horizontal">
		<fieldset class="adminform">
			<legend>Joomla User Group</legend>
            <?php echo JHtml::_('access.usergroups', 'jform[groups]', $this->groups, true); ?>
		</fieldset>
        <fieldset class="adminform">
            <legend>Other User Info</legend>
			<?php
			echo '<label for="jform_lastupdate">Last Updated:</label> '.$this->item->lastupdate.'<br /><br />';//JHTML::_('calendar',$this->item->lastupdate,'jform[lastupdate]','jform_lastupdate','%Y-%m-%d','').'<br /><br />';
			echo '<label for="jform_usersiteurl">Join URL:</label> <input name="jform[usersiteurl]" id="jform_usersiteurl" value="'.$this->item->usersiteurl.'" type="text" class="form-control inputbox" size="30"><br /><br />';
			echo '<label for="jform_usernotes">User Notes:</label> <br /><textarea name="jform[usernotes]" id="jform_usernotes" class="form-control inputbox" style="width:100%;height:400px;font-size:10px;">'.$this->item->usernotes.'</textarea>';


			?>
        </fieldset>
	</div>
</div>

        <?php

		if ( JVersion::MAJOR_VERSION == 4 ) {
				echo HTMLHelper::_('uitab.endTab');
			echo HTMLHelper::_( 'uitab.endTabSet' );
		} else {
				echo JHtml::_('bootstrap.endTab');
			echo JHtml::_( 'bootstrap.endTabSet' );
		}

        if (JVersion::MAJOR_VERSION == 4) { ?></div><?php }

        ?>

    <input type="hidden" name="task" value="user.edit" />
		<input type="hidden" name="usr_user" value="<?php echo $this->item->usr_user; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>

