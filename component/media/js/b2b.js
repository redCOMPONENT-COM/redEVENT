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
	
		/** request object for session **/
		sessionsreq : null,
		
		/** selected users for booking **/
		selected : new Array(),
		
		/**
		 * init events
		 */
		init : function() {

			/**
			 * ajax search course
			 */
			this.sessionsreq = new Form.Request(document.id('course-search-form'), document.id('main-course-results'), {
				resetForm : false,
				useSpinner: true,
				extraData : {
					'tmpl' : 'component'
				}
			});

			/**
			 * update sessions options when selecting event
			 */
			document.id('filter_event').addEvent('change', function(){
				redb2b.updateSessions();
			});

			/**
			 * update sessions options when selecting venue
			 */
			document.id('filter_venue').addEvent('change', function(){
				redb2b.updateSessions();
			});

			/**
			 * update sessions options when selecting venue
			 */
			document.id('filter_session').addEvent('change', function(){
				document.id('book-xref').set('value', this.get('value'));
				redb2b.attendeesList();
			});

			/**
			 * update organization bookings when selecting organization
			 */
			document.id('filter_organization').addEvent('change', function(){
				redb2b.searchBookings();
				
				var person_req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=getusers&tmpl=component',
					data : document.id('org-form'),
					method : 'post',
					onSuccess : function(options){
						var sel = document.id('filter_person');
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
				// Display organization users ?
				redb2b.attendeesList();
			});

			/**
			 * update organization bookings when selecting person
			 */
			document.id('filter_person').addEvent('change', function(){
				redb2b.searchBookings();
				// Display organization users ?
				redb2b.attendeesList();
			});

			/**
			 * update organization bookings when selecting session status active
			 */
			document.id('filter_person_active').addEvent('change', function(){
				redb2b.searchBookings();
			});

			/**
			 * update organization bookings when selecting session status archived
			 */
			document.id('filter_person_archive').addEvent('change', function(){
				redb2b.searchBookings();
			});

			/**
			 * update organization bookings when resetting filter person field
			 */
			document.id('reset_person').addEvent('click', function(){
				document.id('filter_person').set('value', '');
				redb2b.searchBookings();
			});

			/**
			 * update course search when clicking on book session button in lists
			 */
			document.id('redevent-admin').addEvent('click:relay(.bookthis)', function(e){
				e.stop();				
				var id = this.id.substr('6');
				redb2b.selectSession(id);
			});
			
			document.id('search-course-reset').addEvent('click', function(){
				this.form.reset();
				redb2b.attendeesList();
				redb2b.sessionsreq.send();
			});
			
			/**
			 * add checked attendee
			 */
			document.id('redevent-admin').addEvent('click:relay(.attendee-sel)', function(e){
				var id = this.id.substr('3');
				var name = this.getParent('tr').getElement('.attendee-name').get('text');
				var div = document.id('select-list');
				
				if (this.getProperty('checked')) {
					div.removeClass('nouser');
					div.getElement(".notice").set('styles', {display:'none'});
					
					var newrow = new Element('div#member'+id, {'class' : 'selectedmember'});
					var img = new Element('img', {
						'src' : 'media/com_redevent/images/icon-16-delete.png',
						'alt': 'delete'
					}).addEvent('click', function(){
						newrow.dispose();
						if (document.id('cid'+id)) {
							document.id('cid'+id).removeProperty('checked');
						}
						redb2b.selected.erase(id);
						if (!redb2b.selected.length) {
							div.getElement(".notice").set('styles', {display:'block'});
							div.addClass('nouser');
						}
					});
					var imgspan = new Element('span.member-remove');
					var input = new Element('input', {'name' : 'reg[]', 'value' : id, 'type' : 'hidden'});				
					var inputspan = new Element('span.member-name').set('text', name);
					
					newrow.adopt(imgspan.adopt(img));
					newrow.adopt(inputspan.adopt(input));					
					
					newrow.inject(div);
					
					redb2b.selected.push(id);
					
					document.id('book-course').set('styles', {'display' :'block'});
				}
				else {
					/** remove from selected list **/
					document.id('member'+id).dispose();
					redb2b.selected.erase(id);
					if (!redb2b.selected.length) {
						redb2b.resetSelected();
					}
				}
			});
			
			/**
			 * edit attendee
			 */
			document.id('redevent-admin').addEvent('click:relay(.editattendee)', function(e){
				alert('non implemented yet');
			});
			
			/**
			 * remove registration
			 */
			document.id('redevent-admin').addEvent('click:relay(.unregister)', function(e){
				if (confirm('are you sure ?')) {
					alert('non implemented yet');					
				}
			});
			
			document.id('book-course').addEvent('click', function(){
				req = new Request({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=quickbook&tmpl=component',
					data : document.id('selected_users'),
					method : 'post',
					onSuccess : function(responseText){
						alert('should book !');					
					}
				});
				req.send({'test' : 11});
			});
		},
				
		updateSessions : function(async) {
			async = typeof async !== 'undefined' ? async : true;
			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=sessionsoptions&tmpl=component',
				data :document.id('course-search-form'),
				async : async,
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
					redb2b.attendeesList();
				}
			});
			req.send();
		},
		
		searchBookings : function() {
			req = new Request({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=getbookings&tmpl=component',
				data : document.id('org-form'),
				method : 'post',
				onSuccess : function(responseText){
					document.id('main-bookings').set('html', responseText);					
				}
			});
			req.send();
		},
		
		selectSession : function(id) {
			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=getsession&tmpl=component&id=' + id,
				onSuccess : function(session){
					document.id('filter_event').set('value', session.did);
					document.id('filter_session').empty();
					thereq = redb2b.updateSessions(false);
					document.id('filter_session').set('value', id);
					document.id('book-xref').set('value', id);
					redb2b.attendeesList();
					redb2b.sessionsreq.send();
				}
			});
			req.send();			
		},
		
		attendeesList : function() {
			document.id('main-attendees').set('styles', {'display' : 'none'}).empty();
			redb2b.resetSelected();
			if (document.id('filter_organization').get('value') > 0 && document.id('filter_session').get('value') > 0) {
				var req = new Request.HTML({
					url: 'index.php?option=com_redevent&controller=frontadmin&task=getattendees&tmpl=component',
					data : {'xref' : document.id('filter_session').get('value'),
						'org' : document.id('filter_organization').get('value')
					},
					onSuccess : function(text) {
						resdiv = document.id('main-attendees').adopt(text).set('styles', {'display' : 'block'});
					}
				});
				req.send();
			}
		},
		
		resetSelected : function() {
			redb2b.selected = new Array();
			var div = document.id('select-list');
			div.getElement(".notice").set('styles', {display:'block'});
			div.addClass('nouser');
			div.getElements('.selectedmember').dispose();
			document.id('book-course').set('styles', {'display' :'none'});			
		}
};