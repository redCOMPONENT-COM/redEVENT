window.addEvent('domready', function() {

   // Selectors of the containers for switches and content
   var togglerName='dt.accordion_toggler_';
   var contentName='dd.accordion_content_';


   // Position selectors
   var counter=1;   
   var toggler=$$(togglerName+counter);
   var content=$$(contentName+counter);

   while(toggler.length>0)
   {
      // Apply accordion
      new Accordion(toggler, content, {
         opacity: false,
         display: -1,
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