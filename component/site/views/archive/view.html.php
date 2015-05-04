<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML archive View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewArchive extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		// Initialize variables
		$document = JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$menu = $mainframe->getMenu();
		$item = $menu->getActive();
		$params = $mainframe->getParams();
		$uri = JFactory::getURI();
		$pathway = $mainframe->getPathWay();
		$state = $this->get('state');

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// Add js
		JHTML::_('behavior.framework');

		// For filter hint
		RHelperAsset::load('eventslist.js');

		// Get variables
		$task = JRequest::getWord('task');
		$pop = JRequest::getBool('pop');

		// Get data from model
		$rows = $this->get('Data');
		$customs = $this->get('ListCustomFields');
		$customsfilters = $this->get('CustomFilters');
		$pagination = $this->get('Pagination');


		// Are events available?
		if (!$rows)
		{
			$noevents = 1;
		}
		else
		{
			$noevents = 0;
		}

		// Params
		$params->def('page_title', (isset($item->title) ? $item->title : JText::_('COM_REDEVENT_Events')));

		if ($pop)
		{
			// If printpopup set true
			$params->set('popup', 1);
			$this->setLayout('print');
		}

		$print_link = JRoute::_(RedeventHelperRoute::getArchiveRoute() . '&pop=1');
		$pagetitle = $params->get('page_title');

		$list_link = RedeventHelperRoute::getSimpleListRoute();

		// Set Page title
		$this->document->setTitle($pagetitle);

		// Create select lists
		$lists = $this->_buildSortLists();

		$filter_customs = $state->get('filter_customs');

		$this->assign('lists', $lists);

		$this->assignRef('rows', $rows);
		$this->assignRef('customs', $customs);
		$this->assignRef('customsfilters', $customsfilters);
		$this->assignRef('task', $task);
		$this->assignRef('noevents', $noevents);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('params', $params);
		$this->assignRef('dellink', $dellink);
		$this->assignRef('pageNav', $pagination);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('pagetitle', $pagetitle);
		$this->assignRef('config', $elsettings);
		$this->assignRef('thumb_link', $thumb_link);
		$this->assignRef('list_link', $list_link);
		$this->assign('filter_customs', $filter_customs);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);

		parent::display($tpl);
	}

	/**
	 * Method to build the sortlists
	 *
	 * @return array
	 */
	function _buildSortLists()
	{
		$app = JFactory::getApplication();
		$uri = JFactory::getURI();

		// Remove previously set filter in get
		$uri->delVar('filter');
		$uri->delVar('filter_type');
		$uri->delVar('filter_category');
		$uri->delVar('filter_venuecategory');
		$uri->delVar('filter_venue');
		$uri->delVar('filter_event');
		$uri->delVar('filtercustom');

		$elsettings = RedeventHelper::config();
		$params = $app->getParams();

		$filter_order = JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'ASC');

		$state = $this->get('state');

		$filter = $state->get('filter');
		$filter_type = $state->get('filter_type');
		$filter_category = $state->get('filter_category');
		$filter_venue = $state->get('filter_venue');
		$filter_event = $state->get('filter_event');

		$this->assign('action', JRoute::_(RedeventHelperRoute::getArchiveRoute()));

		$sortselects = array();
		if ($params->get('filter_type_event', 1)) $sortselects[] = JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT'));
		if ($params->get('filter_type_venue', 1)) $sortselects[] = JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE'));
		if ($params->get('filter_type_city', 1)) $sortselects[] = JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY'));
		if ($params->get('filter_type_category', 1)) $sortselects[] = JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY'));

		if (count($sortselects) == 0)
		{
			$sortselect = false;
		}
		elseif (count($sortselects) == 1)
		{
			$sortselect = '<input type="hidden" name="filter_type" value="' . $sortselects[0]->value . '" />';
		}
		else
		{
			$sortselect = JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type);
		}

		// Category filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY')));
		$options = array_merge($options, $this->get('CategoriesOptions'));
		$lists['categoryfilter'] = JHTML::_('select.genericlist', $options, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_category);

		// Venue filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE')));
		$options = array_merge($options, $this->get('VenuesOptions'));
		$lists['venuefilter'] = JHTML::_('select.genericlist', $options, 'filter_venue', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_venue);

		// Events filter
		if ($params->get('lists_filter_event', 0))
		{
			$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT')));
			$options = array_merge($options, $this->get('EventsOptions'));
			$lists['eventfilter'] = JHTML::_('select.genericlist', $options, 'filter_event', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_event);
		}

		$this->order = $state->get('filter_order');
		$this->orderDir = $state->get('filter_order_Dir');
		$lists['filter'] = $filter;
		$lists['filter_type'] = $sortselect;

		return $lists;
	}
}
