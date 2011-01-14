/**
 * @version 1.0 $Id: recurrence.js 30 2009-05-08 10:22:21Z roland $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

window.addEvent('domready', function(){

  $$('.dynfilter').addEvent('change', function() {
    this.form.submit();
    return true;
  });
 
  if ($('filter_continent')) {
	  $('filter_continent').addEvent('change', function() {
		resetCountry();
	    this.form.submit();
	    return true;
	  });
  }

  if ($('filter_country')) {  
	  $('filter_country').addEvent('change', function() {
    	resetCity();
	    this.form.submit();
	    return true;
	  });
  }
  
  if ($('filter_city')) {  
	  $('filter_city').addEvent('change', function() {
		resetVenue();
	    this.form.submit();
	    return true;
	  });
  }
  
  if ($('filter_category')) {  
	  $('filter_category').addEvent('change', function() {
	    this.form.submit();
	    return true;
	  });
  }


  function resetCountry()
  {
      if ($('filter_country')) {
    	  $('filter_country').selectedIndex = 0;
      }
      resetCity();
      return true;
  }

  function resetCity()
  {
      if ($('filter_city')) {
    	  $('filter_city').selectedIndex = 0;
      }
      resetVenue();
      return true;
  }

  function resetVenue()
  {
  	if ($('filter_venue')) {
  		$('filter_venue').selectedIndex = 0;
  	}	
    return true;
  }
});


function OnUpdateDate(cal)
{
	cal.hide();
	$('adminForm').submit();
}