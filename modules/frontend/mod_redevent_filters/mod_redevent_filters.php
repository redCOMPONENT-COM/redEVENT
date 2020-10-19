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

$helper = new ModRedeventFiltersHelper;
$model = $helper->getModel();

$data = $helper->getData($params);

// Check if any results returned
if (!$data)
{
	return;
}

RHelperAsset::load('mod_redevent_filters.css', 'mod_redevent_filters');
RHelperAsset::load('mod_redevent_filters.js', 'mod_redevent_filters');

require JModuleHelper::getLayoutPath('mod_redevent_filters');
