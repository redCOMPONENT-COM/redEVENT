<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

$return = base64_encode('index.php?option=com_redevent');

$icons = array(
	array('link' => 'index.php?option=com_redevent&view=events', 'icon' => 'icon-48-events.png', 'text' => JText::_('COM_REDEVENT_EVENTS'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=venues', 'icon' => 'icon-48-venues.png', 'text' => JText::_('COM_REDEVENT_VENUES'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=categories', 'icon' => 'icon-48-categories.png', 'text' => JText::_('COM_REDEVENT_CATEGORIES'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=venuescategories', 'icon' => 'icon-48-venuescategories.png', 'text' => JText::_('COM_REDEVENT_VENUES_CATEGORIES'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=registrations', 'icon' => 'icon-48-registrations.png', 'text' => JText::_('COM_REDEVENT_REGISTRATIONS'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=textsnippets', 'icon' => 'icon-48-library.png', 'text' => JText::_('COM_REDEVENT_TEXT_LIBRARY'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=customfields', 'icon' => 'icon-48-customfields.png', 'text' => JText::_('COM_REDEVENT_CUSTOM_FIELDS'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=roles', 'icon' => 'icon-48-roles.png', 'text' => JText::_('COM_REDEVENT_ROLES'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=pricegroups', 'icon' => 'icon-48-pricegroups.png', 'text' => JText::_('COM_REDEVENT_MENU_PRICEGROUPS'), 'access' => 'core.edit'),
	array('link' => 'index.php?option=com_redevent&view=tools', 'icon' => 'icon-48-housekeeping.png', 'text' => JText::_('COM_REDEVENT_TOOLS'), 'access' => 'core.manage'),
	array('link' => 'index.php?option=com_redevent&view=logs', 'icon' => 'icon-48-log.png', 'text' => JText::_('COM_REDEVENT_LOG'), 'access' => 'core.manage'),
	array('link' => 'index.php?option=com_redcore&view=config&layout=edit&component=com_redevent&return=' . $return, 'icon' => 'icon-48-settings.png', 'text' => JText::_('COM_REDEVENT_SETTINGS'), 'access' => 'core.manage'),
);
?>

<script type="text/javascript">
Joomla.submitbutton = function (pressbutton) {
	submitbutton(pressbutton);
};
</script>

<div class="row-fluid">
	<div class="span9 reDashboardMainIcons">
		<?php $iconsRow = array_chunk($icons, 6); ?>
		<?php foreach ($iconsRow as $row) : ?>
		<p></p>
		<div class="row-fluid">
			<?php foreach ($row as $icon) : ?>
			<?php if ($this->user->authorise($icon['access'], 'com_redevent')): ?>
				<div class="span2">
					<a class="reDashboardIcons" href="<?php echo JRoute::_($icon['link']); ?>">
						<div class="row-fluid pagination-centered">
							<span class="dashboard-icon-link-icon">
								<?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/' . $icon['icon'], $icon['text']); ?>
							</span>
						</div>
						<div class="row-fluid pagination-centered">
							<p class="dashboard-icon-link-text">
								<strong><?php echo $icon['text']; ?></strong>
							</p>
						</div>
					</a>
				</div>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>
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
