var jSelectSession = (function($){
	$(function(){
		$('.sessionmodal-buttons .reset-button').click(function(){
			var fieldId = $(this).closest('div.sessionmodal-buttons').attr('fieldId');
			$("#" + fieldId + "_id").val(0);
			$("#" + fieldId + "_name").val("");
		});
	});

	return function(id, title, fieldId) {
		$("#" + fieldId + "_id").val(id);
		$("#" + fieldId + "_name").val(title);
		$('.sessionFieldModal').modal('hide');
	};
})(jQuery);
