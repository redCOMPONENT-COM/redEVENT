/**
 * @version    2.5 
 * @package    redEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

/**
 * this file manages the js script for b2b interface
 */

window.addEvent('domready', function() {	
	
	redb2b.init();
	
});

var redb2b = {
	
		/**
		 * init events
		 */
		init : function() {
			new Form.Request(document.id('course-search-form'), document.id('main-course-results'), {
				resetForm : false,
				useSpinner: true,
				extraData : {
					'tmpl' : 'component'
				}
			});
			
			document.id('filter_event').addEvent('change', function(){
				redb2b.updateSessions();
			});
			
			document.id('filter_venue').addEvent('change', function(){
				redb2b.updateSessions();
			});
			
			document.id('filter_organization').addEvent('change', function(){
				redb2b.searchBookings();
				
				var person_req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=getusers&tmpl=component',
					data : document.id('org-form'),
					method : 'post',
					onSuccess : function(options){
						var sel = document.id('filter_user');
						sel.empty();
						new Element('option', {'value': ''}).set('text', Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_SELECT_USER")).inject(sel);
						if (options.length) {
							options.each(function(el){
								new Element('option', {'value': el.value}).set('text', el.text).inject(sel);
							});
						}			
					}
				});
				person_req.send();
			});
			
			document.id('filter_user').addEvent('change', function(){
				redb2b.searchBookings();
			});
			
			document.id('filter_person_active').addEvent('change', function(){
				redb2b.searchBookings();
			});
			
			document.id('filter_person_archive').addEvent('change', function(){
				redb2b.searchBookings();
			});
		},
		
		updatemain : function() {
			var request = new Request({
				url: 'index.php?option=com_redevent&controller=frontadmin&task=main&tmpl=component',
				onSuccess : function(responseText, responseXML) {
					document.id('redadmin-main').set('html', responseText);
				}
			});
			request.send();
		},
		
		updateSessions : function() {
			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=sessionsoptions&tmpl=component',
				data :document.id('course-search-form'),
				onRequest: function(){
					document.id('filter_session').set('spinner').spin();
			    },
				onSuccess : function(options) {
					var sel = document.id('filter_session').unspin();
					sel.empty();
					new Element('option', {'value': ''}).set('text', Joomla.JText._("COM_REDEVENT_SESSION")).inject(sel);
					if (options.length) {
						options.each(function(el){
							new Element('option', {'value': el.value}).set('text', el.text).inject(sel);
						});
					}
				}
			});
			req.send();
		},
		
		searchBookings : function() {
			var req = new Request({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=getbookings&tmpl=component',
				data : document.id('org-form'),
				method : 'post',
				onSuccess : function(responseText){
					document.id('main-bookings').set('html', responseText);					
				}
			});
			req.send();			
		}
		
};