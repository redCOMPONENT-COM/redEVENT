<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent categories csv Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelCategoriescsv extends RModelAdmin
{
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

		$query->select('c.id, c.name, c.alias, c.description')
			->select('c.color, c.image, c.published, c.access')
			->select('c.event_template, c.ordering, c.meta_description, c.meta_keywords')
			->from('#__redevent_categories AS c')
			->group('c.id');

		if ($parent = $this->getState('parent'))
		{
			$query->join('INNER', '#__redevent_categories AS parent ON c.lft BETWEEN parent.lft AND parent.rgt');
			$query->where("parent.id = " . (int) $parent);
		}

		$this->_db->setQuery($query);
		$results = $this->_db->loadAssocList();

		return $results;
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

		if (isset($filters['parent']))
		{
			$this->setState('parent', (int) $filters['parent']);
		}
	}
}
