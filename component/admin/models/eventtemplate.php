<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Event template
 *
 * @package  Redevent.admin
 * @since    3.1
 */
class RedeventModelEventtemplate extends RModelAdmin
{
	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		// Do not allow to modify the registration form once there are attendees
		if ($form->getValue('id') && $this->hasAttendees($form->getValue('id')))
		{
			$form->setFieldAttribute('redform_id', 'disabled', '1');
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!is_array($item->submission_types))
		{
			$item->submission_types = explode(",", $item->submission_types);
		}

		if (!is_array($item->showfields))
		{
			$item->showfields = explode(",", $item->showfields);
		}

		return $item;
	}

	/**
	 * Check if one of the events using this template has attendeees
	 *
	 * @param   int  $templateId  template id
	 *
	 * @return bool
	 */
	private function hasAttendees($templateId)
	{
		$query = $this->_db->getQuery(true)
				->select('r.id')
				->from('#__redevent_register AS r')
				->join('INNER', '#__redevent_event_venue_xref AS x on x.id = r.xref')
				->join('INNER', '#__redevent_events AS e on e.id = x.eventid')
				->where('e.template_id = ' . (int) $templateId);

		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadResult();

		return $res ? true : false;
	}
}
