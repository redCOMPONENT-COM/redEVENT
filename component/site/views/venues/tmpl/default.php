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
?>
<div id="redevent" class="el_venuesview">
	<p class="buttons">
		<?php
			if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
				echo ELOutput::submitbutton( $this->dellink, $this->params );
				echo ELOutput::archivebutton( $this->params, $this->task );
			endif;

			echo ELOutput::printbutton( $this->print_link, $this->params );
		?>
	</p>

	<?php if ($this->params->def('show_page_title', 1)) : ?>
		<h1 class='componentheading'>
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>
	<?php endif; ?>

	<!--Venue-->
	<?php foreach($this->rows as $row) : ?>
		
		<h2 class="eventlist">
			<a href="<?php echo $row->targetlink; ?>"><?php echo $this->escape($row->venue); ?></a>
		</h2>

			<?php
				echo redEVENTImage::modalimage('venues', $row->locimage, $row->venue);
				echo ELOutput::mapicon( $row , array('class' => 'map'));
			?>

			<dl class="location floattext">
				<?php if (!empty($row->url)) : ?>
				<dt class="venue_website"><?php echo JText::_('COM_REDEVENT_WEBSITE' ).':'; ?></dt>
	   			<dd class="venue_website">
					<a href="<?php echo $row->url; ?>" target="_blank"> <?php echo $row->urlclean; ?></a>
				</dd>
				<?php endif; ?>

	  			<?php if ( $row->street ) : ?>
	  			<dt class="venue_street"><?php echo JText::_('COM_REDEVENT_STREET' ).':'; ?></dt>
				<dd class="venue_street">
	    			<?php echo $this->escape($row->street); ?>
				</dd>
				<?php endif; ?>

				<?php if ( $row->plz ) : ?>
	  			<dt class="venue_plz"><?php echo JText::_('COM_REDEVENT_ZIP' ).':'; ?></dt>
				<dd class="venue_plz">
	    			<?php echo $this->escape($row->plz); ?>
				</dd>
				<?php endif; ?>

				<?php if ( $row->city ) : ?>
	    		<dt class="venue_city"><?php echo JText::_('COM_REDEVENT_CITY' ).':'; ?></dt>
	    		<dd class="venue_city">
	    			<?php echo $this->escape($row->city); ?>
	    		</dd>
	    		<?php endif; ?>

	    		<?php if ( $row->state ) : ?>
				<dt class="venue_state"><?php echo JText::_('COM_REDEVENT_STATE' ).':'; ?></dt>
				<dd class="venue_state">
	    			<?php echo $this->escape($row->state); ?>
				</dd>
				<?php endif; ?>

				<?php if ( $row->country ) : ?>
				<dt class="venue_country"><?php echo JText::_('COM_REDEVENT_COUNTRY' ).':'; ?></dt>
	    		<dd class="venue_country">
	    			<?php echo $row->countryimg ? $row->countryimg : $row->country; ?>
	    		</dd>
	    		<?php endif; ?>

	    		<dt class="venue_assignedevents"><?php echo JText::_('COM_REDEVENT_EVENTS' ).':'; ?></dt>
	    		<dd class="venue_assignedevents">
	    			<a href="<?php echo $row->targetlink; ?>"><?php echo (int)$row->assignedevents; ?></a>
	    		</dd>

		</dl>

	    <?php if ($row->locdescription) :	?>
		<h2 class="description"><?php echo JText::_('COM_REDEVENT_DESCRIPTION' ).':'; ?></h2>
		<div class="description">
	    	<?php echo $row->locdescription; ?>
		</div>
		<?php endif; ?>
	<?php endforeach; ?>

	<!--pagination-->
	<p class="pageslinks">
		<?php echo $this->pageNav->getPagesLinks(); ?>
	</p>

	<p class="pagescounter">
		<?php echo $this->pageNav->getPagesCounter(); ?>
	</p>
</div>