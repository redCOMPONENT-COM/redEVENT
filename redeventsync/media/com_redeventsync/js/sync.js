window.addEvent('domready', function(){

	document.id('startsync').addEvent('click', function(){

		redeventsync.syncSessions();

	});
});

var redeventsync = {

	syncSessions : function(){

		var sync_req = new Request.JSON({
			url : 'index.php?option=com_redeventsync&view=sync&task=sessions&tmpl=component',
			data : document.id('adminForm'),
			useSpinner: true,
			spinnerTarget: document.id('theform'),
			method : 'post',
			onRequest : function() {
				document.id('results').empty();
			},
			onFailure : function() {
				alert('Something went wrong, please check logs');
			},
			onSuccess : function(json_resp) {
				var res = document.id('results');
				res.empty();
				json_resp.each(function(text) {
					var sp = new Element('div').appendText(text).inject(res);
				});

				// Then update attendees
				redeventsync.syncAttendees();
			}
		});
		sync_req.send();

	},

	syncAttendees : function() {
		var sync_req = new Request.JSON({
			url : 'index.php?option=com_redeventsync&view=sync&task=attendees&tmpl=component',
			data : document.id('adminForm'),
			useSpinner: true,
			spinnerTarget: document.id('theform'),
			method : 'post',
			onFailure : function() {
				alert('Something went wrong, please check logs');
			},
			onSuccess : function(json_resp) {
				var res = document.id('results');

				json_resp.each(function(text) {
					var sp = new Element('div').appendText(text).inject(res);
				});
			}
		});
		sync_req.send();
	}
}
