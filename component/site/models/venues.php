<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Redevent Model Venues
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
*/
class RedeventModelVenues extends RModel
{
	/**
	 * limit venues to a certain category
	 * @var object
	 */
	protected $_category;

	/**
	 * Venues data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Venues total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= $app->getParams('com_redevent');

		if ($params->get('categoryid', 0))
		{
			$this->setCategory($params->get('categoryid', 0));
		}

		// Get the number of events from database
		$limit			= JRequest::getInt('limit', $params->get('display_venues_num'));
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$this->setState('filter.language', $app->getLanguageFilter());
	}

	/**
	 * set the category
	 *
	 * @param   int  $id  id
	 *
	 * @return boolean
	 */
	public function setCategory($id)
	{
		$sub = ' SELECT id, lft, rgt FROM #__redevent_venues_categories WHERE id = ' . $this->_db->Quote((int) $id);
		$this->_db->setQuery($sub);
		$obj = $this->_db->loadObject();

		if (!$obj)
		{
			JError::raiseWarning(0, JText::_('COM_REDEVENT_VENUE_CATEGORY_NOT_FOUND'));
		}
		else
		{
			$this->_category = $obj;
			$this->_data = null;
		}

		return true;
	}

	/**
	 * Method to get the Venues
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			$k = 0;

			for ($i = 0; $i < count($this->_data); $i++)
			{
				$venue =& $this->_data[$i];

				// Create image information
				$venue->limage = RedeventImage::flyercreator($venue->locimage);

				// Generate Venuedescription
				if (!empty($venue->locdescription))
				{
					// Execute plugins
					$venue->locdescription = JHTML::_('content.prepare', $venue->locdescription);
				}

				// Build the url
				if (!empty($venue->url) && strtolower(substr($venue->url, 0, 7)) != "http://")
				{
					$venue->url = 'http://' . $venue->url;
				}

				// Prepare the url for output
				if (strlen(htmlspecialchars($venue->url, ENT_QUOTES)) > 35)
				{
					$venue->urlclean = substr(htmlspecialchars($venue->url, ENT_QUOTES), 0, 35) . '...';
				}
				else
				{
					$venue->urlclean = htmlspecialchars($venue->url, ENT_QUOTES);
				}

				// Create flag
				if ($venue->country)
				{
					$venue->countryimg = RedeventHelperCountries::getCountryFlag($venue->country);
				}

				// Create target link
				$task 	= JRequest::getVar('task', '', '', 'string');

				if ($task == 'archive')
				{
					$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug, 'archive'));
				}
				else
				{
					$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug));
				}

				$k = 1 - $k;
			}
		}

		return $this->_data;
	}

	/**
	 * Total nr of Venues
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQuery()
	{
		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params   = $mainframe->getParams('com_redevent');

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.*, v.id as venueid, COUNT( x.eventid ) AS assignedevents');
		$query->select('CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug');
		$query->from('#__redevent_venues as v');
		$query->join('LEFT', '#__redevent_event_venue_xref AS x ON v.id = x.venueid AND x.published = 1');
		$query->join('LEFT', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xc ON xc.venue_id = v.id');
		$query->join('LEFT', '#__redevent_venues_categories AS c ON c.id = xc.category_id');
		$query->where('v.published = 1');
		$query->where('(v.access IN (' . $gids . '))');
		$query->where('(c.id IS NULL OR c.access IN (' . $gids . '))');
		$query->group('v.id');
		$query->order('v.venue');

		if ($params->get('display_all_venues', 0) == 0)
		{
			$query->where('x.eventid IS NOT NULL ');
		}

		if ($this->_category)
		{
			$query->where('c.lft BETWEEN ' . $this->_db->Quote($this->_category->lft) . ' AND ' . $this->_db->Quote($this->_category->rgt));
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(v.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR v.language IS NULL)');
		}

		return $query;
	}
}
