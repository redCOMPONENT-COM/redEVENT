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
			
		/** selected users for booking **/
		selected : new Array(),
		
		/**
		 * init events
		 */
		init : function() {
			
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
			 * search for sessions
			 */
			document.id('search-course').addEvent('click', redb2b.getSessions);
			
			/**
			 * update course search when clicking on book session button in lists
			 */
			document.id('redevent-admin').addEvent('click:relay(.bookthis)', function(e){
				e.stop();				
				var id = this.id.substr('6');
				redb2b.selectSession(id);
			});
			
			document.id('search-course-reset').addEvent('click', function(){
				document.id('filter_event').set('value', '');
				document.id('filter_session').empty();
				document.id('filter_venue').set('value', '');
				document.id('filter_category').set('value', '');
				document.id('filter_from').set('value', '');
				document.id('filter_to').set('value', '');
				redb2b.attendeesList();
				redb2b.getSessions();
			});
			
			/**
			 * add checked attendee
			 */
			document.id('redevent-admin').addEvent('click:relay(.attendee-sel)', function(e){
				var id = this.id.substr('3');
				var name = this.getParent('tr').getElement('.attendee-name').get('text');
				var div = document.id('select-list');
				
				if (this.getProperty('checked')) {
					if (!redb2b.selected.contains(id)) {
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
			 * edit member
			 */
			document.id('redevent-admin').addEvent('click:relay(.editmember)', function(e){
				var id = this.getParent('tr').getProperty('uid');
				redb2b.editmember(id);
			});

			/**
			 * add member
			 */
			document.id('redevent-admin').addEvent('click:relay(.add-employee)', function(e){
				redb2b.editmember(0);
			});
						
			/**
			 * remove registration
			 */
			document.id('redevent-admin').addEvent('click:relay(.unregister)', function(e){
				if (confirm(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM"))) {
					var register_id = this.getParent('tr').getProperty('rid');
					req = new Request.JSON({
						url : 'index.php?option=com_redevent&controller=frontadmin&task=cancelreg&tmpl=component',
						data : {'rid' : register_id},
						method : 'post',
						onRequest: function(){
							document.id('attendees-tbl').set('spinner').spin();
					    },
						onSuccess : function(response){
							document.id('attendees-tbl').set('spinner').unspin();
							if (response.status == 1) {
								redb2b.attendeesList();	
							}
							else {
								alert(response.error);
							}
						}
					});
					req.send();
				}
			});
			
			document.id('book-course').addEvent('click', function(){
				req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=quickbook&tmpl=component',
					data : document.id('selected_users'),
					method : 'post',
					onRequest: function(){
						document.id('attendees-tbl').set('spinner').spin();
						document.id('selected_users').set('spinner').spin();
				    },
					onSuccess : function(response){
						document.id('attendees-tbl').set('spinner').unspin();
						document.id('selected_users').set('spinner').unspin();
						if (response.status == 1) {
							alert('all booked !');		
							redb2b.attendeesList();
						}
						else if (response.regs.length) {
							var errors = new Array();
							for (var i = 0; i < response.regs.length; i++) {
								var r = response.regs[i];
								if (r.status == 0) {
									errors.push(r.error);
								}
							}
							alert(errors.join("\n"));
						}
						else {
							alert(response.error);
						}
					}
				});
				req.send();
			});
			
			/**
			 * remove session
			 */
			document.id('redevent-admin').addEvent('click:relay(.deletexref)', function(e){
				if (!confirm(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM"))) {
					return;
				}
			});
			
			/**
			 * publish session
			 */
			document.id('redevent-admin').addEvent('click:relay(.publishxref)', function(e){
				redb2b.publishSession(this.getParent('tr').getProperty('xref'), 1);
			});
			
			/**
			 * unpublish session
			 */
			document.id('redevent-admin').addEvent('click:relay(.unpublishxref)', function(e){
				redb2b.publishSession(this.getParent('tr').getProperty('xref'), 0);
			});
			
			/**
			 * edit event
			 */
			document.id('redevent-admin').addEvent('click:relay(.editevent)', function(e){
				alert('non implemented yet');		
			});
			
			window.addEvent('beforeunload', function() {
				return confirm(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_CLOSE"));
			});

			/**
			 * edit ponumber
			 */
			document.id('redevent-admin').addEvent('change:relay(.ponumber)', function(e){
				var text = this.get('value');
				var rid = this.getParent('tr').getProperty('rid');
				var el = this;
				var req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=updateponumber&tmpl=component',
					data :{'rid' : rid, 'value' : text},
					onRequest: function(){
						el.set('spinner').spin();
				    },
					onSuccess : function(result) {
						el.unspin();
						if (!result.status) {
							alert(result.error);
						}
					}
				});
				req.send();
			});
			
			/**
			 * edit comments
			 */
			document.id('redevent-admin').addEvent('change:relay(.comments)', function(e){
				var text = this.get('value');
				var rid = this.getParent('tr').getProperty('rid');
				var el = this;
				var req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=updatecomments&tmpl=component',
					data :{'rid' : rid, 'value' : text},
					onRequest: function(){
						el.set('spinner').spin();
				    },
					onSuccess : function(result) {
						el.unspin();
						if (!result.status) {
							alert(result.error);
						}
					}
				});
				req.send();
			});

			/**
			 * edit status
			 */
			document.id('redevent-admin').addEvent('change:relay(.status)', function(e){
				alert('non implemented yet');		
			});

			document.id('redevent-admin').addEvent('click:relay(#editmemberscreen .ajaxsortcolumn)', function(e){
				e.stop();
				var form = this.getParent('form');
				form.getElement('.redajax_order').set('value', this.getProperty('ordercol'));
				form.getElement('.redajax_order_dir').set('value', this.getProperty('orderdir'));
				var req = new Request.HTML({
					url: 'index.php?option=com_redevent',
					data : form,
					onRequest : function(){
						form.set('spinner').spin();						
					},
					onSuccess : function(text) {
						form.empty().adopt(text).unspin();
					}
				});
				req.send();
			});
			
			document.id('main-course-results').addEvent('click:relay(.ajaxsortcolumn)', function(e){
				e.stop();
				var form = document.id('course-search-form');
				form.filter_order.value = this.getProperty('ordercol');
				form.filter_order_Dir.value = this.getProperty('orderdir');
				redb2b.getSessions();
			});
			
			document.id('main-course-results').addEvent('click:relay(.itemnav)', function(e){
				e.stop();
				var form = document.id('course-search-form');
				form.limitstart.value = this.getProperty('startvalue');
				redb2b.getSessions();
			});
			
			document.id('main-bookings').addEvent('click:relay(.ajaxsortcolumn)', function(e){
				e.stop();
				var form = document.id('org-form');
				form.bookings_order.value = this.getProperty('ordercol');
				form.bookings_order_dir.value = this.getProperty('orderdir');
				redb2b.searchBookings();
			});
			
			document.id('main-attendees').addEvent('click:relay(.ajaxsortcolumn)', function(e){
				e.stop();
				var form = document.id('org-form');
				form.members_order.value = this.getProperty('ordercol');
				form.members_order_dir.value = this.getProperty('orderdir');
				redb2b.attendeesList();
			});
		},
		
		getSessions : function() {
			var req = new Request({
				url: 'index.php?option=com_redevent&controller=frontadmin&task=searchsessions&tmpl=component',
				data: document.id('course-search-form'),
				onRequest : function(){
					document.id('main-course-results').set('spinner').spin();
				},
				onSuccess : function(response) {
					document.id('main-course-results').empty().set('html', response).unspin();
					redb2b.refreshTips();
				}
			});
			req.send();
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
					redb2b.refreshTips();
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
					redb2b.getSessions();
				}
			});
			req.send();			
		},
		
		attendeesList : function() {
			document.id('main-attendees').set('styles', {'display' : 'none'}).empty();
			if (document.id('filter_organization').get('value') > 0 && document.id('filter_session').get('value') > 0) {
				var orgform = document.id('org-form');
				var req = new Request.HTML({
					url: 'index.php?option=com_redevent&controller=frontadmin&task=getattendees&tmpl=component',
					data : {'xref' : document.id('filter_session').get('value'),
						'org' : document.id('filter_organization').get('value'),
						'members_order' : orgform.members_order.value,
						'members_order_dir' : orgform.members_order_dir.value
					},
					onRequest : function(){
						document.id('main-attendees').set('spinner').spin();
					},
					onSuccess : function(text) {
						resdiv = document.id('main-attendees').adopt(text).set('styles', {'display' : 'block'}).unspin();
						Array.each(redb2b.selected, function(val) {
							var cid = document.id('cid' + val);
							if (cid) {
								cid.setProperty('checked', 'checked');
							}
						});
						redb2b.refreshTips();
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
		},
		
		publishSession : function(xref, state) {
			var req = new Request.JSON({
				url: 'index.php?option=com_redevent&controller=frontadmin&task=publishxref&tmpl=component',
				data : {'xref' : xref,
					'state' : state
				},
				onSuccess : function(result) {
					if (result.status) {
						redb2b.getSessions();
					}
					else {
						alert(result.error);
					}
				}
			});
			req.send();
		},
		
		editmember : function(id) {
			req = new Request({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=editmember&tmpl=component',
				data : {'uid' : id},
				method : 'post',
				onSuccess : function(responseText){
					document.id('redadmin-main').hide();
					if (document.id('editmemberscreen')) {
						document.id('editmemberscreen').dispose();
					}
					var editdiv = new Element('div', {'id' : 'editmemberscreen'}).set('html', responseText);
					editdiv.addEvent('click:relay(#closeeditmember)', function(e){
						document.id('editmemberscreen').dispose();
						document.id('redadmin-main').show();
					});
					editdiv.inject('redadmin-toolbar', 'after');
				}
			});
			req.send();			
		},
		
		refreshTips : function(){
			myTips = new Tips(".hasTip");
		}
};
