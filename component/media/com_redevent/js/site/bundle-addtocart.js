/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008-2015 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){

	var listtemplate, selectedtemplate, selectRowTemplate;

	var showMore = function(eventDiv)
	{
		var selectDiv = eventDiv.next();
		var bundleeventid = eventDiv.find('input[name="bundleevent[]"]').val();
		var selected = eventDiv.find('input[name="selected[]"]').val();
		var tbody = selectDiv.find('table tbody');
		var limitStart = tbody.children().length;

		$.ajax({
			url: 'index.php?option=com_redevent&format=json&task=bundle.getsessions',
			data: "bundleeventid=" + bundleeventid + "limitstart=" + limitStart
		})
		.done(function(response){
			if (response.data.length) {
				response.data.forEach(function(row) {
					tbody.append(selectRowTemplate(row));
				});
			}
		});
	};

	$(function() {
		listtemplate = Handlebars.compile($("#select-session-template").html());
		selectedtemplate = Handlebars.compile($("#selected-session-template").html());
		selectRowTemplate = Handlebars.compile($("#select-row-template").html());

		$('div.bundle-event').each(function(index, element){
			var $element = $(element);
			var bundleeventid = $element.find('input[name="bundleevent[]"]').val();

			// Display after getting data
			$.ajax({
				url: 'index.php?option=com_redevent&format=json&task=bundle.defaultsession',
				data: "bundleeventid=" + bundleeventid
			})
			.done(function(response){
				$element.find('table.selected-date tbody').empty().append(selectedtemplate({
					id: response.data.id,
					label: response.data.label,
					prices: response.data.prices,
					singleprice: response.data.singleprice
				}));
			});
		});

		// Toggle session select div
		$('.select-date-button').click(function() {
			var eventDiv = $(this).closest('div.bundle-event');
			var bundleeventid = eventDiv.find('input[name="bundleevent[]"]').val();
			var totalSessions = eventDiv.attr('total');

			// Remove list select if already there
			if (eventDiv.next().attr('class') == 'select-session-list') {
				eventDiv.next().remove();
				return;
			}

			// Display after getting data
			$.ajax({
				url: 'index.php?option=com_redevent&format=json&task=bundle.sessionfilters',
				data: {
					bundleeventid: bundleeventid,
				}
			})
			.done(function(response){
				eventDiv.after(listtemplate({
					total: totalSessions,
					venueoptions: [
						{value:1, text:'venue 1'},
						{value:2, text:'venue 2'}
					],
					timeoptions: [],
					languageoptions: []
				}));

				showMore(eventDiv);
			});
		});
	});
})(jQuery);
