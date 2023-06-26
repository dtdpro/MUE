<?php defined('_JEXEC') or die('Restricted access'); ?>
<style>
.muecom-opt-bar-box { background-color: #777777; width: 100%; height: 10px; float:left;}
.muecom-opt-bar-bar { height: 10px; }
</style>
<?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
<?php else : ?>
    <div id="j-main-container">
<?php endif;?>
<?php
$db = JFactory::getDBO();
		
	foreach ($this->fdata as $f) {
		$anscor=false;
		switch ($f->uf_type) {
			case 'multi':
			case 'dropdown':
				echo '<h3>'.$f->uf_name.'</h3>';
				echo '<table class="adminlist table table-striped" width="100%">';
				echo '<thead><tr><th align="left" width="30%">Option</th><th width="10%">Count</th><th></th></tr></thead>';
				echo '<tfoot><tr><td colspan="4"></td></tr></tfoot>';
				echo '<tbody>';
				$qnum = 'SELECT count(usr_field) FROM #__mue_users WHERE usr_field = '.$f->uf_id.' GROUP BY usr_field';
				$db->setQuery( $qnum );
				$numr = (int)$db->loadResult();
				$query  = 'SELECT o.* FROM #__mue_ufields_opts as o ';
				$query .= 'WHERE o.opt_field = '.$f->uf_id.' ORDER BY ordering ASC';
				$db->setQuery( $query );
				$qopts = $db->loadObjectList();
				$tph=0;
				foreach ($qopts as &$o) {
					$qa = 'SELECT count(*) FROM #__mue_users WHERE usr_field = '.$f->uf_id.' && usr_data = '.$o->opt_id.' GROUP BY usr_data';
					$db->setQuery($qa);
					$o->anscount = $db->loadResult();
					if ($o->anscount == "") $o->anscount = 0;
				}
				foreach ($qopts as $opts) {
					if ($numr != 0) $per = ($opts->anscount+$opts->prehits)/($numr+$tph); else $per=1;
					echo '<tr>';
					
					echo '<td>';
					echo $opts->opt_text;
					echo '</td>';
					echo '<td>';
					echo ($opts->anscount);
					echo '</td><td>';
					echo '<div class="muecom-opt-bar-box"><div class="muecom-opt-bar-bar" style="background-color: #000099; width:'.($per*100).'%"></div></div>';
					echo '</td></tr>';
				}
				echo '</tbody></table>';
				break;
			case 'message':
				echo '<h2>'.$f->uf_name.'</h2>';
				break;
				
		}
		echo '<p>&nbsp;</p>';
	}
	
?>
        </div>