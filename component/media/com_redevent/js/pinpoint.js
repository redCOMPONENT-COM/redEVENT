/**
 * @version 2.5
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008-2011 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
(function($){

	// Call the init function when the page has been loaded
	var mymap = {

		options: {
			zoom: 16,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		},

		venue : null,
		map : null,
		marker : null,
		defaultaddress: 'usa',
		isInit: false,

		init : function(element)
		{
			if (!this.isInit) {
				this.map = new google.maps.Map(element,
					this.options);

				$('#locationSave').click(this.updateclose.bind(this));

				this.isInit = true;
			}

			this.initmarker();
			this.map.setCenter(this.marker.getPosition());
		},

		initaddress : function()
		{
			var address = new Array();
			if ($('#jform_street').val()) {
				address.push($('#jform_street').val());
			}
			if ($('#jform_city').val()) {
				address.push($('#jform_city').val());
			}
			if ($('#jform_country').val()) {
				address.push($('#jform_country').val());
			}
			address = address.join().toLowerCase();
			return address;
		},

		codeadress : function()
		{
			var address = this.venue.address;
			var finalzoom = 16;
			if (address == "") {
				address = mymapDefaultaddress ? mymapDefaultaddress : this.defaultaddress;
				finalzoom = 3;
			}
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (results[0]) {
					this.venue.latitude = results[0].geometry.location.lat();
					this.venue.longitude = results[0].geometry.location.lng();
					this.setmarker();
					this.map.setZoom(finalzoom);
					this.markerShowInfo();
				}
			}.bind(this));
		},

		initmarker : function()
		{
			this.venue = {
				latitude: $('#jform_latitude').val(),
				longitude: $('#jform_longitude').val(),
				address: this.initaddress()
			};

			if (!this.marker) {
				this.marker = new google.maps.Marker({
					map: this.map,
					position: new google.maps.LatLng(this.venue.latitude, this.venue.longitude),
					draggable: true
				});
			}
			else {
				this.marker.setPosition(new google.maps.LatLng(this.venue.latitude, this.venue.longitude));
			}

			// marker now set to default position, try to geocode if an address was provided and coordinates not set
			if (! (this.marker.getPosition().lat() || this.marker.getPosition().lng()) ) {
				this.codeadress();
			}
		},

		setmarker : function()
		{
			var latlng = new google.maps.LatLng(this.venue.latitude, this.venue.longitude);
			this.map.setCenter(latlng);
			this.marker.setPosition(latlng);
		},

		updateclose: function()
		{
			$('#jform_latitude').val(this.marker.getPosition().lat());
			$('#jform_longitude').val(this.marker.getPosition().lng());
			$('#pinpointModal').modal('hide');
		}
	};

	$(document).ready(function() {
		$('#pinpointModal').on('show', function(){
			mymap.init(document.getElementById('pinpointMapCanvas'));
		});
	});

})(jQuery);


