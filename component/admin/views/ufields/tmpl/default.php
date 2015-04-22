<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app	= JFactory::getApplication();
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$published = $this->state->get('filter.published');
$saveOrder = ($listOrder == 'f.ordering');
$sortFields = $this->getSortFields();
if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_mue&task=ufields.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'MUEFieldList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
<script type="text/javascript">
    Joomla.orderTable = function()
    {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>')
        {
            dirn = 'asc';
        }
        else
        {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
</script>


<form action="<?php echo JRoute::_('index.php?option=com_mue&view=ufields'); ?>" method="post" name="adminForm" id="adminForm">
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

        <div class="clearfix"> </div>
	
	<table class="adminlist table table-striped" id ="MUEFieldList">
		<thead>
			<tr>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHtml::_('searchtools.sort', '', 'f.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                </th>
                <th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>	
				<th width="1%">
					<?php echo JText::_('JSTATUS'); ?>
				</th>		
				<th>
					<?php echo JText::_('COM_MUE_UFIELD_HEADING_TITLE'); ?>
				</th>	
				<th width="10%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_TYPE' ); ?>
				</th>
				<th width="1%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_REQUIRED' ); ?>
				</th>
				<th width="1%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_REG' ); ?>
				</th>
				<th width="1%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_PROFILE' ); ?>
				</th>
				<th width="1%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_HIDDEN' ); ?>
				</th>
				<th width="1%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_CHANGE' ); ?>
				</th>
				<th width="10%">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_OPTIONS' ); ?>
				</th>
                <th width="1%">
					<?php echo JText::_('COM_MUE_UFIELD_HEADING_ID'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>" sortable-group-id="muefields">
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
					<?php echo JHtml::_('grid.id', $i, $item->uf_id); ?>
				</td>
				<td class="center">
					<?php if ($item->uf_id > 8) echo JHtml::_('jgrid.published', $item->published, $i, 'ufields.', true);?>
				</td>
				<td>
						<a href="<?php echo JRoute::_('index.php?option=com_mue&task=ufield.edit&uf_id='.(int) $item->uf_id); ?>">
						<?php echo $item->uf_name; ?></a> ( <?php echo $item->uf_sname; ?> )
				</td>
				<td class="small">
					<?php 
					switch ($item->uf_type) {
						case "textar": echo "Text Box"; break;
						case "textbox": echo "Text Field"; break;
						case "email": echo "Email"; break;
						case "username": echo "Username"; break;
						case "multi": echo "Radio Select"; break;
						case "mlist": echo "MultiSelect List"; break;
						case "cbox": echo "Check Box"; break;
						case "mcbox": echo "Multi Checkbox"; break;
						case "yesno": echo "Yes / No"; break;
						case "dropdown": echo "Drop Down"; break;
						case "message": echo "Message"; break;
						case "phone": echo "Phone"; break;
						case "password": echo "Password"; break;
						case "birthday": echo "Birthday"; break;
						case "captcha": echo "Captcha"; break;
						case "mailchimp": echo "MailChimp List"; break;
						case "cmlist": echo 'Campaign Monitor List'; break;
                        case "brlist": echo 'Bronto Mail List'; break;
					}
					?>
				</td>
				<td class="small">
					<?php 
					if ($item->uf_req) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td class="small">
					<?php 
					if ($item->uf_reg) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td class="small">
					<?php 
					if ($item->uf_profile) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td class="small">
					<?php 
					if ($item->uf_hidden) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td class="small">
					<?php 
					if ($item->uf_change) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td>
				<?php 
					if ($item->uf_type=='mlist' || $item->uf_type=='multi' || $item->uf_type=='mcbox' || $item->uf_type=='dropdown') {
						echo '<a href="'.JRoute::_('index.php?option=com_mue&view=uopts&filter_field='.$item->uf_id).'">Options'; 
						$db =& JFactory::getDBO();
						$query = 'SELECT count(*) FROM #__mue_ufields_opts WHERE opt_field="'.$item->uf_id.'"';
						$db->setQuery( $query );
						echo ' ['.$db->loadResult().']</a>'; 
					}
					if ($item->uf_type=="mailchimp") {
						if ($item->uf_default) {
							JHtml::_('behavior.modal', 'a.modal');
							$link = 'index.php?option=com_mue&amp;view=mclist&amp;tmpl=component&amp;field='.$item->uf_id;
							echo '<a class="modal" title="Edit List Options"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">List Options</a>';
						} else {
							echo "LIST NOT SET";
						}
					}
					if ($item->uf_type=="cmlist") {
						if ($item->uf_default) {
							JHtml::_('behavior.modal', 'a.modal');
							$link = 'index.php?option=com_mue&amp;view=cmlist&amp;tmpl=component&amp;field='.$item->uf_id;
							echo '<a class="modal" title="Edit List Options"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">List Options</a>';
						} else {
							echo "LIST NOT SET";
						}
					}
                    if ($item->uf_type=="brlist") {
                        if ($item->uf_default) {
                            JHtml::_('behavior.modal', 'a.modal');
                            $link = 'index.php?option=com_mue&amp;view=brlist&amp;tmpl=component&amp;field='.$item->uf_id;
                            echo '<a class="modal" title="Edit List Options"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">List Options</a>';
                        } else {
                            echo "LIST NOT SET";
                        }
                    }
				
				?>
				</td>
                <td>
					<?php echo $item->uf_id; ?>
				</td>
			
			</tr>
		<?php endforeach; ?>
		
		</tbody>
	</table>
	

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


