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
require_once (dirname(__FILE__).'/helper.php');

$list = modRedEventCategoriesHelper::getList($params);

// Check if any results returned
$items = count($list);

if (!$items)
{
	return;
}

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . '/modules/mod_redevent_categories/mod_redevent_categories.css');
$document->addScript(JURI::base() . '/modules/mod_redevent_categories/mod_redevent_categories.js');

if (JRequest::getCmd('option') == 'com_redevent' && JRequest::getCmd('view') == 'categoryevents')
{
	$currents = modRedEventCategoriesHelper::getParentsCats(JRequest::getInt('id'));
}
else
{
	$currents = array();
}

require(JModuleHelper::getLayoutPath('mod_redevent_categories'));
