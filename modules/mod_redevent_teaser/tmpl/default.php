<?php
/**
 * THIS FILE IS BASED mod_eventlist_teaser from ezuri.de, BASED ON MOD_EVENTLIST_WIDE FROM SCHLU.NET
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2008 - 2011 redComponent
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
 
defined('_JEXEC') or die('Restricted access');
// check if any results returned
$items = count($list);

if (!$items) {
   echo '<p class="redeventmod' . $params->get('moduleclass_sfx') . '">' . JText::_('NOEVENTS') . '</p>';
   return;
}
?>
<div id="redeventteaser">
  <?php if ($params->get('layout', 1) == 1) : ?>  
	<!-- LAYOUT 1 -->  

  <?php foreach ($list as $item) :  ?> 
		<div class="teaser-event">
	  <h2 class="event-title">
	    <?php if ($item->eventlink) : ?>
	    <a href="<?php echo $item->eventlink; ?>" title="
	      <?php echo $item->title; ?> ">
	      <?php endif; ?>
	      <?php echo $item->title; ?>
	      <?php if ($item->eventlink) : ?></a>
	    <?php endif; ?></h2> 
	  <div class="calendar">
	    <div class="year">
	              <?php echo $item->year; ?>
	    </div>
	    <div class="month">
	      <?php echo $item->month; ?>
	    </div>
	    <div class="day">
	      <?php echo $item->dayname; ?>
	    </div>
	    <div class="daynum">
	      <?php echo $item->daynum; ?>
	    </div> 
	  </div> 
	  
	  <div class="teaser">
	    <p>
	      <?php if ($item->eventimage): ?>
	      	<?php echo $item->eventimage; ?>
	      <?php elseif ($item->venueimage): ?>
	      	<?php echo $item->venueimage; ?>
	      <?php endif; ?>
	      <?php echo $item->eventdescription; ?>
	    </p>
	  </div>
	  <div class="clear">
	  </div> 
	  <!-- additional information list -->
	  <?php if ($params->get('showtime', 1) || $params->get('showvenue', 1)  || $params->get('showcategory', 1)): ?>
	  <ul>
	    <!-- Time -->
	    <?php if ($params->get('showtime', 1) && !empty ($item->time)) : ?>
	    <li>
	    <?php echo JText::_('MOD_REDEVENT_TEASER_AINFO_TIME').' '.$item->time; ?></li>
	    <?php endif; ?> 
	    <!-- Venue -->
	    <?php if ($params->get('showvenue', 1) && $item->venue) : ?>
	    <li>
	    <?php echo JText::_('MOD_REDEVENT_TEASER_AINFO_VENUE').' ';?>
	    <a href="<?php echo $item->venuelink; ?>" title="<?php echo $item->venue; ?>"><?php echo $item->venue; ?></a>
	    - 
	    <?php echo $item->city; ?></li>
	    <?php endif; ?> 
	    <!-- Category -->
	    <?php if ($params->get('showcategory', 1)) : ?>
	    <li>
	    <?php echo JText::_('MOD_REDEVENT_TEASER_AINFO_CATEGORY').' ';?>
	    <?php echo implode(", ", $item->categorylink); ?>
	    </li>
	    <?php endif; ?> 
	  </ul>
	  <?php endif; ?>
	  
	   <!-- social bookmarks -->
	    <span class="share">
			<?php 
			$url = JURI::base();
			$pars = parse_url($url);
			$base = $pars['host'];
			?>
      
      <!--Facebook TODO: rel="nofollow" for fb modal -->
      <?php if ($params->get('linkfb') == 1) { ?>
      <a href="http://www.facebook.com/sharer.php?u=<?php echo $base.$item->eventlink; ?>&t=<?php echo $item->title; ?> " title="Share on Facebook" class="modal" rel="{handler: 'iframe', size: {x: 750, y: 450}}">
        <img class="" src="<?php echo JURI::base();?>modules/mod_redevent_teaser/tmpl/img/facebook.png" height="12" width="12" alt="Facebook" /></a>
      <?php 
      }
      ?> 
      
      <!-- Twitter -->
      <?php if ($params->get('linktw') == 1) { ?>
      <a rel="nofollow" target="_blank" href="http://www.twitter.com/home?status=<?php echo urlencode($item->title); ?>+<?php echo $base.$item->eventlink; ?> ">
        <img src="<?php echo JURI::base();?>modules/mod_redevent_teaser/tmpl/img/twitter.png" height="14" width="11" alt="Twitter" /></a>
      <?php 
      }
      ?>     
      
      <!-- Digg -->
      <?php if ($params->get('linkdi') == 1) { ?>      
      <a class="DiggThisButton" href="http://digg.com/submit?url=<?php echo $base.$item->eventlink; ?>&amp;title=<?php echo urlencode($item->title); ?> " rel="external" rev="('news'|'image'|'video'), [topic]">
        <img src="<?php echo JURI::base();?>modules/mod_redevent_teaser/tmpl/img/digg.png" height="11" width="24" alt="DiggThis" /></a>
      <?php 
      }
      ?>  
      
    </span>    
    </div>
    <hr />		
  <?php endforeach; ?>  
  <?php endif ; ?>

<?php if ($params->get('layout', 1) == 2) : ?>
    
<!-- LAYOUT 2 --> 

  <?php foreach ($list as $item) :  ?>   
  <div class="teaser-event">
  <h2 class="sidetitle">
    <?php if ($item->eventlink) : ?>
    <a href="<?php echo $item->eventlink; ?>" title="
      <?php echo $item->title; ?> ">
      <?php endif; ?>
      <?php echo $item->title; ?>
      <?php if ($item->eventlink) : ?></a>
    <?php endif; ?></h2> 
  <div class="calendar">
    <div class="year">
          <?php echo $item->year; ?>
    </div>
    <div class="month">
      <?php echo $item->month; ?>
    </div>
    <div class="day">
      <?php echo $item->dayname; ?>
    </div>
    <div class="daynum">
      <?php echo $item->daynum; ?>
    </div> 
  </div> 
  
 <div class="teaser">
    </div>  <div class="clear">
  </div> 
  <!-- additional information list -->
  
  
   <!-- social bookmarks -->
    <span class="share">
<?php 
$url = JURI::base();
$pars = parse_url($url);
$base = $pars['host'];
?>
      
      <!--Facebook TODO: rel="nofollow" for fb modal -->
      <?php if ($params->get('linkfb') == 1) { ?>
      <a href="http://www.facebook.com/sharer.php?u=<?php echo $base.$item->eventlink; ?>&t=<?php echo $item->title; ?> " title="Share on Facebook" class="modal" rel="{handler: 'iframe', size: {x: 750, y: 450}}">
        <img class="" src="<?php echo JURI::base();?>modules/mod_redevent_teaser/tmpl/img/facebook.png" height="12" width="12" alt="Facebook" /></a>
      <?php 
      }
      ?> 
      
      <!-- Twitter -->
      <?php if ($params->get('linktw') == 1) { ?>
      <a rel="nofollow" target="_blank" href="http://www.twitter.com/home?status=<?php echo urlencode($item->title); ?>+<?php echo $base.$item->eventlink; ?> ">
        <img src="<?php echo JURI::base();?>modules/mod_redevent_teaser/tmpl/img/twitter.png" height="14" width="11" alt="Twitter" /></a>
      <?php 
      }
      ?>     
      
      <!-- Digg -->
      <?php if ($params->get('linkdi') == 1) { ?>      
      <a class="DiggThisButton" href="http://digg.com/submit?url=<?php echo $base.$item->eventlink; ?>&amp;title=<?php echo urlencode($item->title); ?> " rel="external" rev="('news'|'image'|'video'), [topic]">
        <img src="<?php echo JURI::base();?>modules/mod_redevent_teaser/tmpl/img/digg.png" height="11" width="24" alt="DiggThis" /></a>
      <?php 
      }
      ?>  
      
    </span>
    </div>    
    <hr />
  <?php endforeach; ?>  
  <?php endif ; ?>

</div>
