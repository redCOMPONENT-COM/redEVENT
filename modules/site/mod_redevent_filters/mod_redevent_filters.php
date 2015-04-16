<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
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
require_once dirname(__FILE__) . '/helper.php';

$data = modRedEventFiltersHelper::getData($params);

// Check if any results returned
if (!$data)
{
	return;
}

$model = modRedEventFiltersHelper::getModel();;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_redevent_filters/mod_redevent_filters.css');
$document->addScript(JURI::base() . 'modules/mod_redevent_filters/mod_redevent_filters.js');

require(JModuleHelper::getLayoutPath('mod_redevent_filters'));
