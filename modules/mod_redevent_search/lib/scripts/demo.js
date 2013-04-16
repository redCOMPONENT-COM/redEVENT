/*jslint  browser: true, white: true, plusplus: true */
/*global $: true */
var j = jQuery.noConflict();
//alert(window.location);
j(function () {
    'use strict';

    // Load countries then initialize plugin:
    j.ajax({
        //url: 'modules/mod_redevent_search/lib/content/countries.txt',
		url: window.location,
		type : 'POST',
        dataType: 'json',
		data : 'search=ajax',
    }).done(function (source) {

        var eventArray = j.map(source, function (value, key) { return { value: value, data: key }; }),
            countries = j.map(source, function (value) { return value; });

        // Setup jQuery ajax mock:
        j.mockjax({
            url: '*',
            responseTime:  200,
            response: function (settings) {
                var query = settings.data.query,
                    queryLowerCase = query.toLowerCase(),
                    suggestions = j.grep(countries, function(country) {
                         return country.toLowerCase().indexOf(queryLowerCase) !== -1;
                    }),
                    response = {
                        query: query,
                        suggestions: suggestions
                    };

                this.responseText = JSON.stringify(response);
            }
        });
		
		var sURL = window.location;
		
        // Initialize ajax autocomplete:
        j('#filter_text').autocomplete({
            //serviceUrl:'/autosuggest/service/url',
			serviceUrl:'',
			//params: { search:'ajax' } ,
			//type: 'POST',
			//lookup: eventArray,
            onSelect: function(suggestion) {
                //j('#selction-ajax').html('You selected: ' + suggestion.value + ', ' + suggestion.data);
            }
        });

        
    });

});