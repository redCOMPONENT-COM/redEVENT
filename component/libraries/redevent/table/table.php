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
}
