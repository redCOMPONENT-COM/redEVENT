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
			url: 'index.php?option=com_redevent&format=json&task=bundle.sessions',
			data: "bundleeventid=" + bundleeventid + "&limitstart=" + limitStart
		})
		.done(function(response){
			if (response.data.length) {
				response.data.forEach(function(row) {
					row.selected = (row.id == selected);

					if (row.maxattendees > 0) {
						row.places = row.left >= 10 ? "10+" : row.left;
					}
					else {
						row.places = "10+";
					}

					tbody.append(selectRowTemplate(row));
				});
			}
		});
	};

	var updateSelected = function(eventDiv, sessionData) {
		eventDiv.find('table.selected-date tbody').empty().append(selectedtemplate(sessionData));
		eventDiv.change('[name="sessionpricegroup[]"]', updatePrices);
		updatePrices();
	};

	var updatePrices = function(){
		var total = 0;
		var currency = "";

		$('div.bundle-event').each(function(index, element){
			var $element = $(element);
			var priceElement = $element.find('[name="sessionpricegroup[]"]');
			var price = 0;
			var pcurrency = "";

			if (priceElement.prop('tagName') == 'INPUT') {
				price = priceElement.attr('price');
				pcurrency = priceElement.attr('currency');
			}
			else {
				var option = priceElement.find('option:selected');
				price = option.attr('price');
				pcurrency = option.attr('currency');
			}

			price = parseFloat(price) * $element.find('[name="participants[]"]').val();

			if (price) {
				total += price;
				currency = pcurrency;
				$element.find('td.session-total').text(pcurrency + ' ' + price.toFixed(2));
			}
			else {
				$element.find('td.session-total').text("-");
			}
		});

		$('#grand-total .price').text(currency + ' ' + total.toFixed(2));
	}

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
				updateSelected($element, response.data);
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
				data: "bundleeventid=bundleeventid"
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

				// Select session
				eventDiv.next().on('click', '.do-select', function(){
					var sessionId = $(this).attr('sessionid');

					// Display after getting data
					$.ajax({
						url: 'index.php?option=com_redevent&format=json&task=bundle.session',
						data: "id=" + sessionId
					})
					.done(function(response){
						updateSelected(eventDiv, response.data);
						eventDiv.next().remove();
					});
				})
				.click('.show-more-button button', function() {
					showMore(eventDiv)
				});

				showMore(eventDiv);
			});
		});

		$('#add-to-cart-button button').click(function(){
			$(this).closest('form').submit();
		})
	});
})(jQuery);
