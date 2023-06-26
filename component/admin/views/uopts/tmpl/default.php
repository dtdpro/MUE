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
$saveOrder	= $listOrder == 'o.ordering';
$ordering	= ($listOrder == 'o.ordering');
$sortFields = $this->getSortFields();
if ($saveOrder) {
	if (JVersion::MAJOR_VERSION == 3) {
		$saveOrderingUrl = 'index.php?option=com_mue&task=uopts.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'MUEOptionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	} else {
		$saveOrderingUrl = 'index.php?option=com_mue&task=uopts.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
		HTMLHelper::_('draggablelist.draggable');
	}
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=uopts'); ?>" method="post" name="adminForm" id="adminForm">
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
	
	<table class="adminlist table table-striped" id ="MUEOptionList">
		<thead>
			<tr>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHtml::_('searchtools.sort', '', 'o.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                </th>
                <th width="1%">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                </th>
                <th width="1%">
                    <?php echo JText::_('JPUBLISHED'); ?>
                </th>
				<th>
					<?php echo JText::_('COM_MUE_UOPT_HEADING_TITLE'); ?>
				</th>
                <th width="1%">
                    <?php echo JText::_('COM_MUE_UOPT_HEADING_ID'); ?>
                </th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
        <tbody <?php if (JVersion::MAJOR_VERSION == 4) { ?>class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php } ?>>
		<?php foreach($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>" <?php if (JVersion::MAJOR_VERSION == 3) { ?>sortable-group-id="mueoptions" <?php } else { ?>data-draggable-group="mueoptions"<?php } ?>>
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
					<?php echo JHtml::_('grid.id', $i, $item->opt_id); ?>
				</td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'uopts.', true);?>
                </td>
				<td>
						<a href="<?php echo JRoute::_('index.php?option=com_mue&task=uopt.edit&opt_id='.(int) $item->opt_id); ?>">
						<?php echo $this->escape($item->opt_text); ?></a>
				</td>
                <td>
                    <?php echo $item->opt_id; ?>
                </td>
			
			</tr>
		<?php endforeach; ?></tbody>
	</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_field" value="<?php echo $this->field->uf_id; ?>">
		<?php echo JHtml::_('form.token'); ?>
</div>
</form>


