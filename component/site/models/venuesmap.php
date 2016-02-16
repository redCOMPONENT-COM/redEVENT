<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEvent Component Venuesmap Model
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelVenuesmap extends RModel
{
	/**
	 * Venues data array
	 *
	 * @var array
	 */
	protected $data = null;

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
		$params = $app->getParams('com_redevent');

		$this->setState('vcat', $app->input->get('filter_venuecategory', $params->def('vcat', 0), 'string'));
		$this->setState('cat', $app->input->get('filter_category', $params->def('cat', 0), 'string'));

		$this->setState('filter.language', $app->getLanguageFilter());
	}

	/**
	 * Method to get the Venues
	 *
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$query = $this->_buildQuery();

			// Get a reference to the global cache object.
			$cache = JFactory::getCache('redevent');

			$this->data = $cache->call(array($this, '_getResultList'), $query);

			$k = 0;

			for ($i = 0; $i < count($this->data); $i++)
			{
				$venue =& $this->data[$i];

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

				$k = 1 - $k;
			}
		}

		return $this->data;
	}

	/**
	 * Build the query
	 *
	 * @return string
	 */
	protected function _buildQuery()
	{
		$app = JFactory::getApplication();
		$vcat = $this->getState('vcat');
		$cat = $this->getState('cat');
		$customs = $app->getUserState('com_redevent.venuesmap.filter_customs');

		$params = $app->getParams();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.*, COUNT(x.id) AS assignedevents');
		$query->select('CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug');
		$query->from('#__redevent_venues as v');
		$query->join('LEFT', '#__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = v.id');
		$query->join('LEFT', '#__redevent_venues_categories AS vcat ON vcat.id = xvcat.category_id');
		$query->where('v.published = 1');
		$query->where('(v.access IN (' . $gids . '))');
		$query->where('(vcat.id IS NULL OR vcat.access IN (' . $gids . '))');

		if ($params->get('show_empty_venues', 0))
		{
			$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.venueid = v.id AND x.published = 1');
			$query->join('LEFT', '#__redevent_events AS e ON x.eventid = e.id');
		}
		else
		{
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.venueid = v.id AND x.published = 1');
			$query->join('INNER', '#__redevent_events AS e ON x.eventid = e.id');
		}

		if ($cat)
		{
			$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = x.eventid');
			$query->join('INNER', '#__redevent_categories AS cat ON cat.id = xcat.category_id');
			$query->join('INNER', '#__redevent_categories AS topcat ON cat.lft BETWEEN topcat.lft AND topcat.rgt');
			$query->where('topcat.id = ' . $this->_db->Quote($cat));
		}

		if ($vcat)
		{
			$query->join('INNER', '#__redevent_venues_categories AS top ON vcat.lft BETWEEN top.lft AND top.rgt');
			$query->where('top.id = ' . $this->_db->Quote($vcat));
		}

		foreach ((array) $customs as $key => $custom)
		{
			if ($custom != '')
			{
				if (is_array($custom))
				{
					$custom = implode("/n", $custom);
				}

				$query->where('custom' . $key . ' LIKE ' . $this->_db->Quote('%' . $custom . '%'));
			}
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(v.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->_db->quote('*') . ') OR v.language IS NULL)');
		}

		$query->group('v.id');
		$query->order('v.venue');

		return $query;
	}

	/**
	 * used by the caching function
	 *
	 * @param   string  $query  query
	 *
	 * @return array
	 */
	public static function _getResultList($query)
	{
		$db = JFactory::getDBO();

		$db->setQuery($query);

		return ($db->loadObjectList());
	}

	/**
	 * get venues countries
	 *
	 * @return array
	 */
	public function getCountries()
	{
		$venues = $this->getData();
		$countries = array();

		foreach ((array) $venues AS $v)
		{
			if (RedeventHelperCountries::isValid($v->country))
			{
				$countries[] = $v->country;
			}
		}

		if (!count($countries))
		{
			return array();
		}

		$countries = array_unique($countries);

		$res = array();

		foreach ($countries as $c)
		{
			$country = new stdclass;
			$country->name = RedeventHelperCountries::getCountryName($c);
			$country->flag = RedeventHelperCountries::getCountryFlag($c);
			$country->flagurl = RedeventHelperCountries::getIsoFlag($c);
			$country->latitude = RedeventHelperCountries::getLatitude($c);
			$country->longitude = RedeventHelperCountries::getLongitude($c);
			$res[] = $country;
		}

		return $res;
	}

	/**
	 * Custom filters
	 *
	 * @return array
	 */
	public function getCustomFilters()
	{
		$query = ' SELECT f.* FROM #__redevent_fields AS f '
			. ' WHERE f.published = 1 AND f.searchable = 1 AND f.object_key = ' . $this->_db->Quote("redevent.event")
			. ' ORDER BY f.ordering ASC ';
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		$filters = array();

		foreach ($rows as $r)
		{
			$field = RedeventFactoryCustomfield::getField($r->type);
			$field->bind($r);
			$filters[] = $field;
		}

		return $filters;
	}
}
