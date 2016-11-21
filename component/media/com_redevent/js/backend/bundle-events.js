/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008-2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
var redeventBundleEvents = (function($){

	var template, currentRow;

	/**
	 * Initial load
	 */
	var loadRows = function(){
		var bundleId = $('#jform_id').val();

		if (!bundleId) {
			return;
		}

		$.ajax({
			url: 'index.php?option=com_redevent&task=bundle.events&format=json&id=' + bundleId
		}).done(function(response){
			response.forEach(function(row){
				if (row.sessions.length) {
					row.sessions.forEach(function(session){
						data = {
							'event': {
								id: row.id,
								title: row.title
							},
							'session': {
								id: session.id,
								title: session.formatted_start_date + '@' + session.venue
							}
						}
						addRow(data);
					});
				}
				else {
					data = {
						'event': {
							id: row.id,
							title: row.title
						}
					}
					addRow(data);
				}
			});
		});
	};

	/**
	 * Add a row
	 *
	 * @param data
	 */
	var addRow = function(data) {
		var context = data;
		var html = template(context);

		$("#bundle-events tbody").append(html);
	};

	/**
	 * Select event callback
	 *
	 * @param id
	 * @param title
	 */
	var selectEvent = function(id, title) {
		currentRow.find('[name="event_id[]"]').val(id);
		currentRow.find('[name="event_name[]"]').val(title);

		$("#eventModal").modal('hide');
	};

	/**
	 * Select session callback
	 *
	 * @param id
	 * @param title
	 */
	var selectSession = function(id, title) {
		currentRow.find('[name="session_id[]"]').val(id);

		if (!id) {
			currentRow.find('[name="session_name[]"]').val('');
			return;
		}

		// Get session details
		$.ajax({
			'url' : 'index.php?option=com_redevent&task=session.item&format=json&id=' + id
		}).done(function(response) {
			$("#sessionModal").modal('hide');

			if (response.error) {
				alert(response.error);

				return;
			}

			currentRow.find('[name="session_name[]"]').val(response.formatted_start_date + '@' + response.venue);
		});
	};

	$(function() {
		template = Handlebars.compile($("#row-template").html());

		loadRows();

		$('#bundle-events').on('click', '.add-row', function() {
			addRow({});
		});

		$('#bundle-events').on('click', '.delete-row', function() {
			$(this).closest('tr').remove();
		});

		$('#bundle-events').on('click', '.reset-session', function() {
			current = $(this).closest('tr');
			selectSession(0);
		});

		$('#bundle-events').on('click', '.select-event', function() {
			currentRow = $(this).closest('tr');

			$("#eventModal iframe").attr(
				"src", "index.php?option=com_redevent&view=events&layout=element&tmpl=component&function=redeventBundleEvents.selectEvent"
			);

			$("#eventModal").modal('show');
		});

		$('#bundle-events').on('click', '.select-session', function() {
			currentRow = $(this).closest('tr');
			var event_id = currentRow.find('[name="event_id[]"]').val();

			if (!event_id) {
				alert(Joomla.JText._('COM_REDEVENT_VIEW_BUNDLE_JS_SELECT_EVENT_FIRST'));
				return;
			}

			$("#sessionModal iframe").attr(
				"src", "index.php?option=com_redevent&view=sessions&layout=element&tmpl=component&function=redeventBundleEvents.selectSession&filter[event]=" + event_id
			);

			$("#sessionModal").modal('show');
		});
	});

	return {
		selectEvent: selectEvent,
		selectSession: selectSession
	};
})(jQuery);
