/**
 * nested accordeon code, from http://blog.medianotions.de/en/articles/2008/mootools-nested-accordion
 */

window.addEvent('domready', function() {

	// Adaption IE6
	if(window.ie6) var heightValue='100%';
	else var heightValue='';

   // Selectors of the containers for switches and content
   var togglerName='dt.accordion_toggler_';
   var contentName='dd.accordion_content_';


   // Position selectors
   var counter=1;   
   var toggler=$$(togglerName+counter);
   var content=$$(contentName+counter);

   while(toggler.length>0)
   {
	   // find index of open ones ?
	   var index = -1;
	   for (var i = 0, n = toggler.length; i < n; i++) {
		   if ($(toggler[i]).hasClass('open')) {
			   index = i;
			   break;
		   }
	   }
      
      // Apply accordion
      new Accordion(toggler, content, {
         opacity: false,
         display: index,
         alwaysHide: true,
         onComplete: function() { 
            var element=$(this.elements[this.previous]);
            if(element && element.offsetHeight>0) element.setStyle('height', heightValue);         
         },
         onActive: function(toggler, content) {
            toggler.addClass('open');
         },
         onBackground: function(toggler, content) {
            toggler.removeClass('open');
         }
      });

      // Set selectors for next level
      counter++;
      toggler=$$(togglerName+counter);
      content=$$(contentName+counter);
   }
});