<?php
/**
 * @version 1.0 $Id$
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
JFactory::getDocument()->addStyleSheet('components/com_redevent/assets/css/fancy.css');
?>

	<?php	if (!count($this->rows)) : ?>
		<div><?php echo JText::_('COM_REDEVENT_NO_EVENTS' ); ?></div>
	<?php	else :

	$k = 0;
	foreach ($this->rows as $row) :
		$isover = (redEVENTHelper::isOver($row) ? ' isover' : '');
	
		$img = redEVENTImage::getThumbUrl($row->datimage,300);
		$img = ($img ? JHTML::image($img, $row->full_title) : false);
	
		//Link to details
		$detaillink = JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xslug) );		
	?>
<div class="frontpage_event_wrapper" itemscope itemtype="http://schema.org/Event">
	
	<div class="frontpage_event_left_wrapper	
	<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?>
	<?php echo $isover; ?>">		

		<div id="f1_container" class="hover">
			<div id="f1_card" class="shadow">
				<div class="front face">
					<?php if ($img): ?>
					<a href="<?php echo $detaillink ; ?>" itemprop="url">				
						<?php echo $img; ?>					
					</a>
					<?php endif; ?>
				</div>
				<div class="back face">
					<div class="flip_venue_container">
						
						<h1><?php echo JText::_('COM_REDEVENT_VENUE_INFO' ); ?></h1>
						
						<div class="flip_venue_headline">
							<h2><?php echo $this->escape($row->venue); ?></h2>
						</div>
						
						<div class="flip_venue_info">
							<p><?php echo $this->escape($row->street); ?> - <?php echo $this->escape($row->city); ?></p>
							<a target="_blank" href="<?php echo $this->escape($row->url); ?>"><p><?php echo $this->escape($row->url); ?></p></a>
						</div>
						
						<div class="flip_venue_desc">
							<h3><?php echo JText::_('COM_REDEVENT_VENUE_DESCRIPTION' ); ?></h3>
							<p><?php echo $row->locdescription; ?></p>
							<p class="attending"><?php echo JText::sprintf('COM_REDEVENT_ATTENDING_NB_D', $row->registered); ?></p>
						</div>
					</div>
				</div>
	  		</div>
		</div>
		
	</div>

	<div class="frontpage_event_mid_wrapper	
			<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?>
			<?php echo $isover; ?>">
		<div class="frontpage_event_title" itemprop="name">
			<a href="<?php echo $detaillink ; ?>" itemprop="url">				
				<?php echo $this->escape($row->full_title); ?>					
			</a>
		</div>
			
		<div class="frontpage_event_summary">
				<?php echo ($row->summary); ?>
		</div>			
	</div>
	
  <div class="frontpage_event_right_wrapper	
	<?php echo ($k + 1) . $this->params->get( 'pageclass_sfx' ). ($row->featured ? ' featured' : ''); ?>
	<?php echo $isover; ?>">
				
		<div class="frontpage_event_date">				
			<?php echo strftime( '%d/%m', strtotime( $row->dates ));?>
		</div>
			
		<div class="frontpage_event_payment">				
			<a href="<?php echo $detaillink ; ?>" itemprop="url">					
				<div class="frontpage_event_buybtn">
					<p class="btn btn-large btn-primary"><?php echo JText::_('COM_REDEVENT_BUY_TICKETS' ); ?></p>
				</div>
			</a>				
		</div>
		
		<div class="frontpage_event_share">
			<!-- AddThis Button BEGIN -->
			<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=ra-4d6674db3f606730"><img src="http://s7.addthis.com/static/btn/sm-share-en.gif" width="83" height="16" alt="Bookmark and Share" style="border:0"/></a>
			<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4d6674db3f606730"></script>
			<!-- AddThis Button END -->
		</div>			
			
	</div>

</div>
 	<?php	$k = 1 - $k; ?>
	<?php endforeach; ?>
	<?php endif; ?>
