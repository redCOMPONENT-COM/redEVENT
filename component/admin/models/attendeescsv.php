<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent attendees csv Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelAttendeescsv extends RModelAdmin
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

	protected function populateState()
	{
		$filters = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (isset($filters['parent']))
		{
			$this->setState('parent', (int) $filters['parent']);
		}
	}
}
