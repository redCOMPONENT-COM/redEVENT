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
<style type="text/css">
.rf_img {min-height:<?php echo $this->config->imagehight;?>px;}
</style>

<div id="redevent" class="el_venueevents">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			if ($this->editlink) echo ELOutput::editVenueButton($this->venue->slug);
			echo ELOutput::listbutton( $this->list_link, $this->params );
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
	echo ELOutput::mapicon( $this->venue , array('class' => 'map'));
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
  	if ($this->elsettings->showlocdescription == 1 && !empty($this->venuedescription)) :
	?>

		<h2 class="description"><?php echo JText::_( 'DESCRIPTION' ); ?></h2>
	  	<div class="description no_space floattext">
	  		<?php echo $this->venuedescription;	?>
		</div>

	<?php endif; ?>

	<!-- use form for filters and pagination -->
	<form action="<?php echo $this->action; ?>" method="post" id="adminForm">
	
	<!-- filters  -->
	<?php $toggle = $this->params->get('filter_toggle', 3); ?>
	<?php if ($toggle != 1 || $this->params->get('display')) : ?>
	<div id="el_filter" class="floattext">
			<?php if ($toggle != 1 || 1) : ?>
				<?php if ($toggle > 1) : ?>
				<div id="filters-toggle"><?php echo JTExt::_('COM_REDEVENT_TOGGLE_FILTERS'); ?></div>
				<?php endif; ?>
				<div class="el_fleft" id="el-events-filters">
				<?php if ($this->params->get('filter_text', 1) && $this->lists['filter_type']): ?>
				<div id="main-filter">
					<?php
					echo '<label for="filter_type">'.JText::_('FILTER').'</label>&nbsp;';
					echo $this->lists['filter_type'].'&nbsp;';
					?>
					<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('EVENTS_FILTER_HINT'); ?>"/>
					<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'GO' ); ?></button>
					<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'RESET' ); ?></button>
				</div>
				<?php endif; ?>
				
			<?php if ($this->params->get('lists_filter_event', 0)): ?>
			<div id="event-filter"><?php echo $this->lists['eventfilter']; ?></div>
    	<?php endif; ?>
				
				<?php if ($this->params->get('lists_filter_category', 1)): ?>
				<div id="category-filter"><?php echo $this->lists['categoryfilter']; ?></div>
	    	<?php endif; ?>
				
				<?php if ($this->customsfilters && count($this->customsfilters)): ?>
	    	<?php foreach ($this->customsfilters as $custom): ?>
	      <div class="custom-filter" id="filter<?php echo $custom->id; ?>">
	      	<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
	      	<?php echo $custom->renderFilter(array('class' => "inputbox dynfilter"), isset($this->filter_customs[$custom->id]) ? $this->filter_customs[$custom->id] : null); ?>
	      </div>
	    	<?php endforeach; ?>
	    	<?php endif; ?>
			</div>
   	<input type="hidden" id="f-showfilters" name="showfilters" value="<?php echo $toggle == 0 ? '1' : JRequest::getInt('showfilters', $toggle != 3 ? 1 : 0); ?>"/>
			<?php endif; ?>
			
			<?php if ($this->params->get('display')) : ?>
			<div class="el_fright">
				<?php
				echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
				echo $this->pageNav->getLimitBox();
				?>
			</div>
			<?php endif; ?>
	</div>
	<?php endif; ?>
	<!-- end filters -->
	
	<!--table-->
	<?php echo $this->loadTemplate('items'); ?>

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
<!--copyright-->

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>
</div>
