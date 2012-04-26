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

// Call this function when the page has been loaded

var venuesmap = {
		
	map : null,
		
	initialize : function() {
		
		// init map

	    var mapOptions = {
	    		  panControl: true,
	    		  zoomControl: true,
	    		  mapTypeControl: true,
	    		  scaleControl: true,
	    		  streetViewControl: true,
	    		  overviewMapControl: true,
	    		  zoom: 3,
	    		  center: new google.maps.LatLng(43,2),
	    		  mapTypeId: google.maps.MapTypeId.ROADMAP
	    		};
	    map = new google.maps.Map( document.id('gmap').setProperty('style', 'width: 100%; height: 600px'), mapOptions);
	    	    
	    // get marker manager
	    var mgr = new MarkerManager(map);
	    // get bound object to calculate best fit later
	    var bounds = new google.maps.LatLngBounds();
	    
	    var markers = [];
	    // add marker for each location
	    venues.each(function(venue) {    
	      if (venue.lat !=0 && venue.lng !=0) {
	
	        // create marker
	        var point = new google.maps.LatLng(venue.lat, venue.lng);
	        var marker = new google.maps.Marker({ position: point, title: venue.name});

	        // add the point to the bounds
	        bounds.extend(point);
	        
	        google.maps.event.addListener(marker, 'click', function(latlng) {
	        	var url = venueurl + '&id=' + this.venue.id;
	    		var theAjax = new Ajax(url, {
	    			method: 'post',
	    			postBody : ''
	    			});
	    		
	    		theAjax.addEvent('onSuccess', venuesmap.popvenueinfo.bind(this.marker));
	    		theAjax.request();
		        }.bind({'venue':venue, 'marker': marker})
	        );                  
	        
	        markers.push(marker);
	      }
	    });
	
	    var countrymarkers = [];
	    // add marker for each country	    
	    countries.each(function(element) {
	      if (element.lat !=0 && element.lng !=0) {
	        // create latlng object 
	        var icon = new google.maps.MarkerImage(element.flag, 
	        		                               new google.maps.Size(32, 22),
	        		                               new google.maps.Point(0, 0),
	        		                               new google.maps.Point(16, 11)
	        		                               );
	        
	        var target = new google.maps.LatLng(element.lat, element.lng);
	        // create marker
	        opts = {
	          position : target,
	          icon: icon,
	          clickable: true,
	          title: element.name
	        };
	
	        var marker = new google.maps.Marker(opts);
	        
	        google.maps.event.addListener(marker, 'click', function(aa, latlng){
	           this.map.setCenter(this.marker.getLatLng(), 5);
	        }.bind({'map':this, 'marker': marker}));
	        countrymarkers.push(marker);
	      }
	    }.bind(this.map));
	    	    
	    google.maps.event.addListener(mgr, 'loaded', function() {
	    	// optimal map fit
	    	map.fitBounds(bounds);	
		    mgr.addMarkers(countrymarkers, 0 , 5);
		    mgr.addMarkers(markers, 4);
		    mgr.refresh();
	  	}); 
	},
	
	popvenueinfo : function(response)
	{
		var infowindow = new google.maps.InfoWindow({content: response});
		infowindow.open(map, this);
	}
};

window.addEvent('domready', venuesmap.initialize);
