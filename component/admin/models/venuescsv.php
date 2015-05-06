<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent venues csv Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelVenuescsv extends RModelAdmin
{
	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, false);

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		return false;
	}

	/**
	 * Get rows
	 *
	 * @return mixed
	 */
	public function getItems()
	{
		$query = $this->_db->getQuery(true);

		$query->select('v.id, v.venue, v.alias, v.url, v.street, v.plz, v.city, v.state, v.country, v.latitude, v.longitude')
			->select('v.locdescription, v.meta_description, v.meta_keywords, v.locimage, v.map, v.published')
			->select('u.name as creator_name, u.email AS creator_email')
			->from('#__redevent_venues AS v')
			->join('LEFT', '#__redevent_venue_category_xref AS xc ON xc.venue_id = v.id')
			->join('LEFT', '#__users AS u ON v.created_by = u.id')
			->group('v.id');

		if ($categories = $this->getState('categories'))
		{
			$query->where("xc.category_id IN  (" . implode(",", $categories) . ')');
		}

		$this->_db->setQuery($query);
		$results = $this->_db->loadAssocList();

		$cats = $this->getCategories();

		foreach ($results as $k => $r)
		{
			if (isset($cats[$r['id']]))
			{
				$results[$k]['categories_names'] = $cats[$r['id']]->categories_names;
			}
			else
			{
				$results[$k]['categories_names'] = null;
			}
		}

		return $results;
	}

	/**
	 * Get categories indexed by event id
	 *
	 * @return array
	 */
	private function getCategories()
	{
		$query = $this->_db->getQuery(true);

		$query->select('xc.venue_id, GROUP_CONCAT(c.name SEPARATOR "#!#") AS categories_names')
			->from('#__redevent_venue_category_xref AS xc')
			->join('LEFT', '#__redevent_venues_categories AS c ON c.id = xc.category_id')
			->group('xc.venue_id');
		$this->_db->setQuery($query);

		$cats = $this->_db->loadObjectList('venue_id');

		return $cats;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$filters = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (isset($filters['categories']) && is_array($filters['categories']))
		{
			$categories = $filters['categories'];
			JArrayHelper::toInteger($categories);
			$this->setState('categories', $categories);
		}
	}
}
