<div id="system">
<?php // no direct access
// Based upon: https://developers.google.com/maps/articles/phpsqlsearch_v3
defined('_JEXEC') or die('Restricted access');
$cecfg = MUEHelper::getConfig();

?>
<script type="text/javascript">
	var map;
	var markers = [];
	var infoWindow;
	var locationSelect;

	jQuery(document).ready(function() {
		map = new google.maps.Map(document.getElementById("map"), {
			center: new google.maps.LatLng(40, -100),
			zoom: 3,
			mapTypeId: 'roadmap',
			mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
		});
		infoWindow = new google.maps.InfoWindow();
		jQuery.metadata.setType("attr", "validate");
		jQuery("#userdirform").validate({
			errorClass:"uf_error",
			validClass:"uf_valid",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").next("div") );
		    },
			submitHandler: function(form) {
				searchLocations();
			}
		});
	});


	function searchLocations() {
		var address = document.getElementById("addressInput").value;
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({address: address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				searchLocationsNear(results[0].geometry.location);
			} else {
				alert(address + ' not found');
			}
		});
	}

	function clearLocations() {
		infoWindow.close();
		for (var i = 0; i < markers.length; i++) {
			markers[i].setMap(null);
		}
		markers.length = 0;
		
		jQuery( "#nearbylist" ).empty();
	}

	function searchLocationsNear(center) {
		clearLocations(); 
		document.getElementById("lat").value = center.lat();
		document.getElementById("lng").value = center.lng();
		var searchUrl = '<?php echo JURI::base( true ); ?>/components/com_mue/helpers/userdirsearch.php';
		jQuery.post( searchUrl, jQuery("#userdirform").serialize(),
			function( data ) {
				var xml = parseXml(data);
				var markerNodes = xml.documentElement.getElementsByTagName("marker");
				var bounds = new google.maps.LatLngBounds();
				if (markerNodes.length > 0) {
					for (var i = 0; i < markerNodes.length; i++) {
						var name = markerNodes[i].getAttribute("name");
						var userinfo = markerNodes[i].getAttribute("userinfo");
						var distance = parseFloat(markerNodes[i].getAttribute("distance"));
						var latlng = new google.maps.LatLng(
						parseFloat(markerNodes[i].getAttribute("lat")),
						parseFloat(markerNodes[i].getAttribute("lng")));
						
						createOption(name, distance, i);
						createMarker(latlng, name, userinfo);
						bounds.extend(latlng);
					}
					map.fitBounds(bounds);
				} else {
					var nodata = '<div class="nearbymember">No results in search criteria</div>';
					jQuery( "#nearbylist" ).append(nodata);
					map.setOptions({
						center: new google.maps.LatLng(40, -100),
						zoom: 3,
						mapTypeId: 'roadmap',
						mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
					});
					
				}
			},"text"
		);
	}

	function createMarker(latlng, name, userinfo) {
		var html = "<b>" + name + "</b> <br/>" + userinfo;
		var marker = new google.maps.Marker({
			map: map,
			position: latlng
		});
		google.maps.event.addListener(marker, 'click', function() {
			infoWindow.setContent(html);
			infoWindow.open(map, marker);
		});
		markers.push(marker);
	}

	function createOption(name, distance, num) {
		var option = '<div class="nearbymember">';
		option += '<a href="#" onclick="google.maps.event.trigger(markers['+num+'], \'click\');">';
		option += name + "</a><br />" + distance.toFixed(1) + " miles";
		option += "</div>";
		jQuery( "#nearbylist" ).append(option);
	}

	function parseXml(str) {
		if (window.ActiveXObject) {
			var doc = new ActiveXObject('Microsoft.XMLDOM');
			doc.loadXML(str);
			return doc;
		} else if (window.DOMParser) {
			return (new DOMParser).parseFromString(str, 'text/xml');
		}
	}

	function doNothing() {}

</script>


<?php 
echo '<h2 class="componentheading">'.$cecfg->userdir_title.'</h2>';
echo '<div id="mue-user-dir-map">';
echo '<div id="nearbylist" style="width: 30%; height: 400px;float:left;overflow:scroll;"><div class="nearbymember">Enter in address below to search</div></div>';
echo '<div id="map" style="width: 68%; height: 400px;float:right;"></div>';
echo '<div style="clear:both;"></div>';
echo '</div>';
echo '<div style="height:20px;"></div>';
echo '<form action="" method="post" name="userdirform" id="userdirform" class="">';
echo '<div id="mue-user-dir">';
$first = true;
if ($this->sfields) echo '<div class="mue-user-dir-row"><div class="mue-user-dir-label"></div><div class="mue-user-dir-hdr"><b>Search Location</b></div></div>';
echo '<div class="mue-user-dir-row">';
echo '<div class="mue-user-dir-label">Location</div>';
echo '<div class="mue-user-dir-value">';
echo '<input placeholder="Address, City, State, and/or ZIP Code" type="text" id="addressInput" class="uf_field" validate="{required:true, messages:{required:\'This Field is required\'}}">';
echo '</div><div class="mue-user-dir-error"></div></div>';
echo '<div class="mue-user-dir-row">';
echo '<div class="mue-user-dir-label">Distance</div>';
echo '<div class="mue-user-dir-value">';
echo '<select id="radius" name="radius" class="uf_field">';
echo '<option value="25" selected>25 miles</option>';
echo '<option value="100">100 miles</option>';
echo '<option value="200">200 miles</option>';
echo '</select>';
echo '</div></div>';
echo '<div class="mue-user-dir-row">';
echo '<div class="mue-user-dir-label"># of Results</div>';
echo '<div class="mue-user-dir-value">';
echo '<select id="limit" name="limit" class="uf_field">';
echo '<option value="10">10</option>';
echo '<option value="20" selected>20</option>';
echo '<option value="50">50</option>';
echo '<option value="100">100</option>';
echo '</select>';
echo '</div></div>';
//Search fields
if ($this->sfields) echo '<div class="mue-user-dir-row"><div class="mue-user-dir-label"></div><div class="mue-user-dir-hdr"><b>Search Profile</b></div></div>';
foreach($this->sfields as $f) {
	echo '<div class="mue-user-dir-row">';
	echo '<div class="mue-user-dir-label">';
	
	$sname = $f->uf_sname;
	//field title
	if ($f->uf_type != "cbox" && $f->uf_type != "message" && $f->uf_type != "mailchimp") echo $f->uf_name;
	echo '</div>';
	echo '<div class="mue-user-dir-value">';
	
	//Message
	if ($f->uf_type == "message") echo '<strong>'.$f->uf_name.'</strong>';

	//multi checkbox
	if ($f->uf_type=="mcbox") {
		$first = true;
		foreach ($f->options as $o) {
			echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->text.'" class="uf_radio" id="jform_'.$sname.$o->value.'" />'."\n";
			echo '<label for="jform_'.$sname.$o->value.'">';
			echo ' '.$o->text.'</label><br />'."\n";
				
		}
	}

	//dropdown, radio
	if ($f->uf_type=="dropdown" || $f->uf_type=="multi") {
		echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="uf_field uf_select" size="1">';
		echo '<option value="" selected>- Any '.$f->uf_name.' -</option>';
		foreach ($f->options as $o) {
			echo '<option value="'.$o->text.'">';
			echo ' '.$o->text.'</option>';
		}
		echo '</select>';
	}

	//multilist
	if ($f->uf_type=="mlist") {
		echo '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="uf_field uf_mselect" size="4" multiple="multiple">';
		foreach ($f->options as $o) {
			echo '<option value="'.$o->value.'">';
			echo ' '.$o->text.'</option>';
		}
		echo '</select>';
	}


	//text field, phone #
	if ($f->uf_type=="textbox" || $f->uf_type=="phone") {
		echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" class="uf_field" type="text">';
	}

	//text area
	if ($f->uf_type=="textar") {
		echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="uf_field"';
		if ($f->uf_req) {
			echo ' validate="{required:true, messages:{required:\'This Field is required\'}}"';
		}
		echo '>'.$f->value.'</textarea>';
	}

	if ($f->uf_note && $f->uf_type!="captcha") echo '<span class="uf_note">'.$f->uf_note.'</span>';

	echo '</div>';
	echo '<div class="mue-user-dir-error">';
	//if ($f->uf_type=="multi" || $f->uf_type=="mcbox") echo '<label id="jform_'.$sname.'-lbl" for="jform['.$sname.']" class="uf_error"></label>';
	//else echo '<label id="jform_'.$sname.'-lbl" for="jform_'.$sname.'" class="uf_error"></label>';
	echo '</div>';
	echo '</div>';
}
echo '<div class="mue-user-dir-row">';
echo '<div class="mue-user-dir-label"></div>';
echo '<div class="mue-user-dir-submit">';
echo '<input type="submit" value="Search Directory" class="button">';
echo '<input type="hidden" name="lat" id="lat">';
echo '<input type="hidden" name="lng" id="lng">';
//echo '<input type="hidden" name="layout" value="groupuser">';
//echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
//echo JHtml::_('form.token');
echo '</div>';
echo '</div>';
echo '</form>';
echo '<div style="clear:both;"></div>';
echo '</div>';
?>
</div>



