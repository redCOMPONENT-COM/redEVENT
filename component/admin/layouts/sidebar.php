<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$user = JFactory::getUser();
$active = null;
$data = $displayData;
$params = RedeventHelper::config();

if (isset($data['active']))
{
	$active = $data['active'];
}

$icons = array(
	array('view' => 'events', 'icon' => 'icon-16-events.png', 'text' => JText::_('COM_REDEVENT_EVENTS'), 'access' => 'core.edit'),
	array('view' => 'sessions', 'icon' => 'icon-16-events.png', 'text' => JText::_('COM_REDEVENT_SESSIONS'), 'access' => 'core.edit'),
	array('view' => 'venues', 'icon' => 'icon-16-venues.png', 'text' => JText::_('COM_REDEVENT_VENUES'), 'access' => 'core.edit'),
	array('view' => 'categories', 'icon' => 'icon-16-categories.png', 'text' => JText::_('COM_REDEVENT_CATEGORIES'), 'access' => 'core.edit'),
	array('view' => 'venuescategories', 'icon' => 'icon-16-venuescategories.png', 'text' => JText::_('COM_REDEVENT_VENUES_CATEGORIES'), 'access' => 'core.edit'),
	array('view' => 'registrations', 'icon' => 'icon-16-groups.png', 'text' => JText::_('COM_REDEVENT_REGISTRATIONS'), 'access' => 'core.edit'),
	array('view' => 'textsnippets', 'icon' => 'icon-16-library.png', 'text' => JText::_('COM_REDEVENT_TEXT_LIBRARY'), 'access' => 'core.edit'),
	array('view' => 'customfields', 'icon' => 'icon-16-customfields.png', 'text' => JText::_('COM_REDEVENT_CUSTOM_FIELDS'), 'access' => 'core.edit'),
	array('view' => 'roles', 'icon' => 'icon-16-groups.png', 'text' => JText::_('COM_REDEVENT_ROLES'), 'access' => 'core.edit'),
	array('view' => 'pricegroups', 'icon' => 'icon-16-featured.png', 'text' => JText::_('COM_REDEVENT_MENU_PRICEGROUPS'), 'access' => 'core.edit'),
	array('view' => 'tools', 'icon' => 'icon-16-settings.png', 'text' => JText::_('COM_REDEVENT_TOOLS'), 'access' => 'core.manage'),
	array('view' => 'logs', 'icon' => 'icon-16-events.png', 'text' => JText::_('COM_REDEVENT_LOG'), 'access' => 'core.manage'),
	array('view' => 'configuration', 'icon' => 'icon-16-settings.png', 'text' => JText::_('COM_REDEVENT_SETTINGS'), 'access' => 'core.manage'),
);

if ($params->get('redmember_integration_b2b', 0))
{
	$icons[] = array('view' => 'organizations', 'icon' => 'icon-16-groups.png', 'text' => JText::_('COM_REDEVENT_ORGANIZATIONS'), 'access' => 'core.edit');
}

// Configuration link
$uri = JUri::getInstance();
$return = base64_encode('index.php' . $uri->toString(array('query')));
$configurationLink = 'index.php?option=com_redcore&view=config&layout=edit&component=com_redevent&return=' . $return;
?>

<ul class="nav nav-pills nav-stacked redmember-sidebar">
	<?php foreach ($icons as $icon) : ?>
		<?php if ($user->authorise($icon['access'], 'com_redevent')): ?>
			<?php $class = ($active === $icon['view']) ? 'active' : ''; ?>
			<li class="<?php echo $class; ?>">
				<?php if ($icon['view'] == 'configuration') : ?>
					<?php $link = $configurationLink; ?>
				<?php else : ?>
					<?php $link = JRoute::_('index.php?option=com_redevent&view=' . $icon['view']); ?>
				<?php endif; ?>
				<a href="<?php echo $link; ?>">
					<?php echo JHTML::_('image', 'media/com_redevent/images/' . $icon['icon'], $icon['text']); ?>
					<?php echo $icon['text']; ?>
				</a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
