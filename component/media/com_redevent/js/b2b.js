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

			redb2b.updateBreadCrumbs();

			/**
			 * update sessions options when selecting event
			 */
			document.id('filter_event').addEvent('change', function(){

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b course search',
						'eventAction': 'select',
						'eventLabel': 'filter event'
					});
				}

				redb2b.updateSessionSearchFields();
			});

			/**
			 * update sessions options when selecting venue
			 */
			document.id('filter_venue').addEvent('change', function(){
				redb2b.updateSessionSearchFields();

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b course search',
						'eventAction': 'select',
						'eventLabel': 'filter venue'
					});
				}
			});

			/**
			 * update sessions options when selecting venue
			 */
			document.id('filter_category').addEvent('change', function(){
				redb2b.updateSessionSearchFields();

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b course search',
						'eventAction': 'select',
						'eventLabel': 'filter category'
					});
				}
			});

			/**
			 * update sessions options when selecting venue
			 */
			document.id('filter_session').addEvent('change', function(){
				document.id('book-xref').set('value', this.get('value'));
				redb2b.getMembersList();
				redb2b.getSessions();
				redb2b.updateBreadCrumbs();

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b course search',
						'eventAction': 'select',
						'eventLabel': 'filter session'
					});
				}
			});

			/**
			 * update organization bookings when selecting organization
			 */
			document.id('filter_organization').addEvent('change', function(){
				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b organization',
						'eventAction': 'select',
						'eventLabel': 'organization'
					});
				}

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
				redb2b.getMembersList();
			}).fireEvent('change');

			/**
			 * update organization bookings when selecting person
			 */
			document.id('search_person').addEvent('click', function(){
				redb2b.searchBookings();
				// Display organization users ?
				redb2b.getMembersList();

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b organization',
						'eventAction': 'select',
						'eventLabel': 'person'
					});
				}
			});

			/**
			 * update organization bookings when selecting person
			 */
			document.id('org-form').addEvent('submit', function(e){
				e.stop();
			});

            /**
             * update organization bookings when selecting member
             */
            document.id('filter_person').addEvent('keydown', function(event){
                if (event.key == 'enter') {
                    redb2b.searchBookings();
                }

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b organization',
						'eventAction': 'select',
						'eventLabel': 'person'
					});
				}
            });

			/**
			 * update organization bookings when selecting session status active
			 */
			$$('[id^=filter_person_]').addEvent('change', function(){
				redb2b.searchBookings();
			});

			/**
			 * update organization bookings when resetting filter person field
			 */
			document.id('reset_person').addEvent('click', function(){
				document.id('filter_person').set('value', '');
				document.id('filter_organization').fireEvent('change');

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b organization',
						'eventAction': 'reset'
					});
				}
			});

			/**
			 * search for sessions
			 */
			document.id('search-course').addEvent('click', function() {
				redb2b.getSessions();

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b',
						'eventAction': 'search course'
					});
				}
			});

			/**
			 * update course search when clicking on book session button in lists
			 */
			document.id('redevent-admin').addEvent('click:relay(.bookthis)', function(e){
				e.stop();
				var id = this.getProperty('xref');
				redb2b.selectSession(id);

				if (typeof ga !== 'undefined')
				{
					ga('send',{
						'hitType': 'event',
						'eventCategory': 'b2b',
						'eventAction': 'select book session'
					});
				}
			});

			/**
			 * Reset sessions search
			 */
			document.id('search-course-reset').addEvent('click', function(){
				document.id('filter_event').set('value', '');
				document.id('filter_session').empty();
				document.id('filter_venue').set('value', '');
				document.id('filter_category').set('value', '');
				document.id('filter_from').set('value', '');
				document.id('filter_to').set('value', '');
				redb2b.updateSessionSearchFields();
				redb2b.getMembersList();
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

						if (typeof ga !== 'undefined')
						{
							ga('send',{
								'hitType': 'event',
								'eventCategory': 'b2b booking',
								'eventAction': 'selected user'
							});
						}
					}
				}
				else {
					/** remove from selected list **/
					document.id('member'+id).dispose();
					redb2b.selected.erase(id);
					if (!redb2b.selected.length) {
						redb2b.resetSelected();
					}

					if (typeof ga !== 'undefined')
					{
						ga('send',{
							'hitType': 'event',
							'eventCategory': 'b2b booking',
							'eventAction': 'unselected user'
						});
					}
				}
			});

			/**
			 * edit own account
			 */
			$$('#redevent-admin .myaccount').addEvent('click', function(e){
				e.stop();
				var id = this.getProperty('uid');
				redb2b.editmember(id);
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
			document.id('redevent-admin').addEvent('click:relay(#add-employee)', function(e){
				redb2b.addmember();
			});

			/**
			 * update member
			 */
			document.id('redevent-admin').addEvent('click:relay(.update-employee)', function (e) {
				req = new Request.JSON({
					url: 'index.php?option=com_redevent&controller=frontadmin&task=update_user&tmpl=component',
					data: document.id('member-update'),
					format: 'json',
					method: 'post',
					onRequest: function () {

					},
					onSuccess: function (response) {
						if (response.status == 1) {
							document.id('editmemberscreen').dispose();
							document.id('redadmin-main').show();
							redb2b.getMembersList();
						}
						else {
							alert(response.error);
						}
					}
				});
				req.send();
			});

            /**
             * update member
             */
            document.addEvent('click:relay(#sbox-content .update-employee)', function(e){
                var user_id       = document.id('member_id').value;
                var user_username = document.id('member_username').value;
                var user_name     = document.id('member_name').value;
                var user_email    = document.id('member_email').value;
                req = new Request.JSON({
                    url : 'index.php?option=com_redevent&controller=frontadmin&task=update_user&tmpl=component',
                    data : document.id('member-update'),
                    format: 'json',
                    method : 'post',
                    onRequest: function(){

                    },
                    onSuccess : function(response){
                        if (response.status == 1) {
                            document.id('sbox-window').close();
                            redb2b.getMembersList();
                        }
                        else {
                            alert(response.error);
                        }
                    }
                });
                req.send();
            });

			/**
			 * remove registration
			 */
			document.id('redevent-admin').addEvent('click:relay(.unregister)', function(e){
				var confirmText = this.getProperty('confirmtext');
				if (confirm(confirmText)) {
					var register_id = this.getParent('tr').getProperty('rid');
					req = new Request.JSON({
						url : 'index.php?option=com_redevent&controller=frontadmin&task=cancelreg&tmpl=component&from=b2b',
						data : {'rid' : register_id},
						method : 'post',
						onRequest: function(){
							document.id('attendees-tbl').set('spinner').spin();
					    },
						onSuccess : function(response){
							document.id('attendees-tbl').set('spinner').unspin();
							if (response.status == 1) {
								redb2b.getMembersList();
							}
							else {
								alert(response.error);
							}

							if (typeof ga !== 'undefined')
							{
								ga('send',{
									'hitType': 'event',
									'eventCategory': 'b2b booking',
									'eventAction': 'unregistered user'
								});
							}
						}
					});
					req.send();
				}
			});

			document.id('book-course').addEvent('click', function(){
				if (!document.id('book-xref').get('value')) {
					alert(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_SELECT_SESSION_FIRST"));
					return false;
				}
				req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=quickbook&tmpl=component&from=b2b',
					data : document.id('selected_users'),
					method : 'post',
					onRequest: function(){
						document.id('attendees-tbl').set('spinner').spin();
						document.id('selected_users').set('spinner').spin();
				    },
					onFailure : function() {
						alert('Something went wrong');
						document.id('attendees-tbl').unspin();
						document.id('selected_users').unspin();
						redb2b.getMembersList();
					},
					onSuccess : function(response){
						document.id('attendees-tbl').unspin();
						document.id('selected_users').unspin();
						if (response.status == 1) {
							redb2b.getMembersList();
							redb2b.addGoogleAnalyticsTrans(response);
							alert(response.message);
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
				if (confirm(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_PUBLISH"))) {
					redb2b.publishSession(this.getParent('tr').getProperty('xref'), 1);
				}
			});

			/**
			 * unpublish session
			 */
			document.id('redevent-admin').addEvent('click:relay(.unpublishxref)', function(e){
				if (confirm(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_UNPUBLISH"))) {
					redb2b.publishSession(this.getParent('tr').getProperty('xref'), 0);
				}
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
						el.set('tip', el.get('value'));
						redb2b.refreshTips();
					}
				});
				req.send();
			});

			/**
			 * edit status
			 */
			document.id('redevent-admin').addEvent('click:relay(.statusicon)', function(e){
				// Disable for maersk
				return;
				var current = this.getProperty('current');
				var rid = this.getParent('tr').getProperty('rid');
				var el = this;
				var req = new Request.JSON({
					url : 'index.php?option=com_redevent&controller=frontadmin&task=updatestatus&tmpl=component',
					data :{'rid' : rid, 'value' : current},
					onRequest: function(){
						el.set('spinner').spin();
				    },
					onSuccess : function(result) {
						el.unspin();
						if (!result.status) {
							alert(result.error);
						}
						var parent = el.getParent();
						el.dispose();
						parent.set('html', result.html);
						redb2b.refreshTips();
					}
				});
				req.send();
			});

			document.id('redevent-admin').addEvent('click:relay(#editmemberscreen .ajaxsortcolumn)', function(e){
				e.stop();
				var form = this.getParent('form');
				form.getElement('.redajax_order').set('value', this.getProperty('ordercol'));
				form.getElement('.redajax_order_dir').set('value', this.getProperty('orderdir'));
				redb2b.updateFormList(form);
				redb2b.refreshTips();
			});

			document.id('redevent-admin').addEvent('click:relay(#editmemberscreen .itemnav)', function(e){
				e.stop();
				var form = this.getParent('form');
				form.getElement('.redajax_limitstart').set('value', this.getProperty('startvalue'));
				redb2b.updateFormList(form);
				redb2b.refreshTips();
			});

			document.id('redevent-admin').addEvent('click:relay(#main-course-results .ajaxsortcolumn)', function(e){
				e.stop();
				var form = document.id('course-search-form');
				form.filter_order.value = this.getProperty('ordercol');
				form.filter_order_Dir.value = this.getProperty('orderdir');
				redb2b.getSessions();
			});

			$$('#main-course-results .ajaxsortcolumn').addEvent('click', function(e){
				e.stop();
				var form = document.id('course-search-form');
				form.filter_order.value = this.getProperty('ordercol');
				form.filter_order_Dir.value = this.getProperty('orderdir');
				redb2b.getSessions();
			});

			document.id('redevent-admin').addEvent('click:relay(#main-course-results .itemnav)', function(e){
				e.stop();
				var form = document.id('course-search-form');
				form.limitstart.value = this.getProperty('startvalue');
				redb2b.getSessions();
			});

			$$('#main-course-results .itemnav').addEvent('click', function(e){
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

			document.id('main-bookings').addEvent('click:relay(.itemnav)', function(e){
				e.stop();
				var form = document.id('org-form');
				form.bookings_limitstart.value = this.getProperty('startvalue');
				redb2b.searchBookings();
			});

			document.id('main-attendees').addEvent('click:relay(.ajaxsortcolumn)', function(e){
				e.stop();
				var form = document.id('org-form');
				form.members_order.value = this.getProperty('ordercol');
				form.members_order_dir.value = this.getProperty('orderdir');
				redb2b.getMembersList();
			});

			document.id('main-attendees').addEvent('click:relay(.itemnav)', function(e){
				e.stop();
				var form = document.id('org-form');
				form.members_limitstart.value = this.getProperty('startvalue');
				redb2b.getMembersList();
			});

			// For edit events and sessions
			SqueezeBox.initialize({handler: 'iframe', size: {x: 750, y: 500}});
			document.id('redevent-admin').addEvent('click:relay(.xrefmodal)', function(e) {
				e.stop();
				SqueezeBox.fromElement(this);
			});

			$('sbox-btn-close').addEvent('click', function() {
				redb2b.getSessions();
			});
		},

    /**
     * get sessions according to search filter
     */
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
					redb2b.getMembersList();
					redb2b.updateBreadCrumbs();
				}
			});
			req.send();
		},

		updateSessionSearchFields : function() {
			redb2b.updateEventField();
			redb2b.updateSessionField();
			redb2b.updateVenueField();
			redb2b.updateCategoryField();
			redb2b.getSessions();
			redb2b.updateBreadCrumbs();
		},

		updateEventField : function(async) {
			async = typeof async !== 'undefined' ? async : true;
			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=eventsoptions&tmpl=component',
				data :document.id('course-search-form'),
				async : async,
				onRequest: function(){
					document.id('filter_event').set('spinner').spin();
			    },
				onSuccess : function(options) {
					var sel = document.id('filter_event').unspin();
					var current = sel.get('value');

					sel.empty();
					new Element('option', {'value': ''}).set('text', Joomla.JText._("COM_REDEVENT_FILTER_SELECT_EVENT")).inject(sel);
					if (options.length) {
						options.each(function(el){
							new Element('option', {'value': el.value}).set('text', el.text).inject(sel);
						});
					}
					sel.set('value', current);
				}
			});
			req.send();
		},

		updateSessionField : function(async) {
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
					var current = sel.get('value');

					sel.empty();
					new Element('option', {'value': ''}).set('text', Joomla.JText._("COM_REDEVENT_FILTER_SELECT_SESSION")).inject(sel);
					if (options.length) {
						options.each(function(el){
							new Element('option', {'value': el.value}).set('text', el.text).inject(sel);
						});
					}
					sel.set('value', current);

					// redb2b.getMembersList();
				}
			});
			req.send();
		},

		updateVenueField : function(async) {
			async = typeof async !== 'undefined' ? async : true;
			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=venuesoptions&tmpl=component',
				data :document.id('course-search-form'),
				async : async,
				onRequest: function(){
					document.id('filter_venue').set('spinner').spin();
			    },
				onSuccess : function(options) {
					var sel = document.id('filter_venue').unspin();
					var current = sel.get('value');

					sel.empty();
					new Element('option', {'value': ''}).set('text', Joomla.JText._("COM_REDEVENT_FILTER_SELECT_VENUE")).inject(sel);
					if (options.length) {
						options.each(function(el){
							new Element('option', {'value': el.value}).set('text', el.text).inject(sel);
						});
					}
					sel.set('value', current);
				}
			});
			req.send();
		},

		updateCategoryField : function(async) {
			async = typeof async !== 'undefined' ? async : true;
			var req = new Request.JSON({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=categoriesoptions&tmpl=component',
				data :document.id('course-search-form'),
				async : async,
				onRequest: function(){
					document.id('filter_category').set('spinner').spin();
			    },
				onSuccess : function(options) {
					var sel = document.id('filter_category').unspin();
					var current = sel.get('value');

					sel.empty();
					new Element('option', {'value': ''}).set('text', Joomla.JText._("COM_REDEVENT_FILTER_SELECT_CATEGORY")).inject(sel);
					if (options.length) {
						options.each(function(el){
							new Element('option', {'value': el.value}).set('text', el.text).inject(sel);
						});
					}
					sel.set('value', current);
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
					redb2b.updateSessionField(false);
					document.id('filter_session').set('value', id);
					document.id('book-xref').set('value', id);
					redb2b.getMembersList();
					redb2b.updateBreadCrumbs();
				}
			});
			req.send();
		},

		/**
		 * return list of organization members to select for booking
		 */
		getMembersList : function() {
			document.id('main-attendees').set('styles', {'display' : 'none'}).empty();
			if (document.id('filter_organization').get('value') > 0) {
				var orgform = document.id('org-form');
				var req = new Request.HTML({
					url: 'index.php?option=com_redevent&controller=frontadmin&task=getattendees&tmpl=component',
					data : {'xref' : document.id('filter_session').get('value'),
						'org' : document.id('filter_organization').get('value'),
						'filter_person' : document.id('filter_person').get('value'),
						'members_order' : orgform.members_order.value,
						'members_order_dir' : orgform.members_order_dir.value,
						'members_limitstart' : orgform.members_limitstart.value
					},
					onRequest : function(){
						document.id('main-attendees').set('spinner').spin();
					},
					onSuccess : function(text) {
						resdiv = document.id('main-attendees').empty().adopt(text).set('styles', {'display' : 'block'}).unspin();
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

		addmember : function() {
			var orgId = document.id('filter_organization').get('value');

			if (!orgId)
			{
				alert(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_EDIT_MEMBER_MUST_SELECT_ORGANIZATION"));
				return;
			}

			var dummylink = new Element('a', {
				href: "index.php?option=com_redevent&controller=frontadmin&task=editmember&tmpl=component&modal=1&orgId=" + orgId,
				rel: "{handler: 'iframe', size: {x: 800, y: 400}, onClose: redb2b.getMembersList}"
			});
			SqueezeBox.fromElement(dummylink);
		},

		editmember : function(id) {
			var orgId = document.id('filter_organization').get('value');

			if (!orgId)
			{
				alert(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_EDIT_MEMBER_MUST_SELECT_ORGANIZATION"));
				return;
			}
			req = new Request({
				url : 'index.php?option=com_redevent&controller=frontadmin&task=editmember&tmpl=component',
				data : {'uid' : id, 'orgId': orgId},
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
					redb2b.refreshTips();
				}
			});
			req.send();
		},

		updateFormList : function(form) {
			var req = new Request.HTML({
				url: 'index.php?option=com_redevent',
				data : form,
				onRequest : function(){
					form.set('spinner').spin();
				},
				onSuccess : function(text) {
					form.empty().adopt(text).unspin();
					redb2b.refreshTips();
				}
			});
			req.send();
		},

		refreshTips : function(){
			myTips = new Tips(".hasTip", {text : 'tip'});
		},

		updateBreadCrumbs : function() {
			var elements = new Array();
			elements.push(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_YOU_ARE_HERE"));
			elements.push(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_OVERVIEW"));

			if (document.id('filter_session').get('value')) {
				elements.push(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_BOOK"));
			}
			else if (document.id('filter_event').get('value')
				|| document.id('filter_venue').get('value')
				|| document.id('filter_category').get('value')
				|| document.id('filter_from').get('value')
				|| document.id('filter_to').get('value')
				) {
				elements.push(Joomla.JText._("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_SEARCH_RESULTS"));
			}

			var bc = document.id('main-breadcrumb');
			bc.empty();

			var separator = ' <span class="divider">></span>';

			Array.each(elements, function(val) {
				var text = val;
				if (val != elements.getLast()) {
					text += separator;
				}
				var el = new Element('li').set('html', text);
				el.inject(bc);
			});
		},

	addGoogleAnalyticsTrans : function(response) {
		if (!ga)
		{
			return;
		}

		var currency;
		var total = 0.0;

		for (var i = 0; i < response.regs.length; i++) {
			var r = response.regs[i];
			currency = r.details.currency;
			total += parseFloat(r.details.price);
		}

		ga('ecommerce:addTransaction', {
			'id' : response.submit_key, // transaction ID - required
			'affiliation' : gaAffiliation, // affiliation or store name
			'revenue' :total,
			'currency': currency
		});

		for (var i = 0; i < response.regs.length; i++) {
			var r = response.regs[i];
			ga('ecommerce:addItem', {
				'id' : response.submit_key,
				'name' : r.details.event_name + ' @ ' + r.details.venue + '(session ' + r.details.xref + ')',
				'sku' :r.details.event_name,
				'category' : redb2b.gaJoinCategoyNames(r.details.categories),
				'price' : r.details.price,    // Unit price.
				'currency' :r.details.currency,
				'quantity' : 1    // Unit quantity.
			});
		}

		ga('ecommerce:send');
	},

	gaJoinCategoyNames : function(categories) {
		var names = [];
		for (var i = 0; i < categories.length; i++) {
			names.push(categories[i].catname);
		}
		return names.join(',');
	}

};
