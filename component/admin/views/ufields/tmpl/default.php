<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'f.ordering';
$ordering	= ($listOrder == 'f.ordering');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=ufields'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar pull-left">
		<div class="filter-search fltlft">
			
		</div>
		<div class="filter-select fltrt pull-right">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>
	
	<div class="clr"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('COM_MUE_UFIELD_HEADING_ID'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JText::_('COM_MUE_UFIELD_HEADING_TITLE'); ?>
				</th>	
				<th width="100">
					<?php echo JText::_('JPUBLISHED'); ?>
				</th>	
				<th width="100">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'f.ordering', $listDirn, $listOrder); ?>
					<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'ufields.saveorder'); ?>
				</th>
				<th width="75">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_TYPE' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_REQUIRED' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_REG' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_PROFILE' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_HIDDEN' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_CHANGE' ); ?>
				</th>
				<th width="75">
					<?php echo JText::_( 'COM_MUE_UFIELD_HEADING_OPTIONS' ); ?>
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
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $item->uf_id; ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->uf_id); ?>
				</td>
				<td>
						<a href="<?php echo JRoute::_('index.php?option=com_mue&task=ufield.edit&uf_id='.(int) $item->uf_id); ?>">
						<?php echo $item->uf_name; ?></a> ( <?php echo $item->uf_sname; ?> )
				</td>
				<td class="center">
					<?php if ($item->uf_id > 9) echo JHtml::_('jgrid.published', $item->published, $i, 'ufields.', true);?>
				</td>
		        <td class="order">	<div class="input-prepend">
						<?php if ($saveOrder) :?>
							<?php if ($listDirn == 'asc') : ?>
								<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, true, 'ufields.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'ufields.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($listDirn == 'desc') : ?>
								<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, true, 'ufields.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'ufields.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="width-20 text-area-order" />
		
				</div></td>
				<td>
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
					}
					?>
				</td>
				<td>
					<?php 
					if ($item->uf_req) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td>
					<?php 
					if ($item->uf_reg) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td>
					<?php 
					if ($item->uf_profile) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td>
					<?php 
					if ($item->uf_hidden) echo '<span style="color:#008800">Yes</span>';
					else echo '<span style="color:#880000">No</span>'; 
					?>
				</td>
				<td>
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
				
				?>
				</td>
			
			</tr>
		<?php endforeach; ?>
		
		</tbody>
	</table>
	
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_area" value="<?php echo $this->state->get('filter.area'); ?>" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


