(function($){

	var syncSessions = function() {

		$.ajax({
			url: 'index.php?option=com_redeventsync&task=sync.sessions&tmpl=component',
			data: $('#adminForm').serialize(),
			type : 'POST',
			dataType: 'json',
			beforeSend: function (xhr) {
				$('#results').empty();
			}
		}).done(function(data) {
			var res = $('#results');
			res.empty();

			$.each(data, function(id, text) {
				var sp = $('<div>').html(text).appendTo(res);
			});

			// Then update attendees
			redeventsync.syncAttendees();
		});

	};

	var syncAttendees = function() {

		$.ajax({
			url: 'index.php?option=com_redeventsync&task=sinc.attendees&tmpl=component',
			data: $('#adminForm').serialize(),
			type : 'POST',
			dataType: 'json',
			beforeSend: function (xhr) {
				$('#results').empty();
			}
		}).done(function(data) {
			var res = $('#results');

			$.each(data, function(id, text) {
				var sp = $('<div>').html(text).appendTo(res);
			});
		});
	};

	$(function() {
		$('#startsync').click(syncSessions);
	});

})(jQuery);
