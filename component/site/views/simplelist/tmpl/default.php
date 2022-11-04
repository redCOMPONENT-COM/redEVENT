<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );

$toggle = $this->params->get('filter_toggle', 3);
?>
<div id="redevent" class="el_eventlist<?= $this->params->get('pageclass_sfx') ?>">
	<p class="buttons">
		<?php
			if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
				echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
				echo RedeventHelperOutput::thumbbutton( $this->thumb_link, $this->params );
			endif;

			echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
		?>
	</p>

	<?php if ($this->params->def( 'show_page_heading', 1 )) : ?>

	    <h1 class="componentheading">
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>

	<?php endif; ?>


	<?php if ($this->params->get('showintrotext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('introtext'); ?>
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

	<?php echo $this->loadTemplate('table'); ?>

	<p>
	<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
	<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	</p>
	</form>

	<!--footer-->

	<!--pagination-->
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

	<?php if ($this->params->get('events_rsscal', 0) || $this->params->get('events_ical', 1)): ?>
	<!-- start: exports -->
	<div class="events-exports">
	<?php if ($this->params->get('events_rsscal', 0)): ?>
	<span class="events-rsscal">
		<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getSimpleListRoute(null, 'rsscal').'&format=feed'),
	                          JHTML::image('media/com_redevent/images/rsscal2.0.png', JText::_('COM_REDEVENT_EXPORT_RSSCAL'))
		                        ); ?>
	</span>
	<?php endif; ?>

	<?php if ($this->params->get('events_ical', 1)): ?>
	<span class="events-ical">
		<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getSimpleListRoute().'&format=raw&layout=ics'),
	                          JHTML::image('media/com_redevent/images/iCal2.0.png', JText::_('COM_REDEVENT_EXPORT_ICS'))
		                        ); ?>
	</span>
	<?php endif; ?>
	</div>
	<!-- end: exports -->
	<?php endif; ?>

</div>
