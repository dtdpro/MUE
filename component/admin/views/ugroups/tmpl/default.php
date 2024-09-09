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

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'ug.ordering';
$ordering	= ($listOrder == 'ug.ordering');
$sortFields = $this->getSortFields();
if ($saveOrder) {
	if (JVersion::MAJOR_VERSION == 3) {
		$saveOrderingUrl = 'index.php?option=com_mue&task=ugroups.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'MUEGroupList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	} else {
		$saveOrderingUrl = 'index.php?option=com_mue&task=ugroups.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
		HTMLHelper::_('draggablelist.draggable');
	}
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=ugroups'); ?>" method="post" name="adminForm" id="adminForm">
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
	
	<div class="clr"> </div>
	
	<table class="adminlist table table-striped" id="MUEGroupList">
		<thead>
			<tr>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHtml::_('searchtools.sort', '', 'ug.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                </th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>
                <th width="100">
                    <?php echo JText::_('JPUBLISHED'); ?>
                </th>
                <th>
					<?php echo JHtml::_('searchtools.sort','COM_MUE_UGROUP_HEADING_NAME','ug.ug_name', $listDirn, $listOrder); ?>
				</th>
				<th width="100">
					<?php echo JHtml::_('searchtools.sort','JGRID_HEADING_ACCESS','ug.access', $listDirn, $listOrder); ?>
				</th>
                <th width="1%">
                    <?php echo JText::_('COM_MUE_UGROUP_HEADING_ID'); ?>
                </th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
        <tbody <?php if (JVersion::MAJOR_VERSION >= 4) { ?>class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php } ?>>
		<?php foreach($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>" <?php if (JVersion::MAJOR_VERSION == 3) { ?>sortable-group-id="muegroup" <?php } else { ?>data-draggable-group="muegroup"<?php } ?>>
                <td class="order nowrap center hidden-phone">
                    <?php
                    $disableClassName = '';
                    $disabledLabel	  = '';
                    if (!$saveOrder) :
                        $disabledLabel    = JText::_('JORDERINGDISABLED');
                        $disableClassName = 'inactive tip-top';
                    endif; ?>
                    <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />

                </td>
                <td>
					<?php echo JHtml::_('grid.id', $i, $item->ug_id); ?>
				</td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'ugroups.', true);?>
                </td>
				<td>
						<a href="<?php echo JRoute::_('index.php?option=com_mue&task=ugroup.edit&ug_id='.(int) $item->ug_id); ?>">
						<?php echo $this->escape($item->ug_name); ?></a>
				</td>
				<td>
					<?php echo $item->access_level; ?>
				</td>
                <td>
                    <?php echo $item->ug_id; ?>
                </td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


