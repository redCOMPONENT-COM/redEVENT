<?php
/**
 * @package    RedEVENT
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class RedeventModelAjaxeventssuggest extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'q',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = 'e.title', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// List state information
		$this->setState('list.limit', 20);
		$this->setState('list.start', 0);
		$this->setState('list.ordering', $ordering);
		$this->setState('list.direction', 'ASC');
		$this->setState('filter.language', $app->getLanguageFilter());

		if ($app->input->get('q'))
		{
			$this->setState('filter.title', $app->input->get('q'));
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.title');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of articles subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('e.title ');
		$query->from('#__redevent_events AS e');
		$query->where('e.published = 1');

		if ($this->getState('filter.title'))
		{
			$query->where('e.title  LIKE "%' . $this->getState('filter.title') . '%"');
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(e.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR e.language IS NULL)');
		}

		$category = $this->getState('filter.category');

		if (is_numeric($category))
		{
			$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id');
			$query->where('xcat.category_id = ' . $category);
		}

		$venue = $this->getState('filter.venue');

		if (is_numeric($venue))
		{
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
			$query->where('x.venueid = ' . $venue);
		}

		$query->group('e.id');

		$query->order($this->getState('list.ordering') . ' ' . $this->getState('list.direction'));

		return $query;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadColumn();

		return $result;
	}
}
