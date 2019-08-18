<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.modal', 'a.modal');

$cfg=MUEHelper::getConfig();

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$loggeduser = JFactory::getUser();
$sortFields = $this->getSortFields();

JFactory::getDocument()->addScriptDeclaration('
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != "' . $listOrder . '")
		{
			dirn = "asc";
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, "");
	};
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=users'); ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
<?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
<?php else : ?>
    <div id="j-main-container">
<?php endif;?>
    <?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
	
	<div class="clr clearfix"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="5">
					<?php echo JHtml::_('searchtools.sort', 'COM_MUE_USER_HEADING_ID', 'u.id', $listDirn, $listOrder); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_MUE_USER_HEADING_USERNAME', 'u.username', $listDirn, $listOrder); ?>
				</th>	
				<th width="150">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_USERSNAME' , 'u.name', $listDirn, $listOrder); ?>
				</th>
				<th width="200">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_EMAIL' , 'u.email', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JText::_('COM_MUE_USER_HEADING_GROUP'); ?>
				</th>
				<th width="150">
					<?php echo JText::_('COM_MUE_USER_HEADING_JGROUP'); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_JOINSITE' , 'g.userg_siteurl', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" width="60">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_ENABLED', 'u.block', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_VISIT' , 'u.lastvisitDate', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_UPDATE' , 'ug.userg_update', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('searchtools.sort',  'COM_MUE_USER_HEADING_REGISTERED' , 'u.registerDate', $listDirn, $listOrder); ?>
				</th>
				<?php if ($cfg->subscribe) { ?>
					<th width="150"><?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_SINCE' , 'ug.userg_subsince', $listDirn, $listOrder); ?></th>
					<th width="150"><?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_EXPIRES' , 'ug.userg_subexp', $listDirn, $listOrder); ?></th>
					<th width="150"><?php echo JText::_('COM_MUE_USER_HEADING_SUBSTATUS'); ?></th>
				<?php }	?>
			</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo ($cfg->subscribe) ? '15' : '12'; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
		</tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): 
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $item->id; ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
						<a href="<?php echo JRoute::_('index.php?option=com_mue&task=user.edit&id='.(int) $item->id); ?>">
						<?php echo $item->username; ?></a>
				</td>
				<td class="center small">
					<?php echo $item->name; ?>
				</td>
				<td class="center small">
					<?php echo $item->email; ?>
				</td>
				<td class="center small">
					<?php if (isset($item->userg_group)) echo $this->usergroups[$item->userg_group]; ?>
				</td>
				<td class="center small">
					<?php echo implode('<br />',$item->jgroups); ?>
				</td>
				<td class="center small">
					<?php echo $item->userg_siteurl; ?>
				</td>
				<td class="center small">
				<?php echo JHtml::_('grid.boolean', $i, !$item->block, 'users.unblock', 'users.block'); ?>
				</td>
				<td class="center small">
					<?php echo $item->lastvisitDate; ?>
				</td>
				<td class="center small">
					<?php echo $item->lastUpdate; ?>
				</td>
				<td class="center small">
					<?php echo $item->registerDate; ?>
				</td>
				<?php if ($cfg->subscribe) { ?>
				<td class="center small">
					<?php echo $item->userg_subsince; ?>
				</td>
				<td class="center small">
					<?php echo $item->userg_subexp; ?>
				</td>
				<td class="center small">
					<?php 
						if ($item->sub) {
							if ((int)$item->sub->daysLeft > 0) {
								switch ($item->sub->usrsub_status) {
									case "notyetstarted": echo "Not Yet Started"; break;
									case "verified": echo "Incomplete"; break;
									case "canceled": echo "Canceled"; break;
									case "accepted": echo "Accepted"; break;
									case "pending": echo "Pending"; break;
									case "started": echo "Started"; break;
									case "denied": echo "Denied"; break;
									case "refunded": echo "Refunded"; break;
									case "failed": echo "Failed"; break;
									case "pending": echo "Pending"; break;
									case "reversed": echo "Reversed"; break;
									case "canceled_reversal": echo "Canceled Dispute"; break;
									case "expired": echo "Expired"; break;
									case "voided": echo "Voided"; break;
									case "completed": echo "Active"; break;
									case "dispute": echo "Dispute"; break;
								}
								echo ': '.$item->sub->daysLeft.'  Days Left';
							} else echo 'Expired: '.abs((int)$item->sub->daysLeft).'  Days Ago';
						} else {
							echo 'No Subscription';
						}
					?>
				</td>
		        <?php } ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php 
	// Create the copy/move options.
$options = array(
	JHtml::_('select.option', 'add', JText::_('COM_USERS_BATCH_ADD')),
	JHtml::_('select.option', 'del', JText::_('COM_USERS_BATCH_DELETE')),
	JHtml::_('select.option', 'set', JText::_('COM_USERS_BATCH_SET'))
);
// Create the reset password options.
$resetOptions = array(
	JHtml::_('select.option', '', JText::_('COM_USERS_NO_ACTION')),
	JHtml::_('select.option', 'yes', JText::_('JYES')),
	JHtml::_('select.option', 'no', JText::_('JNO'))
);
JHtml::_('formbehavior.chosen', 'select');
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_USERS_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
		<div class="row-fluid">
			<div id="batch-choose-action" class="combo control-group">
				<label id="batch-choose-action-lbl" class="control-label" for="batch-choose-action">
					<?php echo JText::_('COM_USERS_BATCH_GROUP') ?>
				</label>
			</div>
			<div id="batch-choose-action" class="combo controls">
				<div class="control-group">
					<select name="batch[group_id]" id="batch-group-id">
						<option value=""><?php echo JText::_('JSELECT') ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('user.groups')); ?>
					</select>
				</div>
			</div>
			<div class="control-group radio">
				<?php echo JHtml::_('select.radiolist', $options, 'batch[group_action]', '', 'value', 'text', 'add') ?>
			</div>
		</div>
		<label><?php echo JText::_('COM_USERS_REQUIRE_PASSWORD_RESET'); ?></label>
		<div class="control-group radio">
			<?php echo JHtml::_('select.radiolist', $resetOptions, 'batch[reset_id]', '', 'value', 'text', '') ?>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.getElementById('batch-group-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('user.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


