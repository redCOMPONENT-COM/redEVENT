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
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.model');

/**
 * redevent Component calendar Model
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       0.9
 */
class RedeventModelCalendar extends RModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Tree categories data array
	 *
	 * @var array
	 */
	protected $_categories = null;

	protected $_topcat = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * The reference date
	 *
	 * @var int unix timestamp
	 */
	protected $_date = 0;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$app = & JFactory::getApplication();

		$this->setState('filter.language', $app->getLanguageFilter());

		$this->setdate(time());
	}

	/**
	 * set calendar reference date
	 *
	 * @param   string  $date  fgfgf
	 *
	 * @return  void
	 */
	public function setdate($date)
	{
		$this->_date = $date;
	}

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	public function &getData()
	{
		// Lets load the content if it doesn't already exist
		if ( empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query);

			// We have the events happening this month. We have to create occurences for each day for multiple day events.
			$multi = array();

			foreach ($this->_data AS $item)
			{
				$item->categories = $this->getCategories($item->id);

				if (!is_null($item->enddates))
				{
					if ($item->enddates != $item->dates)
					{
						$day = $item->start_day;

						for ($counter = 0; $counter <= $item->datediff - 1; $counter++)
						{
							$day++;

							// Next day:
							$nextday = mktime(0, 0, 0, $item->start_month, $day, $item->start_year);

							// Ensure we only generate days of current month in this loop
							if (strftime('%m', $this->_date) == strftime('%m', $nextday))
							{
								$multi[$counter] = clone $item;
								$multi[$counter]->dates = strftime('%Y-%m-%d', $nextday);

								// Add generated days to data
								$this->_data = array_merge($this->_data, $multi);
							}

							// Unset temp array holding generated days before working on the next multiday event
							unset($multi);
						}
					}
				}

				// Remove events without categories (users have no access to them)
				if (empty($item->categories))
				{
					unset($item);
					continue;
				}

				// Remove event with a start date from previous months
				if ( strftime('%m', strtotime($item->dates)) != strftime('%m', $this->_date) )
				{
					array_shift($this->_data);
				}
			}
		}

		return $this->_data;
	}

	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQuery()
	{
		// Get Events from Database
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DATEDIFF(x.enddates, x.dates) AS datediff, a.id, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes');
		$query->select('a.title, x.venueid as locid, a.datdescription, a.created, l.venue, l.city, l.state, l.url, l.street, l.country, x.featured');
		$query->select('a.datimage');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('DAYOFMONTH(x.dates) AS start_day, YEAR(x.dates) AS start_year, MONTH(x.dates) AS start_month');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->from('#__redevent_events AS a');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = a.id');
		$query->join('INNER', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id');
		$query->join('LEFT', '#__redevent_venues_categories AS vc ON xvcat.category_id = vc.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('INNER', '#__redevent_categories AS cat ON cat.id = xcat.category_id');
		$query->group('x.id');
		$query->order('x.dates, x.times');

		if ($this->getState('filter.language'))
		{
			$query->where('(a.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR a.language IS NULL)');
			$query->where('(cat.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR cat.language IS NULL)');
		}

		// Get the WHERE clauses for the query
		$query = $this->_buildWhere($query);

		return $query;
	}

	/**
	 * Method to build the WHERE clause
	 *
	 * @param   object  $query  the query object
	 *
	 * @return  array
	 *
	 * @access  private
	 */
	protected function _buildWhere($query)
	{
		$app = & JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = & $app->getParams();

		$task = JRequest::getWord('task');

		// First thing we need to do is to select only the published events
		if ($task == 'archive')
		{
			$query->where(' x.published = -1 ');
		}
		else
		{
			$query->where(' x.published = 1 ');
		}
        $query->where(' a.published <> 0 ');

		// Category must be published too
		$query->where(' cat.published = 1 ');

		// Only select events within specified dates. (chosen month)
		$monthstart = mktime(0, 0, 1, strftime('%m', $this->_date), 1, strftime('%Y', $this->_date));
		$monthend = mktime(0, 0, -1, strftime('%m', $this->_date) + 1, 1, strftime('%Y', $this->_date));

		$query->where(' ((x.dates BETWEEN (\'' . strftime('%Y-%m-%d', $monthstart) . '\') AND (\'' . strftime('%Y-%m-%d', $monthend) . '\'))'
				. ' OR (x.enddates BETWEEN (\'' . strftime('%Y-%m-%d', $monthstart) . '\') AND (\'' . strftime('%Y-%m-%d', $monthend) . '\')))');

		// Check if a category is specified
		$topcat = $params->get('topcat', '');

		if (is_numeric($topcat) && $topcat)
		{
			// Get children categories
			$db = &JFactory::getDbo();
			$query_top = $db->getQuery(true);

			$query_top->select('lft, rgt');
			$query_top->from('#__redevent_categories');
			$query_top->join('INNER', '#__ AS ON = ');
			$query_top->where('id = ' . $this->_db->Quote($topcat));
			$db->setQuery($query_top);
			$obj = $this->_db->loadObject();

			if ($obj)
			{
				$query_ch = $db->getQuery(true);

				$query_ch->select('id');
				$query_ch->from('#__redevent_categories ');
				$query_ch->join('INNER', '#__ AS ON = ');
				$query_ch->where('lft >= ' . $this->_db->Quote($obj->lft));
				$query_ch->where('rgt <= ' . $this->_db->Quote($obj->rgt));
				$db->setQuery($query_ch);
				$cats = $db->loadColumn();

				if ($cats)
				{
					$query->where(' xcat.category_id IN (' . implode(', ', $cats) . ')');
				}
			}
			else
			{
				JError::raiseWarning(0, JText::_('COM_REDEVENT_CATEGORY_NOT_FOUND'));
			}
		}

		// Acl
		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$query->where(' (l.access IN (' . $gids . ')) ');
		$query->where(' (cat.access IN (' . $gids . ')) ');
		$query->where(' (vc.access IN (' . $gids . ') OR vc.id IS NULL) ');

		return $query;
	}

	/**
	 * Method to get the Categories
	 *
	 * @param   int  $id  top category id
	 *
	 * @access public
	 *
	 * @return integer
	 */
	protected function getCategories($id)
	{
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.id, c.name AS catname, c.color');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_categories as c');
		$query->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id');
		$query->where('c.published = 1');
		$query->where('x.event_id = ' . $this->_db->Quote((int) $id));
		$query->order('c.ordering');
		$db->setQuery($query);

		$this->_categories = $db->loadObjectList();

		return $this->_categories;
	}
}
