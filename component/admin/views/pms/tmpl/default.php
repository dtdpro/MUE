<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
if (JVersion::MAJOR_VERSION == 3) {
	JHtml::_('bootstrap.tooltip');
	JHtml::_('formbehavior.chosen', 'select');
}

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=pms'); ?>" method="post" name="adminForm" id="adminForm">
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
					<?php echo "Subject" ?>
				</th>	
				<th>
					<?php echo "From" ?>
				</th>	
				<th>
					<?php echo "To" ?>
				</th>	
				<th>
					<?php echo "Date" ?>
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
				<td><?php echo JHtml::_('grid.id', $i, $item->msg_id); ?></td>
				<td class="center"><?php echo $item->msg_status; ?></td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_mue&view=pm&msg_id='.(int) $item->msg_id); ?>">
					<?php echo $this->escape($item->msg_subject); ?></a>
				</td>
				<td><?php echo $item->fromName; ?></td>
				<td><?php echo $item->toName; ?></td>
				<td><?php echo $item->msg_date; ?></td>
				<td><?php echo $item->msg_id; ?></td>
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


