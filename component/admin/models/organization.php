<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Organization
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedEventModelOrganization extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$query = $this->_db->getQuery(true)
			->select($this->_db->qn('name'))
			->from($this->_db->qn('#__redmember_organization'))
			->where($this->_db->qn('id') . ' = ' . $this->_db->q($item->id));

		$this->_db->setQuery($query);
		$item->name = $this->_db->loadResult();

		return $item;
	}
}
