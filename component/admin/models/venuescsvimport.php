<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent venues csv import Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelVenuescsvimport extends RModel
{
	/**
	 * @var string
	 */
	private $duplicateMethod;

	/**
	 * @var array
	 */
	private $categories;

	/**
	 * @var int
	 */
	private $updated = 0;

	/**
	 * @var int
	 */
	private $ignored = 0;

	/**
	 * @var int
	 */
	private $added = 0;

	/**
	 * insert events/sessions in database
	 *
	 * @param   array   $records           array of records to import
	 * @param   string  $duplicate_method  method for handling duplicate record (ignore, create_new, update)
	 *
	 * @return boolean true on success
	 */
	public function import($records, $duplicate_method = 'ignore')
	{
		$this->duplicateMethod = $duplicate_method;

		foreach ($records as $r)
		{
			$this->storeVenue($r);
		}

		$count = array('added' => $this->added, 'updated' => $this->updated, 'ignored' => $this->ignored);

		return $count;
	}

	/**
	 * Store venue
	 *
	 * @param   array  $data  venue data from import
	 *
	 * @return integer|bool
	 */
	private function storeVenue($data)
	{
		$app = JFactory::getApplication();

		if (!empty($data['venue']))
		{
			$v = RTable::getAdminInstance('Venue');

			if ($this->duplicateMethod !== 'create_new' && isset($data['id']) && $data['id'])
			{
				// Load existing data
				$found = $v->load($data['id']);

				// Discard if set to ignore duplicate
				if ($found && $this->duplicateMethod == 'ignore')
				{
					$this->ignored++;

					return true;
				}
			}
			else
			{
				$found = false;
			}

			// Categories relations
			$cats = explode('#!#', $data['categories_names']);
			$cats_ids = array();

			foreach ($cats as $c)
			{
				$cats_ids[] = $this->getCategoryId($c);
			}

			$data['categories'] = $cats_ids;

			// Bind submitted data
			$v->bind($data);

			if ($this->duplicateMethod == 'update' && $found)
			{
				$updating = 1;
			}
			else
			{
				// Create new
				$v->id = null;
				$updating = 0;
			}

			// Store !
			if (!$v->check())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError(), 'error');

				return false;
			}

			if (!$v->store())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError(), 'error');

				return false;
			}

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterVenueSaved', array($v->id));

			($updating ? $this->updated++ : $this->added++);

			return $v->id;
		}

		return false;
	}

	/**
	 * Return category id matching name, creating if needed
	 *
	 * @param   string  $name  category name
	 *
	 * @return id cat id
	 */
	private function getCategoryId($name)
	{
		$id = array_search($name, $this->getCategories());

		if ($id === false)
		{
			// Doesn't exist, create it
			$new = RTable::getAdminInstance('Venuescategory');
			$new->name = $name;
			$new->published = 1;
			$new->store();
			$id = $new->id;
			$this->categories[$id] = $name;
		}

		return $id;
	}

	/**
	 * returns array of current categories names indexed by ids
	 *
	 * @return array
	 */
	private function getCategories()
	{
		if (empty($this->categories))
		{
			$this->categories = array();

			$query = $this->_db->getQuery(true);

			$query->select('id, name')
				->from('#__redevent_venues_categories');

			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			foreach ((array) $res as $r)
			{
				$this->categories[$r->id] = $r->name;
			}
		}

		return $this->categories;
	}
}
