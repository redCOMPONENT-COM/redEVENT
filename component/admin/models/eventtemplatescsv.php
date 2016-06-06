<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent eventtemplatess csv Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelEventtemplatescsv extends RModelAdmin
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

		$query->select('t.*')
			->select('f.formname')
			->from('#__redevent_event_template AS t')
			->innerJoin('#__rwf_forms AS f ON f.id = t.redform_id')
			->order('t.id');

		$this->_db->setQuery($query);
		$rows = $this->_db->loadAssocList();

		$unset = array("checked_out", "checked_out_time", "redform_id");

		$results = $rows ? array_map(
			function($row) use ($unset)
			{
				foreach ($unset as $property)
				{
					unset($row[$property]);
				}

				return $row;
			}, $rows
		) : false;

		return $results;
	}
}
