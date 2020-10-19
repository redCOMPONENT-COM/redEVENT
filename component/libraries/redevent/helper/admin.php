<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper for admin
 *
 * @package  Redevent.Library
 * @since    3.2.0
 */
abstract class RedeventHelperAdmin
{
	/**
	 * Return items for building menus
	 *
	 * @return array
	 */
	public static function getAdminMenuItems()
	{
		$return = base64_encode('index.php?option=com_redevent');

		$items = array(
			'events' => array(
				'icon' => 'icon-calendar',
				'text' => JText::_('COM_REDEVENT_EVENTS'),
				'items' => array(
					array(
						'view' => 'events', 'link' => 'index.php?option=com_redevent&view=events', 'icon' => 'icon-list',
						'text' => JText::_('COM_REDEVENT_EVENTS'), 'access' => 'core.edit'
					),
					array(
						'view' => 'sessions', 'link' => 'index.php?option=com_redevent&view=sessions', 'icon' => 'icon-calendar',
						'text' => JText::_('COM_REDEVENT_SESSIONS'), 'access' => 'core.edit'
					),
					array(
						'view' => 'categories', 'link' => 'index.php?option=com_redevent&view=categories', 'icon' => 'icon-sitemap',
						'text' => JText::_('COM_REDEVENT_CATEGORIES'), 'access' => 'core.edit'
					),
					array(
						'view' => 'eventtemplates', 'link' => 'index.php?option=com_redevent&view=eventtemplates', 'icon' => 'icon-copy',
						'text' => JText::_('COM_REDEVENT_EVENT_TEMPLATES'), 'access' => 'core.edit'
					),
					array(
						'view' => 'registrations', 'link' => 'index.php?option=com_redevent&view=registrations', 'icon' => 'icon-group',
						'text' => JText::_('COM_REDEVENT_REGISTRATIONS'), 'access' => 'core.edit'
					),
				)
			),
			'venues' => array(
				'icon' => 'icon-map-marker',
				'text' => JText::_('COM_REDEVENT_VENUES'),
				'items' => array(
					array(
						'view' => 'venues', 'link' => 'index.php?option=com_redevent&view=venues', 'icon' => 'icon-map-marker',
						'text' => JText::_('COM_REDEVENT_VENUES'), 'access' => 'core.edit'
					),
					array(
						'view' => 'venuescategories', 'link' => 'index.php?option=com_redevent&view=venuescategories', 'icon' => 'icon-sitemap',
						'text' => JText::_('COM_REDEVENT_VENUES_CATEGORIES'), 'access' => 'core.edit'
					),
				)
			),
			'features' => array(
				'icon' => 'icon-list',
				'text' => JText::_('COM_REDEVENT_MENU_FEATURES'),
				'items' => array(
					array(
						'view' => 'textsnippets', 'link' => 'index.php?option=com_redevent&view=textsnippets', 'icon' => 'icon-book',
						'text' => JText::_('COM_REDEVENT_TEXT_LIBRARY'), 'access' => 'core.edit'
					),
					array(
						'view' => 'customfields', 'link' => 'index.php?option=com_redevent&view=customfields', 'icon' => 'icon-pencil',
						'text' => JText::_('COM_REDEVENT_CUSTOM_FIELDS'), 'access' => 'core.edit'
					),
					array(
						'view' => 'roles', 'link' => 'index.php?option=com_redevent&view=roles', 'icon' => 'icon-user',
						'text' => JText::_('COM_REDEVENT_ROLES'), 'access' => 'core.edit'
					),
					array(
						'view' => 'pricegroups', 'link' => 'index.php?option=com_redevent&view=pricegroups', 'icon' => 'icon-usd',
						'text' => JText::_('COM_REDEVENT_MENU_PRICEGROUPS'), 'access' => 'core.edit'
					),
				)
			),
			'tools' => array(
				'icon' => 'icon-wrench',
				'text' => JText::_('COM_REDEVENT_TOOLS'),
				'items' => array(
					array(
						'view' => 'tools', 'link' => 'index.php?option=com_redevent&view=tools', 'icon' => 'icon-wrench',
						'text' => JText::_('COM_REDEVENT_TOOLS'), 'access' => 'core.manage'
					),
					array(
						'view' => 'logs', 'link' => 'index.php?option=com_redevent&view=logs', 'icon' => 'icon-book',
						'text' => JText::_('COM_REDEVENT_LOG'), 'access' => 'core.manage'
					),
					array(
						'view' => 'config',
						'link' => 'index.php?option=com_redcore&view=config&layout=edit&component=com_redevent&return=' . $return,
						'icon' => 'icon-cogs', 'text' => JText::_('COM_REDEVENT_SETTINGS'), 'access' => 'core.manage'
					),
				)
			),
		);

		if (RedeventHelper::config()->get('redmember_integration_b2b', 0))
		{
			$items['redmember'] = array(
				'icon' => 'icon-group',
				'text' => 'redMEMBER',
				'items' => array(
					array(
						'view' => 'organizations', 'link' => 'index.php?option=com_redevent&view=organizations', 'icon' => 'icon-group',
						'text' => JText::_('COM_REDEVENT_ORGANIZATIONS'), 'access' => 'core.edit'
					)
				)
			);
		}

		if (RedeventHelper::config()->get('enable_bundles', 0))
		{
			$items['features']['items'][] = array(
				'view' => 'bundles',
				'link' => 'index.php?option=com_redevent&view=bundles', 'icon' => 'icon-briefcase',
				'text' => JText::_('COM_REDEVENT_MENU_BUNDLES'), 'access' => 'core.edit'
			);
		}

		JPluginHelper::importPlugin('redevent');
		$dispatcher = RFactory::getDispatcher();
		$dispatcher->trigger('onGetRedeventAdminMenuItems', array(&$items));

		return $items;
	}
}
