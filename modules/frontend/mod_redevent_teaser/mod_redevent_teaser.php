<?php
/**
 * THIS FILE IS BASED mod_eventlist_teaser from ezuri.de, BASED ON MOD_EVENTLIST_WIDE FROM SCHLU.NET
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

// Get module helper
require_once 'helper.php';

$list = ModRedeventTeaserHelper::getList($params);

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base(true) . '/modules/mod_redevent_teaser/tmpl/mod_redevent_teaser.css');

if ($params->get('color') == 1)
{
	$document->addStyleSheet(JURI::base(true) . '/modules/mod_redevent_teaser/tmpl/red.css');
}

if ($params->get('color') == 2)
{
	$document->addStyleSheet(JURI::base(true) . '/modules/mod_redevent_teaser/tmpl/blue.css');
}

if ($params->get('color') == 3)
{
	$document->addStyleSheet(JURI::base(true) . '/modules/mod_redevent_teaser/tmpl/green.css');
}


// Check if any results returned
$items = count($list);

if (!$items)
{
	return;
}

require JModuleHelper::getLayoutPath('mod_redevent_teaser', $params->get('layout'));
