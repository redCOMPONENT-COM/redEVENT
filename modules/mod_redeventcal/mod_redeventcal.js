
window.addEvent('domready', function(){
	if ($$('div.redeventcal div.cal_toggle')) {
		$$('div.redeventcal div.cal_toggle').addEvent('click', function(){
			this.getParent('div.redeventcal').toggleClass('hide_mod');				
		});
	}
	if ($$('div.redeventcal div.toggleoff')) {
		$$('div.redeventcal div.toggleoff').addEvent('click', function(){
			this.getParent('div.redeventcal').toggleClass('hide_mod');				
		});
	}
});