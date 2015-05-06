<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$view = $displayData;
$toggle = $view->params->get('filter_toggle', 3);

// Set $showFiltersFieldValue to remember if should show/hide when submitted
$showFiltersFieldValue = JFactory::getApplication()->input->getInt('showfilters');

if (!$showFiltersFieldValue)
{
	if ($toggle == 0)
	{
		$showFiltersFieldValue = '1';
	}
	elseif ($toggle == 3)
	{
		$showFiltersFieldValue = '0';
	}
}

RHelperAsset::load('eventslist.js');
?>
<?php if ($toggle != 1 || $showFiltersFieldValue || $view->params->get('display_limit_select')) : ?>
	<div id="el_filter" class="floattext">

		<?php if ($toggle != 1 || 1): ?>

			<?php if ($toggle > 1): ?>
				<div id="filters-toggle"><?php echo JTExt::_('COM_REDEVENT_TOGGLE_FILTERS'); ?></div>
			<?php endif; ?>

			<div class="el_fleft" id="el-events-filters">

				<?php if ($view->params->get('filter_text', 1)): ?>
					<div id="main-filter">
						<input type="text" name="filter" id="filter" value="<?php echo $view->lists['filter'];?>" class="inputbox" onchange="this.form.submit();" placeholder="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
						<button type="button" class="btn" onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_SEARCH'); ?></button>
						<button type="button" class="btn"  id="filters-reset"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
					</div>
				<?php endif; ?>

				<?php if ($view->params->get('lists_filter_event', 0) && isset($view->lists['eventfilter'])): ?>
					<div id="event-filter"><?php echo $view->lists['eventfilter']; ?></div>
				<?php endif; ?>

				<?php if ($view->params->get('lists_filter_category', 1) && isset($view->lists['categoryfilter'])): ?>
					<div id="category-filter"><?php echo $view->lists['categoryfilter']; ?></div>
				<?php endif; ?>

				<?php if ($view->params->get('lists_filter_venue', 1) && isset($view->lists['venuefilter'])): ?>
					<div id="venue-filter"><?php echo $view->lists['venuefilter']; ?></div>
				<?php endif; ?>

				<?php if (isset($view->customsfilters) && count($view->customsfilters)): ?>
					<?php foreach ($view->customsfilters as $custom): ?>
						<div class="custom-filter" id="filter<?php echo $custom->id; ?>">
							<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
							<?php echo $custom->renderFilter(
								array('class' => "inputbox dynfilter"),
								isset($view->filter_customs[$custom->id]) ? $view->filter_customs[$custom->id] : null
							); ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<input type="hidden" id="f-showfilters" name="showfilters" value="<?php echo $showFiltersFieldValue; ?>"/>
		<?php endif; ?>

		<?php if ($view->params->get('display_limit_select')) : ?>
			<div class="el_fright">
				<?php
				echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
				echo $view->pageNav->getLimitBox();
				?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
