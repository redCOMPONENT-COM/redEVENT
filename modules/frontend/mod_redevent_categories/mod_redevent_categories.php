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

// Get helper
require_once 'helper.php';

$list = ModRedEventCategoriesHelper::getList($params);

// Check if any results returned
$items = count($list);

if (!$items)
{
	return;
}

RHelperAsset::load('mod_redevent_categories.css', 'mod_redevent_categories');
RHelperAsset::load('mod_redevent_categories.js', 'mod_redevent_categories');

if (JRequest::getCmd('option') == 'com_redevent' && JRequest::getCmd('view') == 'categoryevents')
{
	$currents = modRedEventCategoriesHelper::getParentsCats(JRequest::getInt('id'));
}
else
{
	$currents = array();
}

require JModuleHelper::getLayoutPath('mod_redevent_categories');
