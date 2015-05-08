<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');


/**
 * HTML events list View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    3.0
 */
class RedeventViewSessionlist extends RViewSite
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
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$config = RedeventHelper::config();
		$params = $app->getParams();

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		// Get variables
		$task = JRequest::getWord('task');
		$pop = JRequest::getBool('pop');

		// Get data from model
		$rows = $this->get('Data');
		$pagination = $this->get('Pagination');
		$customs = $this->get('ListCustomFields');
		$customsfilters = $this->get('CustomFilters');

		// Title
		$pagetitle = $this->getTitle();
		$this->document->setTitle($pagetitle);

		if ($pop)
		{
			// If printpopup set true
			$params->set('popup', 1);
			$this->setLayout('print');
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=' . $this->getName() . '&tmpl=component&pop=1');

		// Create select lists
		$this->buildSortLists();

		// Action for filter form
		$this->prepareAction();

		$this->assignRef('rows', $rows);
		$this->assignRef('customs', $customs);
		$this->assignRef('customsfilters', $customsfilters);
		$this->assignRef('task', $task);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('params', $params);
		$this->assignRef('pageNav', $pagination);
		$this->assignRef('pagetitle', $pagetitle);
		$this->assignRef('config', $config);
		$this->assign('state', $this->get('state'));

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns', $cols);

		parent::display($tpl);
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
		$uri->delVar('filter_category');
		$uri->delVar('filter_venuecategory');
		$uri->delVar('filter_venue');
		$uri->delVar('filter_event');
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
		return false;
	}

	/**
	 * Add feed links
	 *
	 * @return void
	 */
	protected function addFeedLinks()
	{
		if (!$link = $this->getFeedLink())
		{
			return;
		}

		$document = JFactory::getDocument();

		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
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

		$lists['filter'] = $filter;

		$this->lists = $lists;

		$this->order = $state->get('filter_order');
		$this->orderDir = $state->get('filter_order_Dir');
	}
}
