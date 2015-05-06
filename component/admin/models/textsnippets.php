<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component textsnippets Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelTextsnippets extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_textsnippets';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'textsnippets_limit';

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
				'text_name', 'obj.text_name',
				'id', 'c.id',
				'language', 'obj.language'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string       A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id	.= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'obj.*'));
		$query->from($db->qn('#__redevent_textlibrary', 'obj'));


		// Filter by language
		$language = $this->getState('filter.language');

		if ($language && $language != '*')
		{
			$query->where($db->qn('obj.language') . ' = ' . $db->quote($language));
		}

		// Filter: like / search
		$search = $this->getState('filter.search', '');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('obj.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(obj.text_name LIKE ' . $search . ' OR obj.text_description LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'obj.text_name')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * import in database
	 *
	 * @param   array  $record   record to import
	 * @param   bool   $replace  existing events with same id
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function import($record, $replace = false)
	{
		$count = array('added' => 0, 'updated' => 0);

		$v = $this->getTable('Textsnippet', 'RedeventTable');
		$v->bind($record);

		if ((!$replace) || (!$v->id))
		{
			$v->id = null;
			$count['added']++;
		}
		elseif ($v->id)
		{
			$count['updated']++;
		}

		// Store !
		if (!$v->check())
		{
			throw new Exception(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError());
		}

		if (!$v->store())
		{
			throw new Exception(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError());
		}

		return $count;
	}
}
