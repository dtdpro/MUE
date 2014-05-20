<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'user.cancel' || document.formvalidator.isValid(document.id('mue-form'))) {
			Joomla.submitform(task, document.getElementById('mue-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mue&layout=edit&usr_id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="mue-form" class="form-validate">
<div class="row-fluid">
	<div class="width-50 fltlft span6">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MUE_USER_DETAILS' ); ?></legend>
			<ul class="adminformlist treeselect">
				<li><label id="jform_usergroup-lbl" for="jform_usergroup" class="hasTip" title="Group::Users' Group">MUE Group</label>
				<select id="jform_usergroup" name="jform[usergroup]" class="inputbox" size="1">
				<?php echo JHtml::_('select.options',$this->usergroups,"value","text",$this->item->usergroup); ?>
				</select>
				</li>
				<?php foreach($this->fields as $f) {
					echo '<li>';
					$sname = $f->uf_sname;
					//field title
					echo '<label id="jform_'.$sname.'-lbl" for="jform_'.$sname.'" class="hasTip" title="'.$f->uf_name.'::">'.$f->uf_name.'</label>';
					
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
						echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="inputbox" size="1">';
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
						echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->item->$sname.'" class="inputbox" size="70" type="text">';
					}
					
					//password
					if ($f->uf_type=="password") {
						echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->item->$sname.'" class="inputbox" size="20" type="password">';
					}
					
					//text area
					if ($f->uf_type=="textar") {
						echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="inputbox">'.$this->item->$sname.'</textarea>';
					}
					
					//Yes no
					if ($f->uf_type=="yesno" || $f->uf_type=="cbox" || $f->uf_type=="mailchimp" || $f->uf_type=="cmlist") {
						echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="inputbox" size="1">';
						$selected = ' selected="selected"';
						echo '<option value="1"';
						echo ($this->item->$sname == "1") ? $selected : '';
						echo '>Yes</option>';
						echo '<option value="0"';
						echo ($this->item->$sname == "0") ? $selected : '';
						echo '>No</option>';
						
						echo '</select>';
					}
				
					echo '</li>';
				} ?>
			</ul>
		</fieldset>

			<p>Note: If mailing lists are changed to yes, a list based confirmation WILL be sent to the user, change will not be reflected until user confirms</p>
	</div>
	<div class="width-50 fltlft span6">
		<fieldset class="adminform">
			<legend>Joomla User Group</legend>
				
					<?php echo JHtml::_('access.usergroups', 'jform[groups]', $this->groups, true); ?>
				

		</fieldset>
	</div>
	<div class="width-50 fltlft span6">
		<fieldset class="adminform">
			<legend>Other User Info</legend>
			<?php 
				echo '<label for="jform_lastupdate">Last Updated:</label> '.$this->item->lastupdate.'<br /><br />';//JHTML::_('calendar',$this->item->lastupdate,'jform[lastupdate]','jform_lastupdate','%Y-%m-%d','').'<br /><br />';
				echo '<label for="jform_usersiteurl">Join URL:</label> <input name="jform[usersiteurl]" id="jform_usersiteurl" value="'.$this->item->usersiteurl.'" type="text" class="inputbox" size="30"><br /><br />';
				echo '<label for="jform_usernotes">User Notes:</label> <br /><textarea name="jform[usernotes]" id="jform_usernotes" class="inputbox" style="width:100%;height:400px;font-size:10px;">'.$this->item->usernotes.'</textarea>';
				
					
			?>
		</fieldset>
	</div>

		<input type="hidden" name="task" value="user.edit" />
		<input type="hidden" name="usr_user" value="<?php echo $this->item->usr_user; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

