<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die();

$toggle = $this->params->get('filter_toggle', 3);
$showPagination = $this->params->def('show_pagination', 1);
$showPaginationResults = $this->params->def('show_pagination_results', 1);
?>

<div id="redevent" class="rf_thumb">
<p class="buttons">
		<?php
			if (!$this->params->get('popup')) : //don't show in printpopup
				echo RedeventHelperOutput::listbutton( $this->list_link, $this->params );
				echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
			endif;

			echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
		?>
	</p>

	<?php if ($this->params->def('show_page_title', 1)) : ?>
		<h1 class="componentheading">
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>
	<?php endif; ?>


	<?php if ($this->params->get('showintrotext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('introtext'); ?>
		</div>
	<?php endif; ?>

	<!-- filter -->
	<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">
		<?php if ($toggle != 1 || $this->params->get('display_limit_select')) : ?>
			<div id="el_filter" class="floattext">
				<?php if ($toggle != 1 || 1): ?>
					<?php if ($toggle > 1): ?>
						<div id="filters-toggle"><?php echo JTExt::_('COM_REDEVENT_TOGGLE_FILTERS'); ?></div>
					<?php endif; ?>
					<div class="el_fleft" id="el-events-filters" style="display:none;">
						<?php if ($this->params->get('filter_text', 1) && $this->lists['filter_type']): ?>
							<div id="main-filter">
								<label for="filter_type"><?php echo JText::_('COM_REDEVENT_FILTER') ?></label>&nbsp
								<?php echo $this->lists['filter_type'] ?>&nbsp;
								<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
								<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
							</div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_date', 0)): ?>
							<div id="date-filter"><?php echo $this->lists['dateFilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_event', 0)): ?>
							<div id="event-filter"><?php echo $this->lists['eventfilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_category', 1)): ?>
							<div id="category-filter"><?php echo $this->lists['categoryfilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->params->get('lists_filter_venue', 1)): ?>
							<div id="venue-filter"><?php echo $this->lists['venuefilter']; ?></div>
						<?php endif; ?>

						<?php if ($this->customsfilters && count($this->customsfilters)): ?>
							<?php foreach ($this->customsfilters as $custom): ?>
								<div class="custom-filter" id="filter<?php echo $custom->id; ?>">
									<?php echo '<label for="filtercustom'.$custom->id.'">'.JText::_($custom->name).'</label>&nbsp;'; ?>
									<?php echo $custom->renderFilter(array('class' => "inputbox dynfilter"), isset($this->filter_customs[$custom->id]) ? $this->filter_customs[$custom->id] : null); ?>
									<ul id="divselect<?php echo $custom->id ?>" class="inputbox dynfilter"></ul>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();" class="btn-reset-filter-redvent"><?php echo '<span class="pink">X </span>'.JText::_('COM_REDEVENT_RESET' ); ?></button>
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
		<input type="hidden" id="timeline-filter-order" name="filter_order" value="<?php echo $this->order; ?>" />
		<input type="hidden" id="timeline-filter-direction" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
		<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	</form>
	<?php if (!empty($this->rows)): ?>
		<div class="container sessions-list">
			<?php foreach ($this->rows as $key => $session): ?>
				<?php $link = JRoute::_(RedeventHelperRoute::getDetailsRoute($session->id, ($session->xref ? $session->xref : null) ), false); ?>
				<?php $category = array_pop($session->categories); ?>
				<?php
					$additionClass = '';

					if (!empty($session->custom10)):
						$sessionTypes = explode("\n", $session->custom10);

						foreach ($sessionTypes as $sessionType):
							$additionClass .= 'type-' . strtolower(JFilterOutput::stringURLSafe($sessionType)) . ' ';
						endforeach;
					endif;
				?>
				<div class="well well-small col-md-12 <?php echo ($key%2==0)?'first':''?>">
	                <div class="row">
						<div class="col-sm-9">
							<div class="session-info <?php echo $additionClass; ?>">
								<h3><?php echo $session->times?> - <?php echo $session->endtimes; ?></h3>
								<span><?php echo $category->name; ?></span>
							</div>
							<div class="session-title">
								<a href="<?php echo $link ?>"><h5><?php echo $session->full_title; ?></h5></a>
							</div>
							<div class="session-desc">
								<p><?php echo JHTML::_('string.truncate', strip_tags($session->datdescription), 150, true, false)?></p>
							</div>
							 <a class="readmore" href="<?php echo $link ?>"><?php echo JText::_('COM_REDEVENT_READ_MORE')?></a>
						</div>
						<div class="col-sm-3">
							<?php foreach ($session->prices as $price): ?>
								<img src="<?php echo $price->image; ?>" />
							<?php endforeach; ?>
						</div>
	                </div>
                </div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>