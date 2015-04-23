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

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the EventList View
 *
 * @package    Joomla
 *
 * @subpackage redEVENT
 *
 * @since      0.9
 */
class RedeventViewSimpleList extends RViewSite
{
	/**
	 * Rows data
	 *
	 * @var  array
	 */
	public $rows;

	public $minutePixel = 10;

	/**
	 * Creates the Simple List View
	 *
	 * @since 0.9
	 *
	 */
	function display($tpl = null)
	{
		// Initialize variables
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$document   = JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$menu       = JSite::getMenu();
		$item       = $menu->getActive();
		$params     = $app->getParams();
		$uri        = JFactory::getURI();
		$pathway    = $app->getPathWay();
		$state      = $this->get('state');
		$model      = $this->getModel();

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// Add js
		JHTML::_('behavior.framework');

		// For filter hint
		$document->addScript($this->baseurl . '/components/com_redevent/assets/js/eventslist.js');

		// Get variables
		$task   = $input->getWord('task', '');
		$pop    = $input->getBool('pop', false);
		$layout = $input->getWord('layout', '');

		// Get data from model
		$this->rows     = $this->get('Data');
		$customs        = $this->get('ListCustomFields');
		$customsfilters = $this->get('CustomFilters');
		$pagination     = $this->get('Pagination');
		$this->noevents = 1;

		// Are events available?
		if (!empty($this->rows))
		{
			$this->noevents = 0;
		}

		// Params
		$params->def('page_title', (isset($item->title) ? $item->title : JText::_('COM_REDEVENT_Events')));

		if ($pop)
		{
			// If printpopup set true
			$params->set('popup', 1);
			$this->setLayout('print');
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=simplelist&tmpl=component&pop=1');
		$pagetitle = $params->get('page_title');

		$thumb_link = RedeventHelperRoute::getSimpleListRoute(null, 'thumb');
		$list_link  = RedeventHelperRoute::getSimpleListRoute(null, 'default');

		// Set Page title
		$this->document->setTitle($pagetitle);

		// Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		// Add alternate feed link
		$link = 'index.php?option=com_redevent&view=simplelist&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

		// Create select lists
		$lists = $this->buildSortLists();

		$filter_customs = $state->get('filter_customs');

		$this->assign('lists', $lists);

		$this->assignRef('customs', $customs);
		$this->assignRef('customsfilters', $customsfilters);
		$this->assignRef('task', $task);
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

		$this->order = $state->get('filter_order');
		$this->orderDir = $state->get('filter_order_Dir');

		if ($layout == 'timeline')
		{
			$this->rows = $this->timelineProcessData($this->rows);
		}

		parent::display($tpl);
	}

	/**
	 * Method to build the sortlists
	 *
	 * @access private
	 *
	 * @return array
	 *
	 * @since  0.9
	 */
	private function buildSortLists()
	{
		$app = JFactory::getApplication();
		$uri = JFactory::getURI();
		$state = $this->get('state');

		// Remove previously set filter in get
		$uri->delVar('filter');
		$uri->delVar('filter_type');
		$uri->delVar('filter_date');
		$uri->delVar('filter_category');
		$uri->delVar('filter_venuecategory');
		$uri->delVar('filter_venue');
		$uri->delVar('filter_event');
		$uri->delVar('filtercustom');

		$params = RedeventHelper::config();

		$filter_order = $state->get('filter_order');
		$filter_order_Dir = $state->get('filter_order_Dir');

		$state = $this->get('state');

		$filter          = $state->get('filter');
		$filter_type     = $state->get('filter_type');
		$filter_category = $state->get('filter_category');
		$filter_date     = $state->get('filter_date');
		$filter_venue    = $state->get('filter_venue');
		$filter_event    = $state->get('filter_event');

		$this->assign('action', JRoute::_('index.php?option=com_redevent&view=simplelist'));

		$sortselects = array();

		if ($params->get('filter_type_event', 1))
		{
			$sortselects[] = JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT'));
		}

		if ($params->get('filter_type_venue', 1))
		{
			$sortselects[] = JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE'));
		}

		if ($params->get('filter_type_city', 1))
		{
			$sortselects[] = JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY'));
		}

		if ($params->get('filter_type_category', 1))
		{
			$sortselects[] = JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY'));
		}

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

		// Date filter
		if ($params->get('lists_filter_date', 0))
		{
			$lists['dateFilter'] = JHTML::_(
				'calendar',
				$filter_date,
				'filter_date',
				'filter_date',
				'%Y-%m-%d',
				' class="dynfilter" onchange="javascript:redEventSubmitForm();"'
			);
		}

		$lists['filter']      = $filter;
		$lists['filter_type'] = $sortselect;

		return $lists;
	}
}
