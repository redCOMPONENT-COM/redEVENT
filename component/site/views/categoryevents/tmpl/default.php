<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );
?>
<div id="redevent" class="el_categoryevents">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			echo RedeventHelperOutput::thumbbutton( $this->thumb_link, $this->params );
			echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
		endif;
		echo RedeventHelperOutput::mailbutton( $this->category->slug, 'categoryevents', $this->params );
		echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

<?php if ($this->params->def( 'show_page_title', 1 )) : ?>

    <h1 class='componentheading'>
		<?php echo $this->task == 'archive' ? $this->escape($this->category->name.' - '.JText::_('COM_REDEVENT_ARCHIVE')) : $this->escape($this->category->name); ?>
	</h1>

<?php endif; ?>

<div class="floattext">
<?php if (!empty($this->category->image) || $this->params->get('use_default_picture', 1)): ?>
<div class="catimg">
	<?php if ($this->category->image): ?>
	<?php echo RedeventImage::modalimage($this->category->image, $this->category->name); ?>
	<?php else: ?>
	<?php echo JHTML::image('components/com_redevent/assets/images/noimage.png', $this->category->name); ?>
	<?php endif; ?>
</div>
<?php endif; ?>

<div class="catdescription">
	<?php echo $this->catdescription; ?>
</div>
</div>

<?php echo $this->loadTemplate('attachments'); ?>

<!-- use form for filters and pagination -->
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">

<!-- filters  -->
<?php $toggle = $this->params->get('filter_toggle', 3); ?>
<?php if ($toggle != 1 || $this->params->get('display_limit_select')) : ?>
<div id="el_filter" class="floattext">
		<?php if ($toggle != 1 || 1) : ?>
			<?php if ($toggle > 1) : ?>
			<div id="filters-toggle"><?php echo JTExt::_('COM_REDEVENT_TOGGLE_FILTERS'); ?></div>
			<?php endif; ?>
			<div class="el_fleft" id="el-events-filters">
			<?php if ($this->params->get('filter_text', 1) && $this->lists['filter_type']): ?>
			<div id="main-filter">
				<?php
				echo '<label for="filter_type">'.JText::_('COM_REDEVENT_FILTER').'</label>&nbsp;';
				echo $this->lists['filter_type'].'&nbsp;';
				?>
				<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
				<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
				<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('lists_filter_event', 0)): ?>
			<div id="event-filter"><?php echo $this->lists['eventfilter']; ?></div>
    	<?php endif; ?>

			<?php if ($this->params->get('lists_filter_venue', 1)): ?>
			<div id="venue-filter"><?php echo $this->lists['venuefilter']; ?></div>
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

		<?php if ($this->params->get('display_limit_select')) : ?>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>
<!-- end filters -->

<!--table-->
<?php
if ($this->state->get('results_type') == 0)
{
	$allowed = array(
			'title',
			'venue',
			'category',
			'picture',
	);
	$this->columns = RedeventHelper::validateColumns($this->columns, $allowed);
	echo $this->loadTemplate('eventstable');
}
else
{
	echo $this->loadTemplate('table');
}
?>

<p>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
<input type="hidden" name="view" value="categoryevents" />
<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo (isset($this->item->id) ? $this->item->id:""); ?>" />
<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
</p>
</form>

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
	<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($this->category->id, null, 'rsscal').'&format=feed'),
                          JHTML::image('components/com_redevent/assets/images/rsscal2.0.png', JText::_('COM_REDEVENT_EXPORT_RSSCAL'))
	                        ); ?>
</span>
<?php endif; ?>

<?php if ($this->params->get('events_ical', 1)): ?>
<span class="events-ical">
	<?php echo JHTML::link( JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($this->category->id, null).'&format=raw&layout=ics'),
                          JHTML::image('components/com_redevent/assets/images/iCal2.0.png', JText::_('COM_REDEVENT_EXPORT_ICS'))
	                        ); ?>
</span>
<?php endif; ?>
</div>
<!-- end: exports -->
<?php endif; ?>

</div>
