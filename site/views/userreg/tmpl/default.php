<div id="system">
<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$cecfg = MUEHelper::getConfig();

?>
<script type="text/javascript">
	/*jQuery(document).ready(function() {
		jQuery.metadata.setType("attr", "validate");
		jQuery("#regpickform").validate({
			errorClass:"uf_pickerror",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    }
	    });

	
	});
*/
	function submitGroup() {
		var $form = jQuery( this ),
	        url = $form.attr( 'action' );
	    /* Send the data using post and put the results in a div */
	    jQuery.post( url, jQuery("#regpickform").serialize(),
	      function( data ) {
	          
	          jQuery( "#mue-user-regform" ).empty().append( data );
	      }
	    );
	}
</script>
<h2 class="componentheading">User Registration</h2>
<?php 
echo $cecfg->REG_PAGE_CONTENT;
echo '<div id="mue-user-reg">';
$first = true;
echo '<div class="mue-user-reg-row">';
echo '<div class="mue-user-reg-label">User Group</div>';
echo '<div class="mue-user-reg-hdr">';
echo '<form action="'.JRoute::_("index.php?option=com_mue&view=userreg&tmpl=raw").'" method="post" name="regpickform" id="regpickform" class="">';
echo '<select name="groupid" id="groupid" class="required" onchange="submitGroup()">';
echo '<option value="">- Select Group -</option>';
foreach ($this->groups as $g) {
	echo '<option value="'.$g->ug_id.'">';
	echo $g->ug_name.'</option>'; 	
}
echo '</select>';
echo '<input type="hidden" name="option" value="com_mue">';
echo '<input type="hidden" name="view" value="userreg">';
echo '<input type="hidden" name="layout" value="groupuser">';
echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
echo JHtml::_('form.token');
echo '</form>';
echo '</div>';
echo '</div>';
//echo '<div class="mue-user-reg-submit">';
//echo '</div>';
//echo '<div class="mue-user-reg-submit">';
//echo '<input type="submit" value="Begin Registration" class="button" border="0" name="submit">';
//echo '</div>';
echo '<div style="clear:both;"></div>';
echo '<div id="mue-user-regform"></div>';
echo '</div>';
//echo '<div id="mue-userreg-form"></div>';
?>
</div>



