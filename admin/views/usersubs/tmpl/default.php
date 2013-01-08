<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo JRoute::_('index.php?option=com_mue&view=usersubs'); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CONTINUED_SEARCH_IN_PURCHASE'); ?>" />
			<?php 
				echo '<label for="filter_start">Date Range:</label> '.JHTML::_('calendar',$this->state->get('filter.start'),'filter_start','filter_start','%Y-%m-%d','');
				echo ' '.JHTML::_('calendar',$this->state->get('filter.end'),'filter_end','filter_end','%Y-%m-%d','');
			?>
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';document.id('filter_start').value='<?php echo date("Y-m-d",strtotime("-1 months")); ?>';document.id('filter_end').value='<?php echo date("Y-m-d"); ?>';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_plan" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_MUE_USERSUB_SELECT_PLAN');?></option>
				<?php 
					echo $html[] = JHtml::_('select.options',$this->plist,"value","text",$this->state->get('filter.plan')); 
				?>
			</select>
		</div>
	</fieldset>
	
	<div class="clr"> </div>
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('COM_MUE_USERSUB_HEADING_ID'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JText::_('COM_MUE_USERSUB_HEADING_PLAN'); ?>
				</th>	
				<th>
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_USER' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_EMAIL' ); ?>
				</th>
				<th width="75">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_TYPE' ); ?>
				</th>
				<th width="100">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_TRANSINFO' ); ?>
				</th>
				<th width="100">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_TIME' ); ?>
				</th>
				<th width="100">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_START' ); ?>
				</th>
				<th width="100">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_END' ); ?>
				</th>
				<th width="75">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_IP' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MUE_USERSUB_HEADING_STATUS' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="13"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach($this->items as $i => $item):  ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $item->usrsub_id; ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->usrsub_id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_mue&task=usersub.edit&usrsub_id='.(int) $item->usrsub_id); ?>">
					<?php echo $item->sub_exttitle; ?></a><br />( <?php echo $item->sub_inttitle; ?> )
				</td>
				<td>
					<?php echo $item->user_name.' ('.$item->username.')'; ?>
				</td>
				<td>
					<?php echo $item->user_email; ?>
				</td>
				<td>
					<?php 
					switch ($item->usrsub_type) {
						case "paypal": echo "PayPal"; break;
						case "redeem": echo "Code"; break;
						case "admin": echo "Admin"; break;
						case "google": echo "Google"; break;
					}
					?>
				</td>
				<td>
					<?php echo $item->usrsub_rpprofile.'<br />'.$item->usrsub_transid; ?>
				</td>
				<td>
					<?php echo $item->usrsub_time; ?>
				</td>
				<td>
					<?php echo $item->usrsub_start; ?>
				</td>
				<td>
					<?php echo $item->usrsub_end; ?>
				</td>
				<td>
					<?php echo $item->usrsub_ip; ?>
				</td>
				<td>
					<?php echo ($item->usrsub_rpstatus) ? $item->usrsub_rpstatus : "None"; ?>
					<?php 
					echo '<br />';
					switch ($item->usrsub_status) {
						
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
						case "completed": echo "Completed"; break;
						case "dispute": echo "Dispute"; break;
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
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


