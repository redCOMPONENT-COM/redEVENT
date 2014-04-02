<?php
/**
 * @package redevent
 * @subpackage mod_redevent_categories
 * @copyright (C) 2011 Redweb.dk
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

$list = modRedEventCategoriesHelper::getList($params);

// check if any results returned
$items = count($list);
if (!$items) {
 return;
}

$document = &JFactory::getDocument();
$document->addStyleSheet( JURI::base() . '/modules/mod_redevent_categories/mod_redevent_categories.css' );
$document->addScript(JURI::base() . '/modules/mod_redevent_categories/mod_redevent_categories.js' );

if (JRequest::getCmd('option') == 'com_redevent' && JRequest::getCmd('view') == 'categoryevents') {
	$currents = modRedEventCategoriesHelper::getParentsCats(JRequest::getInt('id'));
}
else {
	$currents = array();
}

require(JModuleHelper::getLayoutPath('mod_redevent_categories'));
