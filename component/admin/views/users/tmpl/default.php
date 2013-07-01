<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
$cfg=MUEHelper::getConfig();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=users'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft pull-left">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MUE_SEARCH_IN_USER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt pull-right">
			<select name="filter_ugroup" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_MUE_SELECT_UGROUP');?></option>
				<?php echo $html[] = JHtml::_('select.options',$this->ugroups,"value","text",$this->state->get('filter.ugroup')); ?>
			</select>
			<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value="*"><?php echo JText::_('COM_MUE_USERS_FILTER_STATE');?></option>
				<?php echo JHtml::_('select.options', MUEHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
			</select>
		</div>
	</fieldset>
	
	<div class="clr"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="5">
					<?php echo JHtml::_('grid.sort', 'COM_MUE_USER_HEADING_ID', 'u.id', $listDirn, $listOrder); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_MUE_USER_HEADING_USERNAME', 'u.username', $listDirn, $listOrder); ?>
				</th>	
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_USERSNAME' , 'u.name', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_EMAIL' , 'u.email', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_GROUP' , 'g.ug_name', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JText::_('COM_MUE_USER_HEADING_JGROUP'); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_JOINSITE' , 'g.userg_siteurl', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_ENABLED', 'u.block', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_VISIT' , 'u.lastvisitDate', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_UPDATE' , 'ug.userg_update', $listDirn, $listOrder); ?>
				</th>
				<th width="150">
					<?php echo JHtml::_('grid.sort',  'COM_MUE_USER_HEADING_REGISTERED' , 'u.registerDate', $listDirn, $listOrder); ?>
				</th>
				<?php if ($cfg->subscribe) { ?>
					<th width="150"><?php echo JText::_('COM_MUE_USER_SUBSTATUS'); ?></th>
				<?php }	?>
			</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo ($cfg->subscribe) ? '12' : '11'; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
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
				<td class="center">
					<?php echo $item->name; ?>
				</td>
				<td class="center">
					<?php echo $item->email; ?>
				</td>
				<td class="center">
					<?php echo $item->ug_name; ?>
				</td>
				<td class="center">
					<?php echo implode('<br />',$item->jgroups); ?>
				</td>
				<td class="center">
					<?php echo $item->userg_siteurl; ?>
				</td>
				<td class="center">
				<?php echo JHtml::_('grid.boolean', $i, !$item->block, 'users.unblock', 'users.block'); ?>
				</td>
				<td class="center">
					<?php echo $item->lastvisitDate; ?>
				</td>
				<td class="center">
					<?php echo $item->lastUpdate; ?>
				</td>
				<td class="center">
					<?php echo $item->registerDate; ?>
				</td>
				<?php if ($cfg->subscribe) { ?>
				<td>
					<?php 
						if ($item->sub) {
							if ((int)$item->sub->daysLeft > 0) {
								switch ($item->sub->usrsub_status) {
									case "notyetstarted": echo "Not Yet Started"; break;
									case "verified": echo "Assessment"; break;
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
	$options = array(
	JHtml::_('select.option', 'add', JText::_('COM_USERS_BATCH_ADD')),
	JHtml::_('select.option', 'del', JText::_('COM_USERS_BATCH_DELETE')),
	JHtml::_('select.option', 'set', JText::_('COM_USERS_BATCH_SET'))
	);
	
	?>
	<fieldset class="batch">
		<legend><?php echo JText::_('COM_USERS_BATCH_OPTIONS');?></legend>
		<label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_USERS_BATCH_GROUP') ?></label>
		<fieldset id="batch-choose-action" class="combo">
			<select name="batch[group_id]" class="inputbox" id="batch-group-id">
				<option value=""><?php echo JText::_('JSELECT') ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('user.groups', JFactory::getUser()->get('isRoot'))); ?>
			</select>
			<?php echo JHtml::_('select.radiolist', $options, 'batch[group_action]', '', 'value', 'text', 'add') ?>
		</fieldset>
	
		<button type="submit" onclick="Joomla.submitbutton('user.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
		<button type="button" onclick="document.id('batch-group-id').value=''">
			<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
		</button>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


