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
 * Event entity.
 *
 * @since  1.0
 */
class RedeventEntitySession extends RedeventEntityBase
{
	/**
	 * Associated event
	 *
	 * @var RedeventEntityAttendee[]
	 */
	private $attendees;

	/**
	 * Associated event
	 *
	 * @var RedeventEntityEvent
	 */
	private $event;

	/**
	 * @var array
	 */
	private $pricegroups;

	/**
	 * Associated venue
	 *
	 * @var RedeventEntityVenue
	 */
	private $venue;

	/**
	 * Return associated attendees
	 *
	 * @return RedeventEntityAttendee[]
	 */
	public function getAttendees()
	{
		if (is_null($this->attendees))
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$model = RModel::getAdminInstance('attendees', array('ignore_request' => true), 'com_redevent');
				$model->setState('filter.session');

				$attendees = $model->getItems();

				$this->attendees = $attendees ? array_map(
					function($element)
					{
						return RedeventEntityAttendee::load($element->id);
					}, $attendees
				) : false;
			}
		}

		return $this->attendees;
	}

	/**
	 * Return associated event
	 *
	 * @return RedeventEntityEvent
	 */
	public function getEvent()
	{
		if (!$this->event)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->event = RedeventEntityEvent::load($item->eventid);
			}
		}

		return $this->event;
	}

	/**
	 * Return formatted start date
	 *
	 * @param   string  $dateFormat  php date() format
	 * @param   string  $timeFormat  php date() format
	 *
	 * @return string
	 */
	public function getFormattedStartDate($dateFormat = null, $timeFormat = null)
	{
		$item = $this->loadItem();

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			return JText::_('LIB_REDEVENT_OPEN_DATE');
		}

		if (RedeventHelperDate::isValidTime($item->times))
		{
			if (!is_null($dateFormat))
			{
				$format = $dateFormat . (is_null($timeFormat) ? '' : $timeFormat);
			}
			else
			{
				$format = null;
			}

			return RedeventHelperDate::formatdatetime($item->dates . ' ' . $item->times, $format);
		}

		return RedeventHelperDate::formatdate($item);
	}

	/**
	 * Return formatted end date
	 *
	 * @param   string  $dateFormat  php date() format
	 * @param   string  $timeFormat  php date() format
	 *
	 * @return string
	 */
	public function getFormattedEndDate($dateFormat = null, $timeFormat = null)
	{
		$item = $this->loadItem();

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			return JText::_('LIB_REDEVENT_OPEN_DATE');
		}

		if (RedeventHelperDate::isValidTime($item->times))
		{
			if (!is_null($dateFormat))
			{
				$format = $dateFormat . (is_null($timeFormat) ? '' : $timeFormat);
			}
			else
			{
				$format = null;
			}

			return RedeventHelperDate::formatdatetime($item->dates . ' ' . $item->times, $format);
		}

		return RedeventHelperDate::formatdate($item);
	}

	/**
	 * Return initialized RedeventRfieldSessionprice
	 *
	 * @return RedeventRfieldSessionprice
	 */
	public function getPricefield()
	{
		$field = new RedeventRfieldSessionprice;
		$field->setOptions($this->getPricegroups());
		$title = $this->getEvent()->title . ($this->title ? ' - ' . $this->title : '');
		$field->setPaymentRequestItemLabel(JText::sprintf('COM_REDEVENT_REGISTRATION_PRICE_ITEM_LABEL_S', $title));

		return $field;
	}

	/**
	 * Return RedeventEntitySessionpricegroups
	 *
	 * @return   RedeventEntitySessionpricegroup[]
	 */
	public function getPricegroups()
	{
		if (!$this->pricegroups)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('sp.*');
			$query->from('#__redevent_sessions_pricegroups AS sp');
			$query->where('sp.xref = ' . $db->Quote($this->id));

			$db->setQuery($query);
			$items = $db->loadObjectList();

			$this->pricegroups = array_map(
				function($item)
				{
					$pricegroup = RedeventEntitySessionpricegroup::getInstance();
					$pricegroup->bind($item);

					return $pricegroup;
				},
				$items
			);
		}

		return $this->pricegroups;
	}

	/**
	 * Return associated venue
	 *
	 * @return RedeventEntityVenue
	 */
	public function getVenue()
	{
		if (!$this->venue)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->venue = RedeventEntityVenue::getInstance($item->venueid)->loadItem();
			}
		}

		return $this->venue;
	}
}
