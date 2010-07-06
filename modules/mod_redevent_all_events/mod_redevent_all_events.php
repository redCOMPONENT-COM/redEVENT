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

// get helper
require_once (dirname(__FILE__).DS.'helper.php');

require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

$list = modRedEventAllEventsHelper::getList($params);

// check if any results returned
$items = count($list);
if (!$items) {
	return;
}

JHTML::_('behavior.tooltip');
$document = &JFactory::getDocument(); 
$document->addStyleSheet( JURI::base() . '/modules/mod_redevent_all_events/mod_redevent_all_events.css' );

require(JModuleHelper::getLayoutPath('mod_redevent_all_events'));