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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="redevent" class="el_venueevents">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			if ($this->editlink) echo ELOutput::editVenueButton($this->venue->slug);
			echo ELOutput::thumbbutton( $this->thumb_link, $this->params );
			echo ELOutput::archivebutton( $this->params, $this->task, $this->venue->slug );
		endif;
		echo ELOutput::mailbutton( $this->venue->slug, 'venueevents', $this->params );
		echo ELOutput::printbutton( $this->print_link, $this->params );
	?>
</p>
<?php if ($this->params->def('show_page_title', 1)) : ?>
	<h1 class='componentheading'>
		<?php echo $this->escape($this->pagetitle); ?>
	</h1>
<?php endif; ?>

	<!--Venue-->
	<?php //flyer
	echo redEVENTImage::modalimage('venues', $this->venue->locimage, $this->venue->venue);
	echo ELOutput::mapicon( $this->venue );
	?>

	<dl class="location floattext">
		<?php if (($this->elsettings->showdetlinkvenue == 1) && (!empty($this->venue->url))) : ?>
		<dt class="venue"><?php echo JText::_( 'WEBSITE' ).':'; ?></dt>
			<dd class="venue">
					<a href="<?php echo $this->venue->url; ?>" target="_blank"> <?php echo $this->venue->urlclean; ?></a>
			</dd>
		<?php endif; ?>

		<?php if ( $this->elsettings->showdetailsadress == 1 ) : ?>

  			<?php if ( $this->venue->street ) : ?>
  			<dt class="venue_street"><?php echo JText::_( 'STREET' ).':'; ?></dt>
			<dd class="venue_street">
    			<?php echo $this->escape($this->venue->street); ?>
			</dd>
			<?php endif; ?>

			<?php if ( $this->venue->plz ) : ?>
  			<dt class="venue_plz"><?php echo JText::_( 'ZIP' ).':'; ?></dt>
			<dd class="venue_plz">
    			<?php echo $this->escape($this->venue->plz); ?>
			</dd>
			<?php endif; ?>

			<?php if ( $this->venue->city ) : ?>
    		<dt class="venue_city"><?php echo JText::_( 'CITY' ).':'; ?></dt>
    		<dd class="venue_city">
    			<?php echo $this->escape($this->venue->city); ?>
    		</dd>
    		<?php endif; ?>

    		<?php if ( $this->venue->state ) : ?>
			<dt class="venue_state"><?php echo JText::_( 'STATE' ).':'; ?></dt>
			<dd class="venue_state">
    			<?php echo $this->escape($this->venue->state); ?>
			</dd>
			<?php endif; ?>

			<?php if ( $this->venue->country ) : ?>
			<dt class="venue_country"><?php echo JText::_( 'COUNTRY' ).':'; ?></dt>
    		<dd class="venue_country">
    			<?php echo $this->venue->countryimg ? $this->venue->countryimg : $this->venue->country; ?>
    		</dd>
    		<?php endif; ?>
		<?php
		endif; //showdetails ende
		?>
	</dl>

	<?php
  	if ($this->elsettings->showlocdescription == 1) :
	?>

		<h2 class="description"><?php echo JText::_( 'DESCRIPTION' ); ?></h2>
	  	<div class="description no_space floattext">
	  		<?php echo $this->venuedescription;	?>
		</div>

	<?php endif; ?>

	<!--table-->

	<?php echo $this->loadTemplate('table'); ?>

	<p>
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="view" value="venueevents" />
	<input type="hidden" name="id" value="<?php echo $this->venue->id; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->item->id;?>" />
	</p>
	</form>

<!--pagination-->
<?php if (!$this->params->get( 'popup' ) ) : ?>
<div class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>
<?php endif; ?>

<?php if ($this->params->get('events_rsscal', 0) || $this->params->get('events_ical', 1)): ?>
<!-- start: exports -->
<div class="events-exports">
<?php if ($this->params->get('events_rsscal', 0)): ?>
<span class="events-rsscal">
	<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getVenueEventsRoute($this->venue->id, null, 'rsscal').'&format=feed'),
                          JHTML::image('components/com_redevent/assets/images/rsscal2.0.png', JText::_('COM_REDEVENT_EXPORT_RSSCAL'))
	                        ); ?>
</span>
<?php endif; ?>

<?php if ($this->params->get('events_ical', 1)): ?>
<span class="events-ical">
	<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getVenueEventsRoute($this->venue->id, null).'&format=ics'),
                          JHTML::image('components/com_redevent/assets/images/iCal2.0.png', JText::_('COM_REDEVENT_EXPORT_ICS'))
	                        ); ?>
</span>
<?php endif; ?>
</div>
<!-- end: exports -->
<?php endif; ?>

<!--copyright-->

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>
</div>
