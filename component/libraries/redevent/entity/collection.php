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
 * Collection of entities.
 *
 * @since  3.0
 */
class RedeventEntityCollection implements Countable, Iterator
{
	/**
	 * @var  array
	 */
	protected $entities = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $entities  Entities to initialise the collection
	 */
	public function __construct(array $entities = array())
	{
		$this->entities = $entities;
	}

	/**
	 * Adds an entity to the collection. It won't add any entity that already exists
	 *
	 * @param   RedeventEntityBase  $entity  Entity going to be added
	 *
	 * @return  self
	 */
	public function add(RedeventEntityBase $entity)
	{
		if ($entity->hasId() && !$this->has($entity->getId()))
		{
			$this->entities[$entity->getId()] = $entity;
		}

		return $this;
	}

	/**
	 * Clears the entities of the collection
	 *
	 * @return  self
	 */
	public function clear()
	{
		$this->entities = array();

		return $this;
	}

	/**
	 * Gets the count of entities in this collection
	 *
	 * @return  integer
	 */
	public function count()
	{
		return count($this->entities);
	}

	/**
	 * Get the active entity.
	 * Iterator implementation.
	 *
	 * @return  mixed  RedeventEntityBase | FALSE if no entities
	 */
	public function current()
	{
		return current($this->entities);
	}

	/**
	 * Get an item by it's id
	 *
	 * @param   integer  $id  Item's identifier
	 *
	 * @return  mixed  RedeventEntityBase if item exists. Null otherwise
	 */
	public function get($id)
	{
		if ($this->has($id))
		{
			return $this->entities[$id];
		}

		return null;
	}

	/**
	 * Gets all the entities
	 *
	 * @return  RedeventEntityBase[]
	 */
	public function getAll()
	{
		return $this->entities;
	}

	/**
	 * Check if an entity is already in this collection
	 *
	 * @param   integer  $id  Entity identifier
	 *
	 * @return  boolean
	 */
	public function has($id)
	{
		return isset($this->entities[$id]);
	}

	/**
	 * Returns ids of the entities in the collection
	 *
	 * @return  array
	 */
	public function ids()
	{
		return array_keys($this->entities);
	}

	/**
	 * Check if the collection is empty
	 *
	 * @return  boolean
	 */
	public function isEmpty()
	{
		return !$this->entities;
	}

	/**
	 * Return the id of the active entity.
	 * Iterator implementation.
	 *
	 * @return  mixed  integer | FALSE if no entities
	 */
	public function key()
	{
		return key($this->entities);
	}

	/**
	 * Load a collection from an array.
	 *
	 * @param   array   $items        Array containing entities data
	 * @param   string  $entityClass  Class to use for items
	 *
	 * @return  self
	 *
	 * @since   3.1.6
	 *
	 * @throws  \RuntimeException  When associated entity class cannot be found
	 */
	public function loadArray(array $items, $entityClass)
	{
		if (!class_exists($entityClass))
		{
			throw new \RuntimeException(\JText::sprintf("LIB_REDEVENT_COLLECTION_ERROR_LOAD_CLASS_NOT_FOUND", $entityClass));
		}

		foreach ($items as $item)
		{
			$item = (object) $item;

			if (!property_exists($item, 'id'))
			{
				throw new \RuntimeException(\JText::_('LIB_REDEVENT_COLLECTION_ERROR_LOAD_ARRAY_REQUIRES_ID_PROPERTY'));
			}

			$entity = $entityClass::getInstance($item->id)->bind($item);

			$this->add($entity);
		}

		return $this;
	}

	/**
	 * Gets the next entity.
	 * Iterator implementation.
	 *
	 * @return  mixed  RedeventEntityBase | FALSE if no entities
	 */
	public function next()
	{
		return next($this->entities);
	}

	/**
	 * Removes an item from the collection
	 *
	 * @param   integer  $id  Entity identifier
	 *
	 * @return  boolean
	 */
	public function remove($id)
	{
		if (!$this->has($id))
		{
			return false;
		}

		unset($this->entities[$id]);

		return true;
	}

	/**
	 * Method to get the first entity.
	 * Iterator implementation.
	 *
	 * @return  mixed  RedeventEntityBase | FALSE if no entities
	 */
	public function rewind()
	{
		return reset($this->entities);
	}

	/**
	 * Sets an item. This removes previous item if it already exists
	 *
	 * @param   integer             $id      Entity identifier
	 * @param   RedeventEntityBase  $entity  Entity
	 *
	 * @return  self
	 */
	public function set($id, RedeventEntityBase $entity)
	{
		$this->entities[$id] = $entity;

		return $this;
	}

	/**
	 * Return entities as stdClass objects
	 *
	 * @return  array  An array of stdClass objects
	 */
	public function toObjects()
	{
		$objects = array();

		foreach ($this->entities as $id => $entity)
		{
			$objects[$id] = $entity->getItem();
		}

		return $objects;
	}

	/**
	 * Return entities as an array of a single field
	 *
	 * @param   string  $fieldName  Name of the field to return in the array
	 *
	 * @return  array  An array of fields
	 */
	public function toFieldArray($fieldName)
	{
		$fields = array();

		foreach ($this->entities as $id => $entity)
		{
			$fields[$id] = $entity->getItem()->$fieldName;
		}

		return $fields;
	}

	/**
	 * Check if there are still entities in the entities array.
	 * Iterator implementation.
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return key($this->entities) !== null;
	}
}
