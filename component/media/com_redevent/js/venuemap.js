/**
 * @version 1.0 $Id: recurrence.js 30 2009-05-08 10:22:21Z roland $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

// Call the init function when the page has been loaded
var mymap = (function($) {
	var options = {
		zoom: 16,
			mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	var venue, map;

	var init = function (venue, elementid) {
		venue = venue;
		map = new google.maps.Map(document.getElementById(elementid), options);

		if (venue.latitude) {
			setmarker();
		}
		else {
			codeadress();
		}
	};

	var initajax = function (venueid, elementid) {
		$.ajax({
			url: basepath + 'index.php?option=com_redevent&view=venue&format=raw&id=' + venueid,
			dataType: "json"
		})
		.done(function(data){
			venue = data;
			map = new google.maps.Map(document.getElementById(elementid), options);

			if (venue.latitude != "null") {
				setmarker();
			}
			else {
				codeadress();
			}
		});
	};

	var codeadress = function () {
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address': venue.address}, function (results, status) {
			if (results[0]) {
				venue.latitude = results[0].geometry.location.lat();
				venue.longitude = results[0].geometry.location.lng();
				setmarker();
			}
		});
	};

	var setmarker = function () {
		var latlng = new google.maps.LatLng(venue.latitude, venue.longitude);

		map.setCenter(latlng);

		var marker = new google.maps.Marker({
			map: map,
			position: latlng
		});

		// set content of infowindow
		var addressparts = venue.address.split(',');

		var div = $('<div></div>');
		var txt = '<strong>' + venue.name + '</strong><br/>';
		for (var i = 0; i < addressparts.length; i++) {
			txt += addressparts[i] + '<br/>';
		}
		div.append(txt);

		if (venue.address) {
			$('<a></a>').attr('href', 'http://maps.googleapis.com/maps?daddr=' + encodeURI(venue.address) + '@' + venue.latitude + ',' + venue.longitude)
				.append(Joomla.JText._('COM_REDEVENT_GET_DIRECTIONS')).appendTo(div);
		}

		var infowindow = new google.maps.InfoWindow();
		infowindow.setContent(div[0].outerHTML);

		google.maps.event.addListener(marker, 'click', function () {
			infowindow.open(map, marker);
		});

		// only open on map display if map is big enough
		var $map = $(map.getDiv());

		if ($map.width() >= 350 && $map.height() >= 350) {
			infowindow.open(map, marker);
		}
	};

	return {
		initajax: initajax
	};
})(jQuery);
