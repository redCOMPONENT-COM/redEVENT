<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

// get helper
require_once (dirname(__FILE__).DS.'helper.php');

require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'helper.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'image.class.php');

$user		=& JFactory::getUser();
if (!$user->get('id')) {
	echo JText::_('MOD_REDEVENT_ATTENDING_MUST_BE_LOGGED');
	return;
}

$list = modRedEventAttendingHelper::getList($params);

// check if any results returned
$items = count($list);

$document = &Jfactory::getDocument();
$document->addScript('modules/mod_redevent_attending/mod_redevent_attending.js');

$offset = JRequest::getInt('reattoffset', (int) $params->get( 'offset', '0' ));
$type   = JRequest::getInt('reattspan', $params->get( 'type', '0' ));
$uri    = &JFactory::getUri();

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
$next     = htmlspecialchars($nexturi->toString());

$document = &JFactory::getDocument();
$document->addStyleSheet( JURI::base() . '/modules/mod_redevent_attending/mod_redevent_attending.css' );

require(JModuleHelper::getLayoutPath('mod_redevent_attending', $params->get('layout', 'table')));
