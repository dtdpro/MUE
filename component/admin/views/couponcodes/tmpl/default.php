<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
if (JVersion::MAJOR_VERSION == 3) {
	JHtml::_('bootstrap.tooltip');
	JHtml::_('formbehavior.chosen', 'select');
}

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;

$published = $this->state->get('filter.published');

$sortFields = $this->getSortFields();
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=couponcodes'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

        <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	
	<div class="clearfix"> </div>
	
	<table class="adminlist table table-striped" id="MOWSCouponCodeList">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>		
				<th width="1%" style="min-width:55px" class="nowrap center">
					<?php echo JText::_('JSTATUS'); ?>
				</th>	
				<th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_CODE'); ?>
				</th>	
				<th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_TYPE'); ?>
				</th>	
				<th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_VALUE'); ?>
				</th>	
				<th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_START'); ?>
				</th>	
				<th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_END'); ?>
				</th>
                <th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_PLANS'); ?>
                </th>
                <th>
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_USECOUNT'); ?>
                </th>
                <th width="1%">
					<?php echo JText::_('COM_MUE_COUPONCODE_HEADING_ID'); ?>
				</th>	
			</tr>
		
		
		</thead>
		<tfoot><tr><td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td><?php echo JHtml::_('grid.id', $i, $item->cu_id); ?></td>
				<td class="center"><?php echo JHtml::_('jgrid.published', $item->published, $i, 'couponcodes.', true);?></td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_mue&task=couponcode.edit&cu_id='.(int) $item->cu_id); ?>">
					<?php echo $this->escape($item->cu_code); ?></a>
				</td>
				<td><?php echo $item->cu_type; ?></td>
				<td><?php echo $item->cu_value; ?></td>
				<td><?php echo $item->cu_start; ?></td>
				<td><?php echo $item->cu_end; ?></td>
                <td><?php echo $item->cu_plans; ?></td>
                <td><?php echo $item->use_count; ?></td>
				<td><?php echo $item->cu_id; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


