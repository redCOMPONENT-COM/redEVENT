<?php
/**
 * THIS FILE IS BASED mod_eventlist_teaser from ezuri.de, BASED ON MOD_EVENTLIST_WIDE FROM SCHLU.NET
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2008 - 2011 redComponent
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

// get module helper
require_once (dirname(__FILE__).DS.'helper.php');

//require needed component classes
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'helper.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'image.class.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'useracl.class.php');

$list = modRedeventTeaserHelper::getList($params);

$document 	= & JFactory::getDocument();
$document->addStyleSheet(JURI::base(true).'/modules/mod_redevent_teaser/tmpl/mod_redevent_teaser.css');

if ($params->get('color') == 1) { 
$document->addStyleSheet(JURI::base(true).'/modules/mod_redevent_teaser/tmpl/red.css');
}
if ($params->get('color') == 2) { 
$document->addStyleSheet(JURI::base(true).'/modules/mod_redevent_teaser/tmpl/blue.css');
}
if ($params->get('color') == 3) { 
$document->addStyleSheet(JURI::base(true).'/modules/mod_redevent_teaser/tmpl/green.css');
}


// check if any results returned
$items = count($list);

if (!$items) {
	return;
}

require(JModuleHelper::getLayoutPath('mod_redevent_teaser', $params->get('layout')));
