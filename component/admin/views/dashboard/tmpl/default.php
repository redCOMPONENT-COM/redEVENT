<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

$params = RedeventHelper::config();

RHelperAsset::load('redevent-backend.css');

$items = RedeventHelperAdmin::getAdminMenuItems();

$i = 1;
?>
<div class="row-fluid">
	<div class="container" id="redevent-cpanel">
		<div class="span9 reDashboardMainIcons">
			<?php foreach ($items as $group): ?>
				<div class="accordion-group navbar-inverse" id="redevent-cpanel-<?php echo $i;?>">
					<div class="accordion-heading navbar-inner">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#redevent-cpanel-<?php echo $i;?>" href="#collapse-cpanel-<?php echo $i;?>">
							<h4>
								<i class="<?php echo $group['icon'];?>"></i>
								<?php echo $group['text'];?>
							</h4>
						</a>
					</div>
					<div class="accordion-body collapse in" id="collapse-cpanel-<?php echo $i;?>">
						<div class="row-fluid">
							<?php foreach ($group['items'] as $item): ?>
								<?php if ($this->user->authorise($item['access'], 'com_redevent')) :?>
									<div class="span2">
										<a href="<?= JRoute::_($item['link']); ?>" class="redevent-cpanel-icon-link">
											<div class="redevent-cpanel-icon-wrapper">
												<div class="redevent-cpanel-icon">
													<i class="<?= $item['icon'] ?> icon-5x"></i>
												</div>
												<?php if (!empty($item['count'])): ?>
													<span class="badge redevent-cpanel-count"><?= $item['count'] ?></span>
												<?php endif; ?>
											</div>
											<div class="redevent-cpanel-text">
												<?=  $item['text']; ?>
											</div>
										</a>
									</div>
								<?php endif;?>
							<?php endforeach;?>
						</div>
					</div>
				</div>
				<?php $i++;?>
			<?php endforeach; ?>
		</div>
		<div class="span3 reDashboardSideIcons">
			<div class="well">
				<div>
					<strong class="row-title">
						<?php echo JText::_('COM_REDEVENT_VERSION'); ?>
					</strong>
					<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_VERSION'); ?>">
						<?php echo $this->version; ?>
					</span>
				</div>
			</div>
			<div class="well">
				<div>
					<strong class="row-title">
						<?php echo JText::_('COM_REDEVENT_EVENT_STATS'); ?>
					</strong>
					<div>
						<?php echo JText::_('COM_REDEVENT_EVENTS_PUBLISHED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_EVENTS_PUBLISHED'); ?>">
							<?php echo $this->eventsStats['published'] ? $this->eventsStats['published'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_EVENTS_UNPUBLISHED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_EVENTS_UNPUBLISHED'); ?>">
							<?php echo $this->eventsStats['unpublished'] ? $this->eventsStats['unpublished'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_EVENTS_ARCHIVED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_EVENTS_ARCHIVED'); ?>">
							<?php echo $this->eventsStats['archived'] ? $this->eventsStats['archived'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_EVENTS_TOTAL'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_EVENTS_TOTAL'); ?>">
							<?php echo $this->eventsStats['total'] ? $this->eventsStats['total'] : 0; ?>
						</span>
					</div>
				</div>
			</div>
			<div class="well">
				<div>
					<strong class="row-title">
						<?php echo JText::_('COM_REDEVENT_VENUE_STATS'); ?>
					</strong>
					<div>
						<?php echo JText::_('COM_REDEVENT_VENUES_PUBLISHED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_VENUES_PUBLISHED'); ?>">
							<?php echo $this->venuesStats['published'] ? $this->venuesStats['published'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_VENUES_UNPUBLISHED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_VENUES_UNPUBLISHED'); ?>">
							<?php echo $this->venuesStats['unpublished'] ? $this->venuesStats['unpublished'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_VENUES_TOTAL'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_VENUES_TOTAL'); ?>">
							<?php echo $this->venuesStats['total'] ? $this->venuesStats['total'] : 0; ?>
						</span>
					</div>
				</div>
			</div>
			<div class="well">
				<div>
					<strong class="row-title">
						<?php echo JText::_('COM_REDEVENT_CATEGORY_STATS'); ?>
					</strong>
					<div>
						<?php echo JText::_('COM_REDEVENT_CATEGORIES_PUBLISHED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_CATEGORIES_PUBLISHED'); ?>">
							<?php echo $this->categoriesStats['published'] ? $this->categoriesStats['published'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_CATEGORIES_UNPUBLISHED'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_CATEGORIES_UNPUBLISHED'); ?>">
							<?php echo $this->categoriesStats['unpublished'] ? $this->categoriesStats['unpublished'] : 0; ?>
						</span>
					</div>
					<div>
						<?php echo JText::_('COM_REDEVENT_CATEGORIES_TOTAL'); ?>
						<span class="badge badge-success pull-right" title="<?php echo JText::_('COM_REDEVENT_CATEGORIES_TOTAL'); ?>">
							<?php echo $this->categoriesStats['total'] ? $this->categoriesStats['total'] : 0; ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
