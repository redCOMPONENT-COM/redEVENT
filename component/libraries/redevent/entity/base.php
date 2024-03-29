<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Base Entity.
 *
 * @since  1.0
 */
abstract class RedeventEntityBase
{
	/**
	 * ACL prefix used to check permissions
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $aclPrefix = "core";

	/**
	 * Identifier of the loaded instance
	 *
	 * @var  mixed
	 */
	protected $id = null;

	/**
	 * Cached instances
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * Cached item
	 *
	 * @var  mixed
	 */
	protected $item = null;

	/**
	 * Cached table.
	 *
	 * @var  JTable
	 */
	protected $table;

	/**
	 * Option of the component containing the tables. Example: com_content
	 *
	 * @var  string
	 */
	protected $component = 'com_redevent';

	/**
	 * Translations for items that support them
	 *
	 * @var  array
	 */
	protected $translations = array();

	/**
	 * @const  integer
	 * @since  1.0
	 */
	const STATE_ENABLED = 1;

	/**
	 * @const  integer
	 * @since  1.0
	 */
	const STATE_DISABLED = 0;

	/**
	 * Load a collection from an array.
	 *
	 * @param   array  $items  Array containing entities data
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException
	 */
	public static function loadArray(array $items)
	{
		$entities = array();

		foreach ($items as $item)
		{
			$item = (object) $item;

			if (!property_exists($item, 'id'))
			{
				throw new \RuntimeException(\JText::_('LIB_REDEVENT_ENTITY_COLLECTION_ERROR_LOAD_ARRAY_REQUIRES_ID_PROPERTY'));
			}

			$entity = self::getInstance($item->id)->bind($item);

			$entities[] = $entity;
		}

		return $entities;
	}

	/**
	 * Constructor
	 *
	 * @param   mixed  $id  Identifier of the active item
	 */
	public function __construct($id = null)
	{
		if ($id)
		{
			$this->id = $id;
		}
	}

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __get($property)
	{
		if (null != $this->item && property_exists($this->item, $property))
		{
			return $this->item->$property;
		}

		if ('slug' == $property && null != $this->item)
		{
			if (!empty($this->item->alias))
			{
				return $this->id . '-' . $this->item->alias;
			}

			return $this->id;
		}

		return false;
	}

	/**
	 * Proxy item properties isset. This needs to be implemented for proper result when doing empty() check
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  boolean   true if it exists
	 */
	public function __isset($property)
	{
		if ('slug' == $property)
		{
			return true;
		}

		return null != $this->item && property_exists($this->item, $property);
	}

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 * @param   mixed   $value     Value to assign
	 *
	 * @return  RedeventEntityBase
	 *
	 * @since   1.0
	 */
	public function __set($property, $value)
	{
		if (null === $this->item)
		{
			$this->item = new stdClass;
		}

		$this->item->$property = $value;

		return $this;
	}

	/**
	 * Bind an object/array to the entity
	 *
	 * @param   mixed  $item  Array/Object containing the item fields
	 *
	 * @return  RedeventEntity    Self instance for chaining
	 */
	public function bind($item)
	{
		// Accept basic array binding
		if (is_array($item))
		{
			$item = (object) $item;
		}

		$this->item = $item;

		if (property_exists($item, 'id'))
		{
			$this->id = $item->id;

			$class = get_called_class();

			// Ensure that we cache the item
			if (!isset(static::$instances[$class][$this->id]))
			{
				static::$instances[$class][$this->id] = $this;
			}
		}

		return $this;
	}

	/**
	 * Check if current user can create an item
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function canCreate()
	{
		if ($this->canDo($this->getAclPrefix() . '.create'))
		{
			return true;
		}

		if ($this->canDo($this->getAclPrefix() . '.create.own'))
		{
			return $this->isOwner();
		}

		return false;
	}

	/**
	 * Check if current user can delete an item
	 *
	 * @return  boolean
	 */
	public function canDelete()
	{
		if (!$this->hasId())
		{
			return false;
		}

		if ($this->canDo($this->getAclPrefix() . '.delete'))
		{
			return true;
		}

		if ($this->canDo($this->getAclPrefix() . '.delete.own'))
		{
			return $this->isOwner();
		}

		return false;
	}

	/**
	 * Check if current user can edit this item
	 *
	 * @return  boolean
	 */
	public function canEdit()
	{
		if (!$this->hasId())
		{
			return false;
		}

		// User has global edit permissions
		if ($this->canDo($this->getAclPrefix() . '.edit'))
		{
			return true;
		}

		// User has global edit permissions
		if ($this->canDo($this->getAclPrefix() . '.edit.own'))
		{
			return $this->isOwner();
		}

		return false;
	}

	/**
	 * Check if current user has permission to perform an action
	 *
	 * @param   string  $action  The action. Example: core.create
	 *
	 * @return  boolean
	 */
	public function canDo($action)
	{
		$user = JFactory::getUser();

		return $user->authorise($action, $this->getAssetName());
	}

	/**
	 * Check if user can view this item.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function canView()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return ((int) $item->state === self::STATE_ENABLED || $this->isOwner());
	}

	/**
	 * Remove an instance from cache
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  void
	 */
	public static function clearInstance($id = null)
	{
		$class = get_called_class();

		unset(static::$instances[$class][$id]);
	}

	/**
	 * Format a link
	 *
	 * @param   string   $url     Url to format
	 * @param   boolean  $routed  Process Url through JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	protected function formatUrl($url, $routed = true, $xhtml = true)
	{
		if (!$url)
		{
			return null;
		}

		if (!$routed)
		{
			return $url;
		}

		return JRoute::_($url, $xhtml);
	}

	/**
	 * Get an item property
	 *
	 * @param   string  $property  Property to get
	 * @param   mixed   $default   Default value to assign if property === null | property === ''
	 *
	 * @return  string
	 */
	public function get($property, $default = null)
	{
		$item = $this->getItem();

		if (!empty($item) && property_exists($item, $property))
		{
			return ($item->$property !== null && $item->$property !== '') ? $item->$property : $default;
		}

		return $default;
	}

	/**
	 * Get the ACL prefix applied to this class
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getAclPrefix()
	{
		return $this->aclPrefix;
	}

	/**
	 * Get the item add link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getAddLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		$url = $this->getBaseUrl() . '&task=' . $this->getInstanceName() . '.add' . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item add link with a return link to the current page.
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getAddLinkWithReturn($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		$url = $this->getAddLink($itemId, false, false) . '&return=' . base64_encode(JUri::getInstance()->toString());

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the identifier of the project asset
	 *
	 * @return  string
	 */
	protected function getAssetName()
	{
		if ($this->hasId())
		{
			return $this->getComponent() . '.' . $this->getInstanceName() . '.' . $this->id;
		}

		// Use the global permissions
		return $this->getComponent();
	}

	/**
	 * Gets the base URL for tasks/views.
	 *
	 * Example: index.php?option=com_redevent&view=shop
	 *
	 * @return  string
	 */
	protected function getBaseUrl()
	{
		return 'index.php?option=' . $this->getComponent() . '&view=' . $this->getInstanceName();
	}

	/**
	 * Get the component that contains the tables
	 *
	 * @return  string
	 */
	protected function getComponent()
	{
		if (null === $this->component)
		{
			$this->component = $this->getComponentFromPrefix();
		}

		return $this->component;
	}

	/**
	 * Get the component from the prefix. Ex.: ContentEntityArticle will return com_content
	 *
	 * @return  string
	 */
	protected function getComponentFromPrefix()
	{
		$class = get_class($this);

		return 'com_' . strtolower(strstr($class, 'Entity', true));
	}

	/**
	 * Get an entity date field formatted
	 *
	 * @param   string   $itemProperty     Item property containing the date
	 * @param   string   $format           Desired date format
	 * @param   boolean  $translateFormat  Translate the format for multilanguage purposes
	 *
	 * @return  string
	 */
	public function getDate($itemProperty, $format = 'DATE_FORMAT_LC1', $translateFormat = true)
	{
		$item = $this->getItem();

		if (!$item || !property_exists($item, $itemProperty))
		{
			return null;
		}

		if ($format && $translateFormat)
		{
			$format = JText::_($format);
		}

		return JHtml::_('date', $item->{$itemProperty}, $format);
	}

	/**
	 * Local proxy for JFactory::getDbo()
	 *
	 * @return  JDatabaseDriver
	 */
	protected function getDbo()
	{
		return JFactory::getDbo();
	}

	/**
	 * Get the item delete link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getDeleteLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$urlToken = '&' . JSession::getFormToken() . '=1';

		$url = $this->getBaseUrl() . '&task=' . $this->getInstanceName()
			. '.delete&id=' . $this->getSlug() . $urlToken . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item delete link with a return link to the current page.
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getDeleteLinkWithReturn($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getDeleteLink($itemId, false, false) . '&return=' . base64_encode(JUri::getInstance()->toString());

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item edit link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getEditLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getBaseUrl() . '&task=' . $this->getInstanceName()
			. '.edit&id=' . $this->getSlug() . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Get the item edit link with a return link to the current page.
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getEditLinkWithReturn($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getEditLink($itemId, false, false) . '&return=' . base64_encode(JUri::getInstance()->toString());

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Create and return a cached instance
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function getInstance($id = null)
	{
		if (null === $id)
		{
			return new static;
		}

		$class = get_called_class();

		if (empty(static::$instances[$class][$id]))
		{
			static::$instances[$class][$id] = new static($id);
		}

		return static::$instances[$class][$id];
	}

	/**
	 * Get the name of the current entity type
	 *
	 * @return  string
	 */
	public function getInstanceName()
	{
		$class = get_class($this);

		$name = strstr($class, 'Entity');
		$name = str_replace('Entity', '', $name);

		return strtolower($name);
	}

	/**
	 * Get item from the database
	 *
	 * @param   boolean  $checkExists  check if item exists, throw exception otherwise
	 *
	 * @return  mixed  Object / null
	 *
	 * @throws  InvalidArgumentException
	 */
	public function getItem($checkExists = false)
	{
		if (empty($this->item))
		{
			$this->loadItem();
		}

		if ($checkExists && !$this->item->id)
		{
			throw new InvalidArgumentException('Cannot load Entity');
		}

		return $this->item;
	}

	/**
	 * Get the item link
	 *
	 * @param   mixed    $itemId  Specify a custom itemId if needed. Default: joomla way to use active itemid
	 * @param   boolean  $routed  Process URL with JRoute?
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 *
	 * @return  string
	 */
	public function getLink($itemId = 'inherit', $routed = true, $xhtml = true)
	{
		if (!$this->hasId())
		{
			return null;
		}

		$url = $this->getBaseUrl() . '&id=' . $this->getSlug() . $this->getLinkItemIdString($itemId);

		return $this->formatUrl($url, $routed, $xhtml);
	}

	/**
	 * Generate the Itemid string part for URLs
	 *
	 * @param   mixed  $itemId  inherit or desired itemId. Use 0 to not inherit active itemId
	 *
	 * @return  string
	 */
	protected function getLinkItemIdString($itemId = 'inherit')
	{
		return ($itemId !== 'inherit') ? '&Itemid=' . (int) $itemId : null;
	}

	/**
	 * Generate the item slug for URLs
	 *
	 * @return  string
	 */
	public function getSlug()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return $this->hasId() ? $this->id : null;
		}

		return !empty($item->alias) ? $this->id . '-' . $item->alias : $this->id;
	}

	/**
	 * Get the associated table
	 *
	 * @param   string  $name  Main name of the Table. Example: Article for ContentTableArticle
	 *
	 * @return  RTable
	 */
	protected function getTable($name = null)
	{
		if (null === $name)
		{
			$class = get_class($this);
			$name = strstr($class, 'Entity');
		}

		$name = str_replace('Entity', '', $name);

		return RTable::getAdminInstance($name, array(), $this->getComponent());
	}

	/**
	 * Check if we have an identifier loaded
	 *
	 * @return  boolean
	 */
	public function hasId()
	{
		$id = (int) $this->id;

		return !empty($id);
	}

	/**
	 * Check if item has been loaded
	 *
	 * @return  boolean
	 */
	public function isLoaded()
	{
		return ($this->hasId() && $this->item !== null);
	}

	/**
	 * Check if current member is owner
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function isOwner()
	{
		if (!$this->hasId())
		{
			return false;
		}

		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return ($item->created_by == $user->get('id'));
	}

	/**
	 * Basic instance check: has id + loadable item
	 *
	 * @return  boolean
	 */
	public function isValid()
	{
		if (!$this->hasId())
		{
			return false;
		}

		$item = $this->getItem();

		return !empty($item);
	}

	/**
	 * Load a cached instance and ensure that the item is loaded
	 *
	 * @param   integer  $id  Identifier of the active item
	 *
	 * @return  $this
	 */
	public static function load($id = null)
	{
		$instance = static::getInstance($id);

		if (!$instance->isLoaded())
		{
			$instance->loadItem();
		}

		return $instance;
	}

	/**
	 * Load the item already loaded in a table
	 *
	 * @param   RTable  $table  Table with the item loaded
	 *
	 * @return  RedeventEntityBase  Self instance for chaining
	 */
	public function loadFromTable($table)
	{
		$key = $table->getKeyName();

		if (!empty($table->{$key}))
		{
			// Get the data from the table
			$data = $table->getProperties(1);

			// Item is always an object
			$this->item = JArrayHelper::toObject($data);
			$this->id = $table->id;
			$this->table = clone $table;

			$class = get_called_class();

			// Ensure that we cache the item
			if (!isset(static::$instances[$class][$this->id]))
			{
				static::$instances[$class][$this->id] = $this;
			}
		}

		return $this;
	}

	/**
	 * Default loading is trying to use the associated table
	 *
	 * @return  RedeventEntityBase  Self instance for chaining
	 */
	public function loadItem()
	{
		if ($this->hasId() && ($table = $this->getTable()) && $table->load(array('id' => $this->id)))
		{
			$this->loadFromTable($table);
		}

		return $this;
	}

	/**
	 * Try to directly save the entity using the associated table
	 *
	 * @param   mixed  $item  Object / Array to save. Null = try to store current item
	 *
	 * @return  integer  The item id
	 *
	 * @throws  RuntimeException  When save failed
	 *
	 * @since   1.0
	 */
	public function save($item = null)
	{
		if (null === $item)
		{
			$item = $this->getItem();
		}

		if (!$item)
		{
			throw new RuntimeException("Nothing to save", 422);
		}

		$table = $this->getTable();

		if (!$table instanceof JTable)
		{
			throw new RuntimeException("Table for instance " . $this->getInstanceName() . " could not be loaded", 500);
		}

		if (!$table->save((array) $item))
		{
			throw new RuntimeException("Item could not be saved: " . $table->getError(), 500);
		}

		return $table->id;
	}
}
