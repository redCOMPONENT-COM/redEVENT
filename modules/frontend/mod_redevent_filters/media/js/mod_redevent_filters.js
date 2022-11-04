/**
 * Redevent filter module javascript
 */
(function($){
	modReFilters = (function(options) {

		var settings = {
			'filtersDivId': '#modRedeventFilters',
			'listFormId': '#adminForm'
		};

		if (options) {
			$.extend(settings, options);
		}

		/**
		 * return list form element
		 *
		 * @returns Element
		 */
		var getListForm = function() {
			return $(settings.listFormId);
		};

		/**
		 * return list form element
		 *
		 * @returns Element
		 */
		var getFiltersDiv = function() {
			return $(settings.filtersDivId);
		};

		/**
		 * Copy filter fields to list form
		 */
		var syncForm = function() {
			var $listForm = getListForm();
			var $filtersDiv = getFiltersDiv();

			// First general text search
			if ($listForm.find('[name=filter_text]') && $listForm.find('#filter')) {
				$listForm.find('#filter_type').val('event');
				$listForm.find('#filter').val($filtersDiv.find('[name=filter_text]').val());
			}

			if ($listForm.find('#clonedDiv')) {
				$listForm.find('#clonedDiv').remove();
			}

			var cloneDiv = $('<div/>').prop('id', 'clonedDiv').css('display', 'none').appendTo($listForm);

			$filtersDiv.find('input').each(function(index, element) {
				var $element = $(element);
				$element.clone().val($element.val()).appendTo(cloneDiv);
			});
		};

		return {
			'syncForm': syncForm,
			'getFiltersDiv': getFiltersDiv,
			'getListForm': getListForm
		};
	})();

	$(document).ready(function() {
		modReFilters.syncForm();

		modReFilters.getFiltersDiv().find('input').change(function() {
			modReFilters.syncForm();
			modReFilters.getListForm().submit();
		});

		modReFilters.getFiltersDiv().find('button').click(function(){
			$('#modRedeventFilters').find('input').val('').trigger('change');
		});
	});
})(jQuery);


