<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Editvenue Model
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelVenue extends RModel
{
	/**
	 * Venue data in Venue array
	 *
	 * @var array
	 */
	protected $venue = null;

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
		$this->_id = $id;
	}

	/**
	 * Logic to get the venue
	 *
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->venue))
		{
			if ($this->_id)
			{
				$query = $this->_db->getQuery(true)
					->select('v.id, v.venue, v.url, v.street, v.plz, v.city, v.state, v.country')
					->select('v.locdescription, v.locimage, v.latitude, v.longitude, v.company')
					->select('COUNT( a.id ) AS assignedevents')
					->select('CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug')
					->from('#__redevent_venues as v')
					->join('LEFT', '#__redevent_event_venue_xref AS a ON a.venueid = v.id AND a.published = 1')
					->where('v.id = ' . $this->_db->Quote($this->_id))
					->group('v.id');

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

				$this->venue = $venue;
			}
		}

		return $this->venue;
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
