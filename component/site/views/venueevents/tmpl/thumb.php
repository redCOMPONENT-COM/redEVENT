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
.rf_img {min-height:<?php echo $this->config->get('imageheight', 100);?>px;}
</style>

<div id="redevent" class="el_venueevents<?= $this->params->get('pageclass_sfx') ?>">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			if ($this->editlink) echo RedeventHelperOutput::editVenueButton($this->venue->slug);
			echo RedeventHelperOutput::listbutton( $this->list_link, $this->params );
		endif;
		echo RedeventHelperOutput::mailbutton( $this->venue->slug, 'venueevents', $this->params );
		echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	?>
</p>
<?php if ($this->params->def('show_page_heading', 1)) : ?>
	<h1 class='componentheading'>
		<?php echo $this->escape($this->pagetitle); ?>
	</h1>
<?php endif; ?>

	<!--Venue-->
	<?php //flyer
	echo RedeventImage::modalimage($this->venue->locimage, $this->venue->venue);
	echo RedeventHelperOutput::mapicon( $this->venue , array('class' => 'map'));
	?>

	<dl class="location floattext">
		<?php if (!empty($this->venue->url)) : ?>
		<dt class="venue"><?php echo JText::_('COM_REDEVENT_WEBSITE' ).':'; ?></dt>
			<dd class="venue">
					<a href="<?php echo $this->venue->url; ?>" target="_blank"> <?php echo $this->venue->urlclean; ?></a>
			</dd>
		<?php endif; ?>

		<?php if ( $this->venue->street ) : ?>
  			<dt class="venue_street"><?php echo JText::_('COM_REDEVENT_STREET' ).':'; ?></dt>
			<dd class="venue_street">
    			<?php echo $this->escape($this->venue->street); ?>
			</dd>
			<?php endif; ?>

			<?php if ( $this->venue->plz ) : ?>
  			<dt class="venue_plz"><?php echo JText::_('COM_REDEVENT_ZIP' ).':'; ?></dt>
			<dd class="venue_plz">
    			<?php echo $this->escape($this->venue->plz); ?>
			</dd>
			<?php endif; ?>

			<?php if ( $this->venue->city ) : ?>
    		<dt class="venue_city"><?php echo JText::_('COM_REDEVENT_CITY' ).':'; ?></dt>
    		<dd class="venue_city">
    			<?php echo $this->escape($this->venue->city); ?>
    		</dd>
    		<?php endif; ?>

    		<?php if ( $this->venue->state ) : ?>
			<dt class="venue_state"><?php echo JText::_('COM_REDEVENT_STATE' ).':'; ?></dt>
			<dd class="venue_state">
    			<?php echo $this->escape($this->venue->state); ?>
			</dd>
			<?php endif; ?>

			<?php if ( $this->venue->country ) : ?>
			<dt class="venue_country"><?php echo JText::_('COM_REDEVENT_COUNTRY' ).':'; ?></dt>
    		<dd class="venue_country">
    			<?php echo $this->venue->countryimg ? $this->venue->countryimg : $this->venue->country; ?>
    		</dd>
    		<?php endif; ?>

	</dl>

	<?php
  	if (!empty($this->venuedescription)) :
	?>

		<h2 class="description"><?php echo JText::_('COM_REDEVENT_DESCRIPTION' ); ?></h2>
	  	<div class="description no_space floattext">
	  		<?php echo $this->venuedescription;	?>
		</div>

	<?php endif; ?>

	<!-- use form for filters and pagination -->
	<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">


		<!-- filters  -->
		<?php echo RedeventLayoutHelper::render(
			'sessionlist.filters',
			$this
		); ?>
		<!-- end filters -->

	<!--table-->
	<?php echo $this->loadTemplate('items'); ?>

	<p>
		<input type="hidden" name="option" value="com_redevent" />
		<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
		<input type="hidden" name="view" value="venueevents" />
		<input type="hidden" name="id" value="<?php echo $this->venue->id; ?>" />
		<input type="hidden" name="Itemid" value="<?php echo $this->item->id;?>" />
		<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	</p>
	</form>

<!--pagination-->
<?php if (!$this->params->get( 'popup' ) ) : ?><!--pagination-->
<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pageNav->get('pages.total') > 1)) : ?>
<div class="pagination">
	<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
				<?php echo $this->pageNav->getPagesCounter(); ?>
		</p>

		<?php endif; ?>
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>
<?php  endif; ?>
<!-- pagination end -->
<?php endif; ?>

</div>
