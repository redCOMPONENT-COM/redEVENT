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

defined('_JEXEC') or die('Restricted access');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

require_once (dirname(__FILE__).'/helper.php');

$app = JFactory::getApplication();

RHelperAsset::load('mod_redevent_search.css', 'mod_redevent_search');

$helper = new modRedEventSearchHelper();

$elsettings = RedeventHelper::config();

$action = JRoute::_(RedeventHelperRoute::getSearchRoute());

$input = JFactory::getApplication()->input;

// FILTERS
$lists = array();

$lists['filter'] = $input->get('filter');

$sortselects = array();
if ($params->get('filter_type_event', 1))
{
	$sortselects[]	= JHTML::_('select.option', 'title', JText::_('MOD_REDEVENT_SEARCH_SELECT_EVENT'));
}

if ($params->get('filter_type_venue', 0))
{
	$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('MOD_REDEVENT_SEARCH_SELECT_VENUE'));
}

if ($params->get('filter_type_city', 0))
{
	$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('MOD_REDEVENT_SEARCH_SELECT_CITY'));
}

if ($params->get('filter_type_category', 0))
{
	$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('MOD_REDEVENT_SEARCH_SELECT_CATEGORY'));
}

if (count($sortselects) == 0)
{
	$sortselect = false;
}
elseif (count($sortselects) == 1)
{
	$sortselect = '<input type="hidden" name="filter_type" value="' . $sortselects[0]->value . '" />' . $sortselects[0]->text;
}
else
{
	$sortselect = JHTML::_(
		'select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $input->get('filter_type')
	);
}

$lists['filter_types'] = $sortselect;

// Category filter
$options = array(JHTML::_('select.option', '', JText::_('MOD_REDEVENT_SEARCH_FILTER_SELECT_CATEGORY')));
$options = array_merge($options, $helper->getCategoriesOptions());
$lists['categories'] = JHTML::_(
	'select.genericlist', $options, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', $input->getInt('filter_category')
);

$lists['multiple_categories'] = JHTML::_(
	'select.genericlist', $options, 'filter_multicategory[]', 'size="6" multiple="multiple" class="inputbox dynfilter"',
	'value', 'text', $input->get('filter_multicategory', array(), 'array')
);

// Venue filter
$options = array(JHTML::_('select.option', '', JText::_('MOD_REDEVENT_SEARCH_FILTER_SELECT_VENUE')));
$options = array_merge($options, $helper->getVenuesOptions());
$lists['venues'] = JHTML::_(
	'select.genericlist', $options, 'filter_venue', 'size="1" class="inputbox dynfilter"', 'value', 'text', $input->getInt('filter_venue')
);

$filter_date_from = $input->get('filter_date_from');
$filter_date_to   = $input->get('filter_date_to');

$customsfilters = $helper->getCustomFilters();

$search = $input->getString('search', '');
$query = $input->getString('query', '');

if ($search == 'ajax' && $query)
{
	$helper->getAjaxSearch($query);
	$app->close();
}

// DISPLAY
require(JModuleHelper::getLayoutPath('mod_redevent_search', $params->get('layout', 'default')));
