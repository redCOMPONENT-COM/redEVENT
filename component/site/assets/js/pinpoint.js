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

window.addEvent('domready', function (){    
	/**
     * Set default options, overrideable from later calls.
     */
    SqueezeBox.initialize({
        size: {x: 600, y: 400}
    });
    
    $$('span.venuemap').addEvent('click', function(){
    	var d = new Element('div', { style: "width: 600px; height: 400px" }).inject(document.body);
    	SqueezeBox.open(d, {handler: 'adopt'});
    	mymap.init(d);
    });
});

//Call the init function when the page has been loaded
var mymap = {
		
	options: {
		zoom: 16,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	},
	
	venue : null,	
	map : null,
	marker : null,
	infowindow: null,
	defaultaddress: 'usa',
	
	init : function(element) 
	{
		this.map = new google.maps.Map(element,
			    this.options);
		this.initmarker();
		this.map.setCenter(this.marker.getPosition());
	},
	
	initaddress : function()
	{
		var address = new Array();
		if (document.id('street').get('value')) {
			address.push(document.id('street').get('value'));
		}
		if (document.id('city').get('value')) {
			address.push(document.id('city').get('value'));
		}
		if (document.id('country').get('value')) {
			var sel = document.id('country').getSelected();
			address.push(sel[0].get("text"));
		}
		address = address.join().toLowerCase();
		return address;
	},

	codeadress : function() 
	{
		var address = this.venue.address;
		var finalzoom = 16;
		if (address == "") {
			address = this.defaultaddress;
			finalzoom = 3;
		}
		var geocoder = new google.maps.Geocoder();
	    geocoder.geocode( { 'address': address}, function(results, status) {
	    	if (results[0]) {
		    	this.venue.latitude = results[0].geometry.location.lat();
		    	this.venue.longitude = results[0].geometry.location.lng();
				this.setmarker();
				this.map.setZoom(finalzoom);
		    }
	    }.bind(this));
	},
	
	initmarker : function()
	{
		this.venue = {
				latitude: document.id('latitude').get('value'),
				longitude: document.id('longitude').get('value'),
				address: this.initaddress()
		};
		
		this.marker = new google.maps.Marker({
            map: this.map, 
            position: new google.maps.LatLng(this.venue.latitude, this.venue.longitude),
            draggable: true
        });
        
        google.maps.event.addListener(this.marker, "dragstart", function() {
        	if (this.infowindow) {
        		this.infowindow.close();
        	}
        }.bind(this));

        google.maps.event.addListener(this.marker, "dragend", this.markerShowInfo.bind(this));
        
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

	  
	markerShowInfo: function()
	{
		var lat, lng;
		var info = new Element('div').setProperty('id', 'markerInfo');
		info.set('html', this.venue.address ? this.venue.address : '');
		new Element('br').inject(info);

		var pos = this.marker.getPosition();
		lat = pos.lat()+'';
		lng = pos.lng()+'';

		info.appendText(sLatitude + ': ' + lat.substr(0,8) + '...');
		new Element('br').inject(info);
		info.appendText(sLongitude + ': ' + lng.substr(0,8) + '...');
		new Element('br').inject(info);
		var apply = new Element('span');
		apply.appendText(Joomla.JText._("COM_REDEVENT_APPLY")).addEvent('click', this.updateclose.bind(this));
		apply.setProperties({
			'class': 'gmaplink'
		});
		apply.inject(info);

		info.appendText(' - ');

		var close = new Element('span').setProperties({'class':'gmaplink'});
		close.appendText(Joomla.JText._("COM_REDEVENT_CLOSE")).addEvent('click', this.close.bind(this));
		close.inject(info);

		this.infowindow = new google.maps.InfoWindow({  
			content: info  
		});
		this.infowindow.open(this.map, this.marker);
	},

	close: function() 
	{
		SqueezeBox.close();
		return false;
	},

	updateclose: function()
	{
		$('latitude').value = this.marker.getPosition().lat();
		$('longitude').value = this.marker.getPosition().lng();
		this.close();
	}
};