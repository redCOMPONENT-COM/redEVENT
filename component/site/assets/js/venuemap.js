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
var mymap = {
		
	options: {
		zoom: 16,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	},
	
	venue : null,
	
	map : null,
	
	init : function(venue, elementid) {
		this.venue = venue;
		this.map = new google.maps.Map(document.getElementById(elementid),
			    this.options);
		if (this.venue.latitude) {
			this.setmarker();
		}
		else {
			this.codeadress();
		}
	},
	
	initajax : function(venueid, elementid) {
		var url = basepath + 'index.php?option=com_redevent&view=venue&format=raw&id='+venueid;
		var theAjax = new Ajax(url, {
			method: 'post',
			postBody : ''
			});
		
		theAjax.addEvent('onSuccess', function(response) {
			this.venue = eval('(' + response + ')');
			this.map = new google.maps.Map(document.getElementById(elementid),
				    this.options);
			if (this.venue.latitude != "null") {
				this.setmarker();
			}
			else {
				this.codeadress();
			}
		}.bind(this));
		theAjax.request();
	},
	
	codeadress : function() {
		var geocoder = new google.maps.Geocoder();
	    geocoder.geocode( { 'address': this.venue.address}, function(results, status) {
	    	this.venue.latitude = results[0].geometry.location.lat();
	    	this.venue.longitude = results[0].geometry.location.lng();
	    	this.setmarker();
	    }.bind(this));
	},
	
	setmarker : function() {
		var latlng = new google.maps.LatLng(this.venue.latitude, this.venue.longitude);
		this.map.setCenter(latlng);
        var marker = new google.maps.Marker({
            map: this.map, 
            position: latlng
        });
        
        // set content of infowindow
        var addressparts = this.venue.address.split(',');
        var div = new Element('div');
        var txt = '<strong>'+this.venue.name+'</strong><br/>';
        for (var i = 0; i < addressparts.length; i++) {
        	txt += addressparts[i]+'<br/>';
        }
        div.setHTML(txt);
        if (this.venue.address) {
        	new Element('a', {href: 'http://maps.google.com/maps?daddr=”'+this.venue.address+'”', target: '_blank'}).setText(directiontext).injectInside(div);
        }
        var infowindow = new google.maps.InfoWindow();
        infowindow.setContent(div);
    	google.maps.event.addListener(marker, 'click', function() {
    		infowindow.open(this.map, marker);
    	}.bind(this));
    	// only open on map display if map is big enough
        var size = $(this.map.getDiv()).getSize().size;
        if (size.x >= 350 && size.y >= 350) {
        	infowindow.open(this.map, marker);
        }
	}
};

