<?php
/**
 * @version    1.0 $Id$
 * @package    Joomla
 * @subpackage redEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Venueevents View
 *
 * @package    Joomla
 * @subpackage redEVENT
 * @since      0.9
 */
class RedeventViewVenueevents extends RViewSite
{
	/**
	 * Creates the Venueevents View
	 *
	 * @since 0.9
	 */
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialize variables
		$document = JFactory::getDocument();
		$menu = $mainframe->getMenu();
		$elsettings = RedeventHelper::config();
		$item = $menu->getActive();
		$params = $mainframe->getParams('com_redevent');
		$uri = JFactory::getURI();
		$acl = RedeventUserAcl::getInstance();

		//add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// add js
		JHTML::_('behavior.framework');

		// Request variables
		$limitstart = JRequest::getInt('limitstart');
		$limit = $mainframe->getUserStateFromRequest('com_redevent.venueevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$pop = JRequest::getBool('pop');
		$task = JRequest::getWord('task');

		//get data from model
		$rows = $this->get('Data');
		$venue = $this->get('Venue');
		$total = $this->get('Total');
		$customs = $this->get('ListCustomFields');
		$customsfilters = $this->get('CustomFilters');

		//does the venue exist?
		if ($venue->id == 0)
		{
			return JError::raiseError(404, JText::sprintf('COM_REDEVENT_Venue_d_not_found', $venue->id));
		}

		//are events available?
		if (!$rows)
		{
			$noevents = 1;
		}
		else
		{
			$noevents = 0;
		}

		// Add needed scripts if the lightbox effect is enabled
		JHTML::_('behavior.modal');

		//add alternate feed link
		$link = 'index.php?option=com_redevent&view=venueevents&format=feed&id=' . $venue->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

		//pathway
		$pathway = $mainframe->getPathWay();

		//create the pathway
		if ($task == 'archive')
		{
			$link = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug, 'archive'));
			$pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE') . ' - ' . $venue->venue, $link);
			$print_link = JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $venue->slug . '&task=archive&pop=1&tmpl=component');
			$pagetitle = $venue->venue . ' - ' . JText::_('COM_REDEVENT_ARCHIVE');
		}
		else
		{
			$link = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug));
			$pathway->addItem($venue->venue, $link);
			$print_link = JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $venue->slug . '&pop=1&tmpl=component');
			$pagetitle = $venue->venue;
		}
		$thumb_link = RedeventHelperRoute::getVenueEventsRoute($venue->slug, null, 'thumb');
		$list_link = RedeventHelperRoute::getVenueEventsRoute($venue->slug, null, 'default');

		//set Page title
		$this->document->setTitle($pagetitle);
		$document->setMetadata('keywords', $venue->meta_keywords);
		$document->setDescription(strip_tags($venue->meta_description));

		//Printfunction
		$params->def('print', !$mainframe->getCfg('hidePrint'));
		$params->def('icons', $mainframe->getCfg('icons'));

		if ($pop)
		{
			$params->set('popup', 1);
		}

		//Check if the user has access to the form
		$maintainer = $acl->canEditVenue($venue->id);

		//Generate Venuedescription
		if (!empty ($venue->locdescription))
		{
			//execute plugins
			$venuedescription = JHTML::_('content.prepare', $venue->locdescription);
		}

		//build the url
		if (!empty($venue->url) && strtolower(substr($venue->url, 0, 7)) != "http://")
		{
			$venue->url = 'http://' . $venue->url;
		}

		//prepare the url for output
		if (strlen(htmlspecialchars($venue->url, ENT_QUOTES)) > 35)
		{
			$venue->urlclean = substr(htmlspecialchars($venue->url, ENT_QUOTES), 0, 35) . '...';
		}
		else
		{
			$venue->urlclean = htmlspecialchars($venue->url, ENT_QUOTES);
		}

		//create flag
		if ($venue->country)
		{
			$venue->countryimg = RedeventHelperCountries::getCountryFlag($venue->country);
		}

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		//create select lists
		$lists = $this->_buildSortLists($elsettings);

		$state = $this->get('state');
		$filter_customs = $state->get('filter_customs');

		$this->assign('lists', $lists);
		$this->assign('action', JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug)));
		$this->assignRef('state', $state);

		$this->assignRef('rows', $rows);
		$this->assignRef('customs', $customs);
		$this->assignRef('noevents', $noevents);
		$this->assignRef('venue', $venue);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('params', $params);
		$this->assignRef('editlink', $maintainer);
		$this->assignRef('venuedescription', $venuedescription);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item', $item);
		$this->assignRef('pagetitle', $pagetitle);
		$this->assignRef('task', $task);
		$this->assignRef('config', $elsettings);
		$this->assignRef('thumb_link', $thumb_link);
		$this->assignRef('list_link', $list_link);
		$this->assignRef('customsfilters', $customsfilters);
		$this->assign('filter_customs', $filter_customs);
		$this->assign('state', $this->get('state'));

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);

		if ($state->get('results_type') == 0)
		{
			$this->setLayout('searchevents');
			$allowed = array(
				'title',
				'venue',
				'category',
				'picture',
			);
			$cols = RedeventHelper::validateColumns($cols, $allowed);
		}

		$this->assign('columns', $cols);

		parent::display($tpl);
	}

	function _buildSortLists($elsettings)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Table ordering values
		$filter_order = JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'ASC');

		$state = $this->get('state');

		$filter = $state->get('filter');
		$filter_type = $state->get('filter_type');
		$filter_category = $state->get('filter_category');
		$filter_venue = $state->get('filter_venue');
		$filter_event = $state->get('filter_event');

		$sortselects = array();
		if ($params->get('filter_type_event', 1)) $sortselects[] = JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT'));
		if ($params->get('filter_type_city', 1)) $sortselects[] = JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY'));
		if ($params->get('filter_type_category', 1)) $sortselects[] = JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY'));

		if (count($sortselects) == 0)
		{
			$sortselect = false;
		}
		else if (count($sortselects) == 1)
		{
			$sortselect = '<input type="hidden" name="filter_type" value="' . $sortselects[0]->value . '" />';
		}
		else
		{
			$sortselect = JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type);
		}

		// events filter
		if ($params->get('lists_filter_event', 0))
		{
			$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT')));
			$options = array_merge($options, $this->get('EventsOptions'));
			$lists['eventfilter'] = JHTML::_('select.genericlist', $options, 'filter_event', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_event);
		}

		// category filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY')));
		$options = array_merge($options, $this->get('CategoriesOptions'));
		$lists['categoryfilter'] = JHTML::_('select.genericlist', $options, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_category);

		$this->order = $state->get('filter_order');
		$this->orderDir = $state->get('filter_order_Dir');
		$lists['filter'] = $filter;
		$lists['filter_type'] = $sortselect;

		return $lists;
	}
}
