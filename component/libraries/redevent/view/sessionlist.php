<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');


/**
 * HTML events list View class of the redEVENT component
 *
 * @package  Redevent.Library
 * @since    3.0
 */
abstract class RedeventViewSessionlist extends RedeventViewFront
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
		$this->prepareView();

		parent::display($tpl);
	}

	/**
	 * Prepare the view
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Get data from model
		$rows = $this->get('Data');
		$pagination = $this->get('Pagination');
		$customs = $this->get('ListCustomFields');
		$customsfilters = $this->get('CustomFilters');

		// Create select lists
		$this->buildSortLists();

		// Action for filter form
		$this->prepareAction();

		$this->assignRef('rows', $rows);
		$this->assignRef('customs', $customs);
		$this->assignRef('customsfilters', $customsfilters);
		$this->assignRef('pageNav', $pagination);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$app = JFactory::getApplication();
		$menuItem = $app->getMenu()->getActive();
		$params = $app->getParams();

		$params->def('page_title', (isset($menuItem->title) ? $menuItem->title : JText::_('COM_REDEVENT')));

		return $params->get('page_title');
	}

	/**
	 * Prepare form action
	 *
	 * @return void
	 */
	protected function prepareAction()
	{
		$uri = JFactory::getURI();

		// Remove previously set filter in get
		$uri->delVar('filter');
		$uri->delVar('filter_type');
		$uri->delVar('filter_category');
		$uri->delVar('filter_venuecategory');
		$uri->delVar('filter_venue');
		$uri->delVar('filter_event');
		$uri->delVar('filter_continent');
		$uri->delVar('filter_country');
		$uri->delVar('filter_state');
		$uri->delVar('filter_city');
		$uri->delVar('filter_date_from');
		$uri->delVar('filter_date_to');
		$uri->delVar('filtercustom');

		$this->assign('action', JRoute::_('index.php?option=com_redevent&view=' . $this->getName()));
	}

	/**
	 * Get feed link
	 *
	 * @return void
	 */
	protected function getFeedLink()
	{
		return 'index.php?option=com_redevent&format=feed&view=' . $this->getName();
	}

	/**
	 * Method to build the sort lists
	 *
	 * @return void
	 */
	protected function buildSortLists()
	{
		$state = $this->get('state');
		$params = RedeventHelper::config();

		$filter = $state->get('filter');
		$filter_category = $state->get('filter_category');
		$filter_venue = $state->get('filter_venue');
		$filter_event = $state->get('filter_event');
		$filter_venuecategory = $state->get('filter_venuecategory');
		$filter_country   = $state->get('filter_country');
		$filter_city      = $state->get('filter_city');
		$filter_state     = $state->get('filter_state');
		$filter_date_from = $state->get('filter_date_from');
		$filter_date_to   = $state->get('filter_date_to');

		// Category filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY')));
		$options = array_merge($options, $this->get('CategoriesOptions'));
		$lists['categoryfilter'] = JHTML::_(
			'select.genericlist', $options, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_category
		);

		// Venue filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE')));
		$options = array_merge($options, $this->get('VenuesOptions'));
		$lists['venuefilter'] = JHTML::_(
			'select.genericlist', $options, 'filter_venue', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_venue
		);

		// Events filter
		if ($params->get('lists_filter_event', 0))
		{
			$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT')));
			$options = array_merge($options, $this->get('EventsOptions'));
			$lists['eventfilter'] = JHTML::_(
				'select.genericlist', $options, 'filter_event', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_event
			);
		}

		$vcatoptions = array();
		$vcatoptions[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_venue_category'));
		$vcatoptions = array_merge($vcatoptions, RedeventHelper::getVenuesCatOptions());
		$selectedcats = ($filter_venuecategory) ? array($filter_venuecategory) : array();
		$lists['vcategories'] = JHTML::_(
			'select.genericlist', $vcatoptions, 'filter_venuecategory', 'size="1" class="inputbox dynfilter"', 'value', 'text', $selectedcats
		);
		unset($catoptions);

		// Country filter
		$countries = array();
		$countries[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_country'));
		$countries = array_merge($countries, $this->get('CountryOptions'));
		$lists['countries'] = JHTML::_('select.genericlist', $countries, 'filter_country', 'class="inputbox"', 'value', 'text', $filter_country);
		unset($countries);

		// State filter
		$states = array();
		$states[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_state'));
		$states = array_merge($states, $this->get('StateOptions'));
		$lists['states'] = JHTML::_('select.genericlist', $states, 'filter_state', 'class="inputbox"', 'value', 'text', $filter_state);
		unset($states);

		// City filter
		$cities = array();
		$cities[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_city'));
		$cities = array_merge($cities, $this->get('CityOptions'));
		$lists['cities'] = JHTML::_('select.genericlist', $cities, 'filter_city', 'class="inputbox"', 'value', 'text', $filter_city);
		unset($cities);

		$lists['filter'] = $filter;

		$this->lists = $lists;

		$this->order = $state->get('filter_order');
		$this->orderDir = $state->get('filter_order_Dir');
	}
}
