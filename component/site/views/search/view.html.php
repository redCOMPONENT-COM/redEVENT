<?php
/**
 * @version 1.0 $Id: view.html.php 736 2009-08-31 09:51:56Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the redEVENT search View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
*/
class RedeventViewSearch extends JView
{
	/**
	 * Creates the search View
	 *
	 * @since 0.9
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		//initialize variables
		$document 	= JFactory::getDocument();
		$config = redEVENTHelper::config();
		$menu		= JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= $mainframe->getParams();
		$uri 		= JFactory::getURI();
		$pathway 	= $mainframe->getPathWay();

		//add css file
		$document->addStyleSheet('media/com_redevent/css/redevent.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// add javascript
		JHTML::_('behavior.mootools');
		$document->addScript( $this->baseurl.'/components/com_redevent/assets/js/search.js' );

		// get variables
		$task 		= JRequest::getWord('task');
		$pop		= JRequest::getBool('pop');

		//get data from model
		$rows 	= $this->get('Data');
		$customs 	= $this->get('ListCustomFields');
		$customsfilters 	= $this->get('CustomFilters');
		$total 	= $this->get('Total');
		// Create the pagination object
		$pageNav = $this->get('Pagination');

		$limitstart	      = $pageNav->limitstart;
		$limit		        = $pageNav->limit;

		$state = $this->get( 'state' );

		// set in the model
		$filter_country   = $state->get('filter_country');
		$filter_city      = $state->get('filter_city');
		$filter_state     = $state->get('filter_state');
		$filter_venue     = $state->get('filter_venue');
		$filter_date_from = $state->get('filter_date_from');
		$filter_date_to   = $state->get('filter_date_to');
		$filter_venuecategory = $state->get('filter_venuecategory');
		$filter_category  = $mainframe->getUserStateFromRequest('com_redevent.search.filter_category',      'filter_category',      $params->get('category', 0), 'int');
		$filter_event     = $state->get('filter_event');
		$filter_customs   = $state->get('filter_customs');

		//are events available?
		if (!$rows)
		{
			$noevents = 1;
			$filter = $this->get('Filter');

			if (!$filter)
			{
				$nofilter = 1;
			}
		}
		else
		{
			$noevents = 0;
			$nofilter = 0;
		}

		$this->checkDirectRedirect($rows);

		//params
		$params->def( 'page_title', $item->title);

		if ($pop)
		{
			//If printpopup set true
			$params->set( 'popup', 1 );
			$this->setLayout('print');
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=search&tmpl=component&pop=1');
		$pagetitle = $params->get('page_title');

		//Set Page title
		$this->document->setTitle($pagetitle);

		//create select lists
		$lists	= $this->_buildSortLists();

		if ($params->get('category', 0) == 0) // do not display the filter if set in view params
		{
			$catoptions = array();
			$catoptions[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_category'));
			$catoptions = array_merge($catoptions, $this->get('CategoriesOptions'));
			$selectedcats = ($filter_category) ? array($filter_category) : array();
			//build select
			$lists['categories'] =  JHTML::_('select.genericlist', $catoptions, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', $selectedcats);
			unset($catoptions);
		}

		$vcatoptions = array();
		$vcatoptions[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_venue_category'));
		$vcatoptions = array_merge($vcatoptions, redEVENTHelper::getVenuesCatOptions());
		$selectedcats = ($filter_venuecategory) ? array($filter_venuecategory) : array();
		//build select
		$lists['vcategories'] =  JHTML::_('select.genericlist', $vcatoptions, 'filter_venuecategory', 'size="1" class="inputbox dynfilter"', 'value', 'text', $selectedcats);
		unset($catoptions);

		// country filter
		$countries = array();
		$countries[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_country'));
		$countries = array_merge($countries, $this->get('CountryOptions'));
		$lists['countries'] = JHTML::_('select.genericlist', $countries, 'filter_country', 'class="inputbox"', 'value', 'text', $filter_country);
		unset($countries);

		// state filter
		$states = array();
		$states[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_state'));
		$states = array_merge($states, $this->get('StateOptions'));
		$lists['states'] = JHTML::_('select.genericlist', $states, 'filter_state', 'class="inputbox"', 'value', 'text', $filter_state);
		unset($states);

		// city filter
		$cities = array();
		$cities[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_city'));
		$cities = array_merge($cities, $this->get('CityOptions'));
		$lists['cities'] = JHTML::_('select.genericlist', $cities, 'filter_city', 'class="inputbox"', 'value', 'text', $filter_city);
		unset($cities);

		// venues filter
		$venues = array();
		$venues[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Select_venue'));
		$venues = array_merge($venues, $this->get('VenuesOptions'));
		$lists['venues'] = JHTML::_('select.genericlist', $venues, 'filter_venue', 'class="inputbox dynfilter"', 'value', 'text', $filter_venue);
		unset($venues);

		// events filter
		$options = array();
		$options[] = JHTML::_('select.option', '0', JText::_('COM_REDEVENT_Search_select_event'));
		$options = array_merge($options, $this->get('EventsOptions'));
		$lists['events'] = JHTML::_('select.genericlist', $options, 'filter_event', 'class="inputbox dynfilter"', 'value', 'text', $filter_event);
		unset($venues);

		// remove previously set filter in get
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

		$this->assign('lists' , 					$lists);
		$this->assign('total',						$total);
		$this->assign('action', 					JRoute::_(RedeventHelperRoute::getSearchRoute()));

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('customs',         $customs);
		$this->assignRef('customsfilters',  $customsfilters);
		$this->assignRef('task' , 					$task);
		$this->assignRef('noevents' , 				$noevents);
		$this->assignRef('nofilter' , 				$nofilter);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('elsettings' , 			$config);
		$this->assignRef('pagetitle' , 				$pagetitle);
		$this->assign('filter_country',        $filter_country);
		$this->assign('filter_state',        $filter_state);
		$this->assign('filter_date_from',    $filter_date_from);
		$this->assign('filter_date_to',      $filter_date_to);
		$this->assign('filter_customs',      $filter_customs);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = redEVENTHelper::validateColumns($cols);
		$this->assign('columns',        $cols);

		if ($state->get('results_type') == 0)
		{
			$this->setLayout('searchevents');
			$allowed = array(
					'title',
					'venue',
					'category',
					'picture',
			);
			$cols = redEVENTHelper::validateColumns($cols, $allowed);
		}

		$this->assign('columns',        $cols);

		parent::display($tpl);
	}

	/**
	 * Method to build the sortlists
	 *
	 * @access private
	 * @return array
	 * @since 0.9
	 */
	protected function _buildSortLists()
	{
		$filter_order		= JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'ASC');

		$filter				= JRequest::getString('filter');
		$filter_type		= JRequest::getString('filter_type');

		$sortselects = array();
		$sortselects[]	= JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT') );
		$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE') );
		$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY') );
		$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY') );
		$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_types'] 	= $sortselect;

		return $lists;
	}

	/**
	 * Potentially redirect to details if only one result
	 *
	 * @param   array  $rows  result rows
	 *
	 * @return void
	 */
	protected function checkDirectRedirect($rows)
	{
		$config = redEVENTHelper::config();

		if (count($rows) == 1 && $config->get('redirect_search_unique_result_to_details', 0))
		{
			if ($this->get('state')->get('results_type') == 0)
			{
				$route = RedeventHelperRoute::getDetailsRoute($rows[0]->slug);
			}
			else
			{
				$route = RedeventHelperRoute::getDetailsRoute($rows[0]->slug, $rows[0]->xslug);
			}

			JFactory::getApplication()->redirect($route);
		}
	}
}
