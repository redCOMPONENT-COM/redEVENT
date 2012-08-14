
window.addEvent('domready', function(){
	if ($$('div.redeventcal div.cal_toggle')) {
		$$('div.redeventcal div.cal_toggle').addEvent('click', function(){
			this.getParent('div.redeventcal').addClass('hide_mod');
			if (dataStore) {
				dataStore.setItem('hide_mod_recal', 1);
			}
		});
	}
	if ($$('div.redeventcal div.toggleoff')) {
		$$('div.redeventcal div.toggleoff').addEvent('click', function(){
			this.getParent('div.redeventcal').removeClass('hide_mod');
			if (dataStore) {
				dataStore.setItem('hide_mod_recal', -1);
			}				
		});
	}
	
	if (typeof(Storage)!=="undefined")
	{
		// localStorage and sessionStorage supported
		var dataStore = window.sessionStorage;
		
		if (dataStore.getItem('hide_mod_recal') == 1) {
			$$('div.redeventcal').addClass('hide_mod');
		}
		if (dataStore.getItem('hide_mod_recal') == -1) {
			$$('div.redeventcal').removeClass('hide_mod');
		}
	}
});