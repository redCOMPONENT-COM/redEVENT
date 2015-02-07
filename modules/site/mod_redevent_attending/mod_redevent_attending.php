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

// get helper
require_once (dirname(__FILE__).'/helper.php');

$user = JFactory::getUser();

if (!$user->get('id'))
{
	echo JText::_('MOD_REDEVENT_ATTENDING_MUST_BE_LOGGED');

	return;
}

$list = modRedEventAttendingHelper::getList($params);

$offset = JFactory::getApplication()->input->getInt('reattoffset', (int) $params->get( 'offset', '0' ));
$type = JFactory::getApplication()->input->getInt('reattspan', $params->get( 'type', '0' ));
$uri = JFactory::getUri();

$curi = clone $uri;
$curi->setVar('reattoffset', $offset);
$curi->setVar('reattspan', $type);

$select = modRedEventAttendingHelper::getSelect($params);

// quick links to previous and next
$prevuri = clone $curi;
$prevuri->setVar('reattoffset', $offset-1);
$previous = htmlspecialchars($prevuri->toString());

$nexturi = clone $curi;
$nexturi->setVar('reattoffset', $offset+1);
$next = htmlspecialchars($nexturi->toString());

$document = Jfactory::getDocument();
$document->addScript('modules/mod_redevent_attending/mod_redevent_attending.js');
$document->addStyleSheet( JURI::base() . '/modules/mod_redevent_attending/mod_redevent_attending.css' );

require(JModuleHelper::getLayoutPath('mod_redevent_attending', $params->get('layout', 'table')));
