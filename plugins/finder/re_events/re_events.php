<?php
/**
 * @package     redEVENT
 * @subpackage  Finder.re_events
 *
 * @copyright   Copyright redEVENT (C) 2008-2012 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

/**
 * Finder adapter for redEVENT events.
 *
 * @package     redEVENT
 * @subpackage  Finder.re_events
 * @since       2.5
 */
class plgFinderRe_events extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Re_events';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_redevent';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'default';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Event';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table = '#__redevent_events';

	protected $state_field = 'published';

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   2.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Method to update the item link information when the item category is
	 * changed. This is fired when the item category is published or unpublished
	 * from the list view.
	 *
	 * @param   string   $extension  The extension whose category has been updated.
	 * @param   array    $pks        A list of primary key ids of the content that has changed state.
	 * @param   integer  $value      The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		// Make sure we're handling com_weblinks categories
		if ($extension == 'com_redevent.category')
		{
			// TODO: add a trigger when saving redevent categories ?
			$this->categoryStateChange($pks, $value);
		}
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_redevent.event')
		{
			$id = $table->id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}
		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row      A JTable object
	 * @param   boolean  $isNew    If the content has just been created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle web links here. We need to handle front end and back end editing.
		if ($context == 'com_redevent.event' || $context == 'com_redevent.event.form' )
		{
			// Reindex the item
			$this->reindex($row->id);
		}

		// Check for access changes in the category
		if ($context == 'com_redevent.category')
		{
			// Check if the access levels are different
			if (!$isNew && $this->old_cataccess != $row->access)
			{
				$this->categoryAccessChange($row);
			}
		}

		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string   $context  The context of the content passed to the plugin.
	 * @param   JTable   $row     A JTable object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle web links here
		if ($context == 'com_redevent.event' || $context == 'com_redevent.event.form' )
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$this->checkItemAccess($row);
			}
		}

		// Check for access levels from the category
		if ($context == 'com_redevent.category')
		{
			// Query the database for the old access level if the item isn't new
			if (!$isNew)
			{
				$this->checkCategoryAccess($row);
			}
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle web links here
		if ($context == 'com_redevent.event' || $context == 'com_redevent.event.form' )
		{
			$this->itemStateChange($pks, $value);
		}
		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}

	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
	 * @param   string               $format  The item format
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Build the necessary route and path information.
		$item->url = RedeventHelperRoute::getDetailsRoute($item->slug);
		$item->route = RedeventHelperRoute::getDetailsRoute($item->slug);
		$item->path = FinderIndexerHelper::getContentPath($item->route);

		// Handle the link to the meta-data.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Event');

		// index categories
		$this->addCategoriesTaxonomy($item);

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		// Load dependent classes.
		require_once JPATH_SITE . '/libraries/redevent/helper/route.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = $sql instanceof JDatabaseQuery ? $sql : $db->getQuery(true);
		$sql->select('a.id, a.title, a.alias, a.summary AS summary, a.datdescription AS description');
		$sql->select('a.meta_keywords AS metakey, a.meta_description AS metadesc');
		$sql->select('a.created_by, a.modified, a.modified_by');
		$sql->select('a.published AS state, a.created AS start_date');

		// FIXME: it's not right, but the adapter itself does the same !
		$sql->select('MAX(c.access) AS access, a.created AS start_date');

		// Handle the alias CASE WHEN portion of the query
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $sql->charLength('a.alias');
		$case_when_item_alias .= ' THEN ';
		$a_id = $sql->castAsChar('a.id');
		$case_when_item_alias .= $sql->concatenate(array($a_id, 'a.alias'), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id.' END as slug';
		$sql->select($case_when_item_alias);

		$sql->select('u.name AS author');

		$sql->from('#__redevent_events AS a');
		$sql->join('LEFT', '#__users AS u ON u.id = a.created_by');
		$sql->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$sql->join('INNER', '#__redevent_categories AS c ON c.id = xcat.category_id');

		$sql->group('a.id');

		return $sql;
	}


	/**
	 * Method to check the existing access level for items
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function checkItemAccess($row)
	{
		// Store the access level to determine if it changes
		$this->old_access = $row->access;
	}

	protected function checkCategoryAccess($row)
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('access'));
		$query->from($this->db->quoteName('#__redevent_categories'));
		$query->where($this->db->quoteName('id') . ' = ' . (int)$row->id);
		$this->db->setQuery($query);

		// Store the access level to determine if it changes
		$this->old_cataccess = $this->db->loadResult();
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * an event and category.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getStateQuery()
	{
		$sql = $this->db->getQuery(true);
		// Item ID
		$sql->select('a.id');
		// Item and category published state
		$sql->select('a.' . $this->state_field . ' AS state, MAX(c.published) AS cat_state');
		// Item and category access levels
		$sql->select('MAX(c.access) AS cat_access');
		$sql->from($this->table . ' AS a');
		$sql->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$sql->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$sql->group('a.id');

		return $sql;
	}


	/**
	 * Method to get a content item to index.
	 * we need to override this method due to multiple categories in redevent
	 *
	 * @param   integer  $id  The id of the content item.
	 *
	 * @return  FinderIndexerResult  A FinderIndexerResult object.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItem($id)
	{
		JLog::add('FinderIndexerAdapter::getItem', JLog::INFO);

		// Get the list query and add the extra WHERE clause.
		$sql = $this->getListQuery();
		$sql->where('a.' . $this->db->quoteName('id') . ' = ' . (int) $id);

		// Get the item to index.
		$this->db->setQuery($sql);
		$row = $this->db->loadAssoc();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Convert the item to a result object.
		$item = JArrayHelper::toObject($row, 'FinderIndexerResult');

		// Set the item type.
		$item->type_id = $this->type_id;

		// Set the item layout.
		$item->layout = $this->layout;

		return $item;
	}

	/**
	 * Method to get a list of content items to index.
	 * we need to override this method due to multiple categories in redevent
	 *
	 * @param   integer         $offset  The list offset.
	 * @param   integer         $limit   The list limit.
	 * @param   JDatabaseQuery  $sql     A JDatabaseQuery object. [optional]
	 *
	 * @return  array  An array of FinderIndexerResult objects.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItems($offset, $limit, $sql = null)
	{
		JLog::add('FinderIndexerAdapter::getItems', JLog::INFO);

		$items = array();

		// Get the content items to index.
		$this->db->setQuery($this->getListQuery($sql), $offset, $limit);
		$rows = $this->db->loadAssocList();

		// Check for a database error.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Convert the items to result objects.
		foreach ($rows as $row)
		{
			// Convert the item to a result object.
			$item = JArrayHelper::toObject($row, 'FinderIndexerResult');

			// Set the item type.
			$item->type_id = $this->type_id;

			// Set the mime type.
			$item->mime = $this->mime;

			// Set the item layout.
			$item->layout = $this->layout;

			// Set the extension if present
			if (isset($row->extension))
			{
				$item->extension = $row->extension;
			}

			// Add the item to the stack.
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * get categories associated to row
	 *
	 * @param object $row
	 * @return object
	 */
	protected function addCategoriesTaxonomy(FinderIndexerResult &$item)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.name, c.access, c.published AS state');
		$query->from('#__redevent_categories AS c');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.category_id = c.id');
		$query->where('xcat.event_id = '.$item->id);
		$db->setQuery($query);
		$cats = $db->loadObjectList();

		if ($cats)
		{
			foreach ($cats as $c) {
				$item->addTaxonomy('Category', $c->name, $c->state, $c->access);
			}
		}

		return $item;
	}

	/**
	 * Method to update index data on category access level changes
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function categoryAccessChange($row)
	{
		$sql = clone($this->getStateQuery());
		$sql->where('c.id = ' . (int) $row->id);

		// Get the access level.
		$this->db->setQuery($sql);
		$items = $this->db->loadObjectList();

		// Adjust the access level for each item within the category.
		foreach ($items as $item)
		{
			// Set the access level.
			$temp = max($item->access, $row->access);

			// Update the item.
			$this->change((int) $item->id, 'access', $temp);

			// Reindex the item
			$this->reindex($item->id);
		}
	}
}
