<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * CSV View class for Attendees screen
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedEventViewAttendees extends RViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	public function getColumns()
	{
		/**
		 * Get the columns for the csv file.
		 *
		 * @return  array  An associative array of column names as key and the title as value.
		 */
		$cols = array(
			'uregdate' => JText::_('COM_REDEVENT_REGDATE'),
			'uip' => JText::_('COM_REDEVENT_IP_ADDRESS'),
			'uniqueid' => JText::_('COM_REDEVENT_UNIQUE_ID'),
			'username' => JText::_('COM_REDEVENT_USERNAME'),
			'confirmdate' => JText::_('COM_REDEVENT_ACTIVATED'),
			'cancelled' => JText::_('COM_REDEVENT_CANCELLED'),
			'waitinglist' => JText::_('COM_REDEVENT_WAITINGLIST'),
			'price' => JText::_('COM_REDEVENT_PRICE'),
			'vat' => JText::_('COM_REDEVENT_VAT'),
			'pricegroup' => JText::_('COM_REDEVENT_PRICEGROUP'),
			'paid' => JText::_('COM_REDEVENT_PAYMENT'),
		);

		$redformFields = $this->get('RedformFields');

		foreach ($redformFields AS $f)
		{
			$cols['field_' . $f->field_id] = $f->field_header;
		}

		return $cols;
	}
}
