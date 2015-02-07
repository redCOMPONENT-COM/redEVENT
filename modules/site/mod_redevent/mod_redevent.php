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

$list = modRedEventHelper::getList($params);

// Check if any results returned
$items = count($list);

if (!$items)
{
	return;
}

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . '/modules/mod_redevent/mod_redevent.css');

$layout = $params->get('layout');

if ($layout == '_:table')
{
	$cols = explode(",", $params->get('table_cols', 'date, title'));
	$cols = array_map('trim', $cols);
	$cols = array_map('strtolower', $cols);

	$customfields = modRedEventHelper::getCustomFields();
}

require(JModuleHelper::getLayoutPath('mod_redevent', $layout));
