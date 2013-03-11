<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'p.ordering';
$ordering	= ($listOrder == 'p.ordering');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=uplans'); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>
	
	<div class="clr"> </div>
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('COM_MUE_UPLAN_HEADING_ID'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JText::_('COM_MUE_UPLAN_HEADING_TITLE'); ?>
				</th>			
				<th width="100">
					<?php echo JText::_('COM_MUE_UPLAN_HEADING_COST'); ?>
				</th>			
				<th width="100">
					<?php echo JText::_('COM_MUE_UPLAN_HEADING_PERIOD'); ?>
				</th>	
				<th width="100">
					<?php echo JText::_('JPUBLISHED'); ?>
				</th>	
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'o.ordering', $listDirn, $listOrder); ?>
					<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'uplans.saveorder'); ?>
				</th>
				<th width="100">
					<?php echo JHtml::_('grid.sort','JGRID_HEADING_ACCESS','ug.access', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $item->sub_id; ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->sub_id); ?>
				</td>
				<td>
						<a href="<?php echo JRoute::_('index.php?option=com_mue&task=uplan.edit&sub_id='.(int) $item->sub_id); ?>">
						<?php echo $this->escape($item->sub_exttitle); ?></a>
				</td>
				<td>
					<?php echo '$'.$item->sub_cost; ?>
				</td>
				<td>
					<?php echo $item->sub_length.' '.$item->sub_period; if ($item->sub_recurring) echo ' Recurrung'; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'uplans.', true);?>
				</td>
				<td class="order">
						<?php if ($saveOrder) :?>
							<?php if ($listDirn == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, (1), 'uplans.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (1), 'uplans.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($listDirn == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, (1), 'uplans.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (1), 'uplans.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
		
				</td>
				<td>
					<?php echo $item->access_level; ?>
				</td>
			
			</tr>
		<?php endforeach; ?></tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

