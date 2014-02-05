/**
 * redevent quickbook module javascript
 */

document.addEvent('domready', function(){

	modReFilters.init();
	modReFilters.syncForm();

	modReFilters.getFiltersDiv().getElements('input').addEvent('change', function() {
		modReFilters.syncForm();
		modReFilters.getListForm().submit();
	});

	modReFilters.getFiltersDiv().getElement('button').addEvent('click', function(){
		this.getParent('div').getElement('input').set('value', '').fireEvent('change');
	});
});

modReFilters = {

	options: {
		'filtersDivId': 'modRedeventFilters',
		'listFormId': 'adminForm'
	},

	init: function(options) {
		if (options) {
			this.options = Object.merge(this.options, options);
		}
	},

	/**
	 * return list form element
	 *
	 * @returns Element
	 */
	getListForm: function() {
		return document.id(this.options.listFormId);
	},

	/**
	 * return list form element
	 *
	 * @returns Element
	 */
	getFiltersDiv: function() {
		return document.id(this.options.filtersDivId);
	},

	/**
	 * Copy filter fields to list form
	 */
	syncForm: function() {
		var listForm = this.getListForm();
		var filtersDiv = this.getFiltersDiv();

		// First general text search
		if (filtersDiv.getElement('[name=filter_text]') && listForm.getElement('#filter')) {
			listForm.getElement('#filter_type').set('value', 'event');
			listForm.getElement('#filter').set('value', filtersDiv.getElement('[name=filter_text]').get('value'));
		}

		if (listForm.getElement('#clonedDiv')) {
			listForm.getElement('#clonedDiv').dispose();
		}

		var cloneDiv = new Element('div', {'id': 'clonedDiv'}).setStyle('display', 'none').inject(listForm);

		filtersDiv.getElements('input').each(function(el) {
			var cp = el.clone().set('value', el.get('value')).inject(cloneDiv);
		});
	}


};
