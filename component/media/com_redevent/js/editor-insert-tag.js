/**
 * javascript for adding tag to editor
 */
var redeventEditorInsertTag = (function($) {
	$(function(){
		$('.tagInsertModal').on('show', function(){
			var $element = $(this);
			var fieldId = $element.attr('field');
			$element.find('iframe').attr("src", 'index.php?option=com_redevent&view=tags&tmpl=component&field=' + fieldId);
		});
	});

	// Provide an interface
	return function(tag, editor) {
		jInsertEditorText(tag, editor);
		$('.tagInsertModal').modal('hide');
	};
})(jQuery);
