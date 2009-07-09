/**
 * @version 1.1 $Id$
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * google map documentation: http://code.google.com/apis/maps/documentation/reference.html
 */

google.load("maps", "2");

// Call this function when the page has been loaded
function initialize() {
	if (GBrowserIsCompatible()) {
	  // init map
    var map = new GMap2($('gmap').setProperty('style', 'width: 100%; height: 600px'));
    // setCenter must be call before performing other operations
    map.setCenter(new GLatLng(0,0), 1);
    
    // add controls
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
    //map.addControl(new GSmallZoomControl());
    map.addControl(new GScaleControl());
    map.addControl(new GOverviewMapControl());
    
    //scroll == zoom
    map.enableScrollWheelZoom();
    
    map.geocoder = null;

    //zoom only when mous in map area
    GEvent.addDomListener(map.getContainer(), "DOMMouseScroll",
        function(oEvent) { if (oEvent.preventDefault)
        oEvent.preventDefault(); }
    );
    
    // get marker manager
    var mgr = new MarkerManager(map);
    // get bound object to calculate best fit later
    var bounds = new GLatLngBounds();
    
    var markers = [];
    // add marker for each location
    venues.each(function(venue) {    
      if (venue.lat !=0 && venue.lng !=0) {

        // create marker
        var point = new GLatLng(venue.lat, venue.lng)
        var marker = new GMarker(point, {'title':venue.venue});
        // add the point to the bounds
        bounds.extend(point);
        
        GEvent.addListener(marker, 'click', function(latlng){
	          GDownloadUrl(venueurl + '&id=' + this.venue.id, popvenueinfo.bind(this.marker));
	        }.bind({'venue':venue, 'marker': marker})
        );                  
        
        markers.push(marker);
      }
    }.bind(map));

    var countrymarkers = [];
    // add marker for each location
    countries.each(function(element) {
      var marker;
      if (element.lat !=0 && element.lng !=0) {
        // create latlng object 
        var icon = new GIcon();
        icon.image = element.flag;
        icon.iconSize = new GSize(32, 22);
        icon.iconAnchor = new GPoint(16, 11);
        
        var target = new GLatLng(element.lat, element.lng);
        // create marker
        opts = {
          "icon": icon,
          "clickable": true,
        };
        var marker = new LabeledMarker(target, opts);
        GEvent.addListener(marker, 'click', function(aa, latlng){
           this.map.setCenter(this.marker.getLatLng(), 5);
        }.bind({'map':this, 'marker': marker}));
                
        countrymarkers.push(marker);
      }
    }.bind(map));

    
    mgr.addMarkers(countrymarkers, 0 , 5);
    mgr.addMarkers(markers, 4);
    
    // optimal zoom
    map.setZoom(map.getBoundsZoomLevel(bounds));
    map.setCenter(bounds.getCenter());
    
    mgr.refresh();
    
	}
}

function popvenueinfo(text, status)
{
  if (status == 200) {
    this.openInfoWindowHtml(text);
  }
  else {
    this.openInfoWindowHtml('Error loading details');
  } 
}

// the google 'domready' callback
google.setOnLoadCallback(initialize);
