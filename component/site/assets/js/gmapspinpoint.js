/**
 * @version 1.0 $Id: ggmapspinpoint.js 30 2009-05-08 10:22:21Z roland $
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
	//GMapsOverlay.init();
});
	
//nothing yet
/**
 * @TODO: fix it !
 */
var GMapsOverlay = {
		
		marker:  null,
		address: '',
		venue:   '',
		options: null,
		geocoder: null,
		
		init: function(options){
		    
		    this.options = Object.extend({
		      resizeDuration: 400,
		      resizeTransition: Fx.Transitions.sineInOut,
		      width: 250,
		      height: 250,
		      animateCaption: true
		    }, options || {});

		    this.geocoder = new google.maps.Geocoder();

		    $$('.pinpoint').addEvent('click', this.click.bind(this));

//		    this.eventKeyDown = this.keyboardListener.bindAsEventListener(this);
		    this.eventKeyDown = this.keyboardListener.bind(this);

		    this.eventPosition = this.position.bind(this);

		    this.overlay = new Element('div').setProperty('id', 'gmOverlay').injectInside(document.body);

		    this.center = new Element('div').setProperty('id', 'gmCenter').setStyles({

		      width: this.options.width+'px',
		      height: this.options.height+'px',
		      marginLeft: '-'+(this.options.width/2)+'px'

		    }).injectInside(document.body);
		    
		    this.top = new Element('div').setProperty('id', 'gmTop').set('html', sTitle).injectInside(this.center);
		    
		    this.maplayer = new Element('div').setProperty('id', 'gmMap').injectInside(this.center);

		    var myLatlng = new google.maps.LatLng(43, 7);
		    var mapOptions = {
		    		  panControl: true,
		    		  zoomControl: true,
		    		  mapTypeControl: true,
		    		  scaleControl: true,
		    		  streetViewControl: false,
		    		  overviewMapControl: true,
		    		  zoom: 8,
		    		  center: myLatlng,
		    		  mapTypeId: google.maps.MapTypeId.ROADMAP
		    		};

		    this.map = new google.maps.Map(this.maplayer, mapOptions);  
		    
		    google.maps.event.addListener(this.map, "rightclick", function(point) {
		      this.marker.setMap(); // removes overlay from map
		      this.showMarker(point.latLng);
		    }.bind(this));

		    this.bottomContainer = new Element('div').setProperty('id', 'gmBottomContainer').setStyle('display', 'none').injectInside(document.body);

		    this.bottom = new Element('div').setProperty('id', 'gmBottom').injectInside(this.bottomContainer);

		    new Element('a').setProperties({id: 'gmCloseLink', href: '#'}).injectInside(this.bottom).onclick = this.overlay.onclick = this.close.bind(this);

		    this.caption = new Element('div').setProperty('id', 'gmCaption').injectInside(this.bottom);

		    new Element('div').setStyle('clear', 'both').injectInside(this.bottom);

		    this.center.style.display = 'none';

		    var nextEffect = this.nextEffect.bind(this);

		    this.fx = {

		      overlay: this.overlay.effect('opacity', {duration: 500, fps:100}).hide(),

		      resize: this.center.effects({duration: this.options.resizeDuration, transition: this.options.resizeTransition, onComplete: nextEffect}),

		      maplayer: this.maplayer.effect('opacity', {duration: 500, onComplete: nextEffect}),

		      bottom: this.bottom.effect('margin-top', {duration: 400, onComplete: nextEffect})

		    };

		  },

		  click: function(){
		      
		    this.clearOverlays();
		    return this.show();

		  },
		  
		  clearOverlays : function(){
			if (this.infowindow) {
				this.infowindow.close();
			}
			if (this.marker) {
				this.marker.setMap();
			}
		  },

		  show: function(){
		    
		    this.position();

		    this.setup(true);

		    this.top = window.getScrollTop() + (window.getHeight() / 15);

		    this.center.setStyles({top: this.top+'px', display: ''});

		    this.fx.overlay.start(0.8);

		    return this.changeLink();

		  },

		  position: function(){

		    this.overlay.setStyles({top: window.getScrollTop()+'px', height: window.getHeight()+'px'});

		  },

		  setup: function(open){

		    var elements = $A(document.getElementsByTagName('object'));

		    if (window.ie) elements.extend(document.getElementsByTagName('select'));

		    elements.each(function(el){ el.style.visibility = open ? 'hidden' : ''; });

		    var fn = open ? 'addEvent' : 'removeEvent';

		    window[fn]('scroll', this.eventPosition)[fn]('resize', this.eventPosition);

		    document[fn]('keydown', this.eventKeyDown);

		    this.step = 0;

		  },

		  keyboardListener: function(event){

		    this.close();

		  },

		  changeLink: function(){

		    this.step = 1;

		    this.bottomContainer.style.display = 'none';

		    this.fx.maplayer.hide();

		    this.center.className = 'gmLoading';

		    this.venue = $('venue').value;

		    var address = null;
		    if ($('street').value) address = $('street').value;
		    
		    if ($('plz').value || $('city').value) {
		      if (address) {
		        address += ',' + $('plz').value + ' ' + $('city').value;
		      }
		      else {
		        address = $('plz').value + ' ' + $('city').value;
		      }
		    }
		    
		    if ($('country').value) {
		      var countryselect = $('country');
		      if (parseInt(countryselect.selectedIndex) > 0) {
		        var country = countryselect.options[countryselect.selectedIndex].text;
		        if (address) {
		          address += ',' + country.substring(0, country.indexOf(','));
		        }
		        else {
		          address = country.substring(0, country.indexOf(','));  
		        }
		      }
		    }
		    this.address = address;
		    
		    var latitude = $('latitude').value;
		    var longitude= $('longitude').value;
		    
		    if (latitude != 0 && longitude != 0 && latitude != "" && longitude != "") {
		      venuepos = new google.maps.LatLng(latitude, longitude);
		      this.showPoint(venuepos, this.address);
		    }
		    else if (this.address) {
		      this.showAddress(this.address);
		    }
		    else {
		      this.showMarker(null);
		    }
		    this.nextEffect();

		    return false;

		  },

		  nextEffect: function(){

		    switch (this.step++){

		      case 1:

		      this.center.className = '';

		      this.caption.set('html', "Google-Maps");

		      if (this.center.clientHeight != this.maplayer.offsetHeight){

		        this.fx.resize.start({height: this.maplayer.offsetHeight, width: this.maplayer.offsetWidth, marginLeft: -this.maplayer.offsetWidth/2});

		        break;

		      }

		      this.step++;

		      case 2:

		      this.bottomContainer.setStyles({top: (this.top + this.center.clientHeight)+'px', height: '0px', marginLeft: this.center.style.marginLeft, width: this.center.clientWidth+'px', display: ''});

		      this.fx.maplayer.start(1);

		      break;

		      case 3:

		      if (this.options.animateCaption){

		        this.fx.bottom.set(-this.bottom.offsetHeight);

		        this.bottomContainer.style.height = '';

		        this.fx.bottom.start(0);

		        break;

		      }

		      this.bottomContainer.style.height = '';

		      case 4:

		      this.step = 0;

		    }
		    google.maps.event.trigger(this.map, "resize");
		  },

		  close: function(){

		    if (this.step < 0) return;

		    this.step = -1;

		    for (var f in this.fx) this.fx[f].stop();

		    this.center.style.display = this.bottomContainer.style.display = 'none';

		    this.fx.overlay.chain(this.setup.pass(false, this)).start(0);
		    
		    var lat = $('latitude').value;
		    var lng = $('longitude').value;
		    if (lat == '' || lat == 0 || lng == '' || lng == 0) {
		      $('latitude').value = this.marker.getPosition().lat();
		      $('longitude').value = this.marker.getPosition().lng();
		    }

		    return false;

		  },

		  showAddress: function(address) {
			  this.geocoder.geocode( { 'address': address}, function(results, status) {
				  if (status == google.maps.GeocoderStatus.OK) {
					  place = results[0];
					  this.address = place.formatted_address;
					  this.showMarker(place.geometry.location);
				  } else {
				        target = null;				        
				        this.showMarker(target);
				  }
			  }.bind(this));
		  },
		  
		  showPoint: function(target, address){
		    
		      if(target){
		        this.showMarker(target); 
		      }
		  },
		  
		  showMarker: function(target)
		  {
		    var isNull = false;
		    if(!target)
		    {
		      target = new google.maps.LatLng(0, 0);
		      isNull = true;      
		    }
		    
	        //set center
	        this.map.setCenter(target);
	        this.map.setZoom(4);

	        //set marker
	        this.marker = new google.maps.Marker({position: target, draggable: true});

	        google.maps.event.addListener(this.marker, "dragstart", function() {
	        	if (this.infowindow) {
	        		this.infowindow.close();
	        	}
	        }.bind(this));

	        google.maps.event.addListener(this.marker, "dragend", this.markerShowInfo.bind(this));
	        
	        this.marker.setMap(this.map);
	        
	        this.markerShowInfo();
	        
	        if (!isNull) {
		        this.map.setZoom(15);
	        }
		  },
		  
		  updateclose: function()
		  {
		    $('latitude').value = this.marker.getPosition().lat();
		    $('longitude').value = this.marker.getPosition().lng();
		    this.close();
		  },
		  
		  markerShowInfo: function()
		  {
		     var info = new Element('div').setProperty('id', 'markerInfo');
		     info.set('html', '<strong>' + (this.venue ? this.venue : '') + '</strong><br />' + (this.address ? this.address : ''));
		     new Element('br').injectInside(info);

		     var pos = this.marker.getPosition();
		     if (pos) {
			     var lat = pos.lat()+'';
			     var lng = pos.lng()+'';
			 }
		     else {
			     var lat = '';
			     var lng = '';
			 }
		     
		     info.appendText(sLatitude + ': ' + lat.substr(0,8) + '...');
		     new Element('br').injectInside(info);
		     info.appendText(sLongitude + ': ' + lng.substr(0,8) + '...');
		     new Element('br').injectInside(info);
		     var apply = new Element('a');
		     apply.appendText(sApply).addEvent('click', this.updateclose.bind(this));
		     apply.setProperties({
		       'class': 'gmaplink',
		       'href': '#'
		     });
		     apply.injectInside(info);
		     
		     info.appendText(' - ');
		     
		     var close = new Element('a').setProperties({'class':'gmaplink', 'href': '#'});
		     close.appendText(sClose).addEvent('click', this.close.bind(this));
		     close.injectInside(info);
		     
		     this.infowindow = new google.maps.InfoWindow({  
		    	  content: info  
		    	});
		     this.infowindow.open(this.map, this.marker);
		  }
		  
		};