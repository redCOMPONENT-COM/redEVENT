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
 * EventList Component Editvenue Model
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelVenue extends RModel
{
	/**
	 * Venue data in Venue array
	 *
	 * @var array
	 */
	var $_venue = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		$app = JFactory::getApplication();
		parent::__construct();

		$this->setState('filter.language', $app->getLanguageFilter());

		$id = JRequest::getInt('id');
		$this->setId($id);
	}

	/**
	 * Method to set the Venue id
	 *
	 * @param   int  $id  venue id
	 *
	 * @return void
	 */
	public function setId($id)
	{
		// Set new venue ID
		$this->_id			= $id;
	}

	/**
	 * Logic to get the venue
	 *
	 * @return array
	 */
	public function &getData(  )
	{
		$mainframe = JFactory::getApplication();

		if (empty($this->_venue))
		{
			if ($this->_id)
			{
				// Load the Event data
				$query = ' SELECT v.id, v.venue, v.url, v.street, v.plz, v.city, v.state, v.country, v.locdescription, v.locimage, v.latitude, v.longitude, v.company, '
				. ' COUNT( a.id ) AS assignedevents,'
				. ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug'
				. ' FROM #__redevent_venues as v'
				. ' LEFT JOIN #__redevent_event_venue_xref AS a ON a.venueid = v.id AND a.published = 1'
				. ' WHERE v.id = ' . $this->_db->Quote($this->_id)
				. ' GROUP BY v.id ';
				$this->_db->setQuery($query);

				$venue = $this->_db->loadObject();

				// Create image information
				$venue->limage = RedeventImage::flyercreator($venue->locimage);

				// Generate Venuedescription
				if (!empty ($venue->locdescription))
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
				$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug));

				$venue->categories = $this->_getVenueCategories($this->_id);

				$this->_venue = $venue;
			}
		}

		return $this->_venue;
	}

	/**
	 * logic to get the venue
	 *
	 * @access private
	 * @return array
	 */
	protected function _loadVenue( )
	{
		if (empty($this->_venue))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*');
			$query->from('#__redevent_venues');
			$query->where('id = ' . $this->_id);
			$db->setQuery($query);
			$this->_venue = $db->loadObject();

			return $this->_venue;
		}

		return $this->_venue;
	}

	/**
	 * adds categories property to event row
	 *
	 * @param   int  $venueid  venue id
	 *
	 * @return object
	 */
	protected function _getVenueCategories($venueid)
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.id, c.name, c.access');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_venues_categories as c');
		$query->join('INNER', '#__redevent_venue_category_xref as x ON x.category_id = c.id');
		$query->where('c.published = 1');
		$query->where('x.venue_id = ' . $this->_db->Quote($venueid));
		$query->order('c.lft');

		if ($this->getState('filter.language'))
		{
			$query->where('(c.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR c.language IS NULL)');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
