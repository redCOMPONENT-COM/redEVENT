<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Venue
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelVenue extends RModelAdmin
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
		$result = parent::getItem($pk);

		if ($result && $result->id)
		{
			$helper = new RedeventHelperAttachment;
			$files = $helper->getAttachments('venue' . $result->id);
			$result->attachments = $files;

			$result->categories = $this->getVenueCategories($result);
		}
		else
		{
			$result->attachments = array();
			$result->categories = array();
		}

		return $result;
	}

	/**
	 * Method to get the category data
	 *
	 * @param   object  $result  result to get categories from
	 *
	 * @return  array
	 */
	private function getVenueCategories($result)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('c.id');
		$query->from('#__redevent_venues_categories AS c');
		$query->join('INNER', '#__redevent_venue_category_xref AS x ON x.category_id = c.id');
		$query->where('x.venue_id = ' . $result->id);

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$result = parent::save($data);

		if ($result)
		{
			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('venue' . $this->getState($this->getName() . '.id'));
		}

		return $result;
	}
}
