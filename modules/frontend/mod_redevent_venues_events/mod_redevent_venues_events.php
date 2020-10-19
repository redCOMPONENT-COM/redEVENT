<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load redEVENT library
$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

if (!file_exists($redeventLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
}

include_once $redeventLoader;

RedeventBootstrap::bootstrap();

require_once 'helper.php';

$list = ModRedEventVenuesEventsHelper::getList($params);

if (!count($list))
{
	return;
}

JHTML::_('behavior.tooltip');

RHelperAsset::load('mod_redevent_venues_events.css', 'mod_redevent_venues_events');
RHelperAsset::load('mod_redevent_venues_events.js', 'mod_redevent_venues_events');

require JModuleHelper::getLayoutPath('mod_redevent_venues_events');
