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

$user = JFactory::getUser();

if (!$user->get('id'))
{
	echo JText::_('MOD_REDEVENT_ATTENDING_MUST_BE_LOGGED');

	return;
}

$list = ModRedeventAttendingHelper::getList($params);

$offset = JFactory::getApplication()->input->getInt('reattoffset', (int) $params->get('offset', '0'));
$type = JFactory::getApplication()->input->getInt('reattspan', $params->get('type', '0'));
$uri = JFactory::getUri();

$curi = clone $uri;
$curi->setVar('reattoffset', $offset);
$curi->setVar('reattspan', $type);

$select = modRedEventAttendingHelper::getSelect($params);

// Quick links to previous and next
$prevuri = clone $curi;
$prevuri->setVar('reattoffset', $offset - 1);
$previous = htmlspecialchars($prevuri->toString());

$nexturi = clone $curi;
$nexturi->setVar('reattoffset', $offset + 1);
$next = htmlspecialchars($nexturi->toString());

RHelperAsset::load('mod_redevent_attending.js', 'mod_redevent_attending');
RHelperAsset::load('mod_redevent_attending.css', 'mod_redevent_attending');

require JModuleHelper::getLayoutPath('mod_redevent_attending', $params->get('layout', 'table'));
