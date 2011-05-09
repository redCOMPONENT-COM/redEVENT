/*
Add mootools tooltip event, with fading.
*/
window.addEvent('domready', function(){
   //do your tips stuff in here...
   var eventTip = new Tips($$('.eventTip'), {
      className: 'custom', //this is the prefix for the CSS class
      initialize:function(){
         this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 200, wait: false}).set(0);
      },
      maxTitleChars: 100, 
      onShow: function(toolTip) {
         this.fx.start(1);
      },
      onHide: function(toolTip) {
         this.fx.start(0);
      }
   });

   /* categories filtering */
   $$('.eventCat').addEvent( 'click', function(event) {
	   this.toggleClass('catoff');
	   toggleEvents();
   });
   
   $('buttonshowall').addEvent( 'click', function(event) {
	   $$('.eventCat').removeClass('catoff');
	   toggleEvents();
   }); 
   
   $('buttonhideall').addEvent( 'click', function(event) {
	   $$('.eventCat').addClass('catoff');
	   toggleEvents();
   }); 

});

function toggleEvents()
{
	var visible = new Array();
	var i = 0;
	$$('.eventCat').each(function(item, index) {
		if (!item.hasClass('catoff')) {
			visible[i++] = 'cat'+item.getProperty('catid');
		}
	});
	
	$$('div.eventcontent div').each(function(item, index){
		var show = false;
		for ( i in visible ) {
			if (item.hasClass(visible[i])) {
				show = true;
				break;
			}
		}
		if (show == true) {
			item.setStyle('display', 'block');
		}
		else {
			item.setStyle('display', 'none');
		}			
	});
}
