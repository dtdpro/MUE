<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
if (JVersion::MAJOR_VERSION == 3) {
	JHtml::_('bootstrap.tooltip');
	JHtml::_('formbehavior.chosen', 'select');
}

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

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
					    <div class="small"><?php if (isset($item->userg_group)) echo $this->usergroups[$item->userg_group]; ?></div>
				</td>
				<td class="center small">
					<?php echo $item->name; ?>
				</td>
				<td class="center small">
					<?php echo $item->email; ?>
				</td>
				<td class="center small">
					<?php
					if (JVersion::MAJOR_VERSION == 3) {
						echo JHtml::_('grid.boolean', $i, !$item->block, 'users.unblock', 'users.block');
					} else {
						$self = $loggeduser->id == $item->id;
						if ($self) {
							$states = [
								1 => [ 'task'           => 'unblock', 'text'           => '', 'active_title'   => 'COM_USERS_TOOLBAR_BLOCK', 'inactive_title' => '', 'tip'            => true, 'active_class'   => 'unpublish', 'inactive_class' => 'unpublish', ],
								0 => [ 'task'           => 'block', 'text'           => '', 'active_title'   => '', 'inactive_title' => 'COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF', 'tip'            => true, 'active_class'   => 'publish', 'inactive_class' => 'publish', ]
							];
						} else {
							$states = [
								1 => [ 'task'           => 'unblock', 'text'           => '', 'active_title'   => 'COM_USERS_TOOLBAR_UNBLOCK', 'inactive_title' => '', 'tip'            => true, 'active_class'   => 'unpublish', 'inactive_class' => 'unpublish', ],
								0 => [ 'task'           => 'block', 'text'           => '', 'active_title'   => 'COM_USERS_TOOLBAR_BLOCK', 'inactive_title' => '', 'tip'            => true, 'active_class'   => 'publish', 'inactive_class' => 'publish', ]
							];
						}
						echo HTMLHelper::_( 'jgrid.state', $states, $item->block, $i, 'users.', !$self );
					}
					?>
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
									case "verified": echo "Verified"; break;
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


        <input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


