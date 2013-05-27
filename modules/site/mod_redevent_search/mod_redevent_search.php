<?php
/**
 * @version 2.0
 * @package Joomla
 * @subpackage RedEvent search module
 * @copyright (C) 2011 redCOMPONENT.com
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
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'helper.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'useracl.class.php');

$app = JFactory::getApplication();

$document = &JFactory::getDocument();
$document->addStyleSheet( JURI::base() . 'modules/mod_redevent_search/mod_redevent_search.css' );

$helper = new modRedEventSearchHelper();

$elsettings = redEVENTHelper::config();

$action = JRoute::_(RedeventHelperRoute::getSearchRoute());


// FILTERS
$lists = array();

$lists['filter'] = JRequest::getVar('filter');

$sortselects = array();
if ($params->get('filter_type_event', 1))	$sortselects[]	= JHTML::_('select.option', 'title', JText::_('MOD_REDEVENT_SEARCH_SELECT_EVENT') );
if ($params->get('filter_type_venue', 0))	$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('MOD_REDEVENT_SEARCH_SELECT_VENUE') );
if ($params->get('filter_type_city', 0))	$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('MOD_REDEVENT_SEARCH_SELECT_CITY') );
if ($params->get('filter_type_category', 0))	$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('MOD_REDEVENT_SEARCH_SELECT_CATEGORY') );

if (count($sortselects) == 0) {
	$sortselect = false;
}
else if (count($sortselects) == 1) {
	$sortselect = '<input type="hidden" name="filter_type" value="'.$sortselects[0]->value.'" />'.$sortselects[0]->text;
}
else {
	$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', JRequest::getVar('filter_type') );
}
$lists['filter_types'] = $sortselect;

// category filter
$options = array(JHTML::_('select.option', '', JText::_('MOD_REDEVENT_SEARCH_FILTER_SELECT_CATEGORY') ));
$options = array_merge($options, $helper->getCategoriesOptions());
$lists['categories'] = JHTML::_('select.genericlist', $options, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', JRequest::getInt('filter_category'));

// venue filter
$options = array(JHTML::_('select.option', '', JText::_('MOD_REDEVENT_SEARCH_FILTER_SELECT_VENUE') ));
$options = array_merge($options, $helper->getVenuesOptions());
$lists['venues'] = JHTML::_('select.genericlist', $options, 'filter_venue', 'size="1" class="inputbox dynfilter"', 'value', 'text', JRequest::getInt('filter_venue'));

$filter_date_from = JRequest::getVar('filter_date_from');
$filter_date_to   = JRequest::getVar('filter_date_to');

$customsfilters = $helper->getCustomFilters();

$post = JRequest::get('post');
$search = isset($post['search']) ? $post['search'] : '';

if ($search =='ajax' && isset($post['query']))
{
	$helper->getAjaxSearch($post['query']);
	$app->close();
}

// DISPLAY
require(JModuleHelper::getLayoutPath('mod_redevent_search', $params->get('layout', 'default')));