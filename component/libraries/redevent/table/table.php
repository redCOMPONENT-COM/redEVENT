<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent events table class
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventTable extends RTable
{
	/**
	 * Return exploded and quoted pks string for query
	 *
	 * @param   array  $pk  ids to sanitize
	 *
	 * @return string
	 */
	protected function sanitizePk($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			JArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		return $pk;
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!parent::bind($src, $ignore))
		{
			return false;
		}

		// Autofill created_by and modified_by information
		$now = JDate::getInstance();
		$nowFormatted = $now->toSql();
		$userId = JFactory::getUser()->get('id');

		if (property_exists($this, 'created_by')
			&& empty($src['created_by']) && (is_null($this->created_by) || empty($this->created_by)))
		{
			$src['created_by']   = $userId;
			$src['created'] = $nowFormatted;
		}

		if (property_exists($this, 'modified_by') && empty($src['modified_by']))
		{
			$src['modified_by']   = $userId;
			$src['modified'] = $nowFormatted;
		}

		return true;
	}
}
