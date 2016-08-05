<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component events templates Model
 *
 * @package  Redevent.admin
 * @since    3.1
 */
class RedeventModelEventtemplates extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_eventtemplates';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'eventtemplates_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configs
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'obj.id', 'obj.name', 'obj.language',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Merge templates
	 *
	 * @param   array  $cid     template ids
	 * @param   int    $target  target template
	 *
	 * @return void
	 */
	public function mergeTemplates($cid, $target)
	{
		// Just in case, remove target from remove list...
		$remove = array_diff($cid, array($target));

		// First, reassign $target to associated events
		$query = $this->_db->getQuery(true)
			->update('#__redevent_events')
			->set('template_id = ' . $target)
			->where('template_id IN (' . implode(",", $remove) . ')');

		$this->_db->setQuery($query);
		$this->_db->execute();

		// Then delete them
		$query = $this->_db->getQuery(true)
			->delete('#__redevent_event_template')
			->where('id IN (' . implode(",", $remove) . ')');

		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('obj.*');
		$query->from('#__redevent_event_template AS obj ');
		$query->group('obj.id');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = obj.language');

		// Get the WHERE and ORDER BY clauses for the query
		$filter_language = $this->getState('filter.language');

		if ($filter_language)
		{
			$query->where('obj.language = ' . $this->_db->quote($filter_language));
		}

		$search = $this->getState('filter.search');

		if ($search)
		{
			$like = $this->_db->quote('%' . $search . '%');

			$parts = array();
			$parts[] = 'LOWER(obj.name) LIKE ' . $like;

			$query->where(implode(' OR ', $parts));
		}

		$order = $this->getState('list.ordering', 'obj.name');
		$dir = $this->getState('list.direction', 'asc');
		$query->order($db->qn($order) . ' ' . $dir);

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   Ordering column
	 * @param   string  $direction  Direction
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Forcing default values
		parent::populateState($ordering ?: 'obj.name', $direction ?: 'asc');
	}
}
