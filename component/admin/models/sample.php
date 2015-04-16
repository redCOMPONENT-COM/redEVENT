<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component sample data Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelSample extends RModel
{
	/**
	 * creates sample data for redevent
	 *
	 * @return bool
	 */
	public function create()
	{
		$category = $this->getCategory();
		$venue    = $this->getVenue();
		$event    = $this->createEvent($category);
		$this->createXref($event, $venue);

		return true;
	}

	/**
	 * return a category id
	 *
	 * @return int
	 */
	private function getCategory()
	{
		$query = $this->_db->getQuery(true)
			->select('id')
			->from('#__redevent_categories')
			->where('published = 1');

		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadResult();

		if (!$res)
		{
			return $this->createCategory();
		}
		else
		{
			return $res;
		}
	}

	/**
	 * creates a sample category
	 *
	 * @return category id
	 */
	private function createCategory()
	{
		$row = RTable::getAdminInstance('Category');
		$row->name = 'Category S1';
		$row->description = 'Sample category';
		$row->color = '#00DD00';
		$row->published = 1;

		if ($row->check() && $row->store())
		{
			return $row->id;
		}
		else
		{
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_category'));

			return false;
		}
	}

	/**
	 * return a venue id
	 *
	 * @return int
	 */
	private function getvenue()
	{
		$query = $this->_db->getQuery(true)
			->select('id')
			->from('#__redevent_venues')
			->where('published = 1');

		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadResult();

		if (!$res)
		{
			return $this->createVenue();
		}
		else
		{
			return $res;
		}
	}

	/**
	 * creates a sample venue
	 *
	 * @return venue id
	 */
	private function createVenue()
	{
		$row = RTable::getAdminInstance('venue');
		$row->venue        = 'Venue S1';
		$row->locdescription = 'Sample venue';
		$row->published      = 1;

		if ($row->check() && $row->store())
		{
			return $row->id;
		}
		else
		{
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_venue'));

			return false;
		}
	}

	/**
	 * creates a sample event
	 *
	 * @param   int  $category  category id
	 *
	 * @return unknown_type
	 */
	private function createEvent($category)
	{
		$event = RTable::getAdminInstance('event');
		$event->title          = JText::_('COM_REDEVENT_SAMPLE_EVENT_TITLE');
		$event->datdescription = JText::_('COM_REDEVENT_SAMPLE_EVENT_DESCRIPTION');
		$event->published      = 1;
		$event->redform_id     = 1;

		$event->registra       = 1;
		$event->unregistra     = 0;
		$event->juser          = 0;

		$event->notify_on_list_subject    = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_ON_LIST_SUBJECT');
		$event->notify_on_list_body       = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_ON_LIST_BODY');
		$event->notify_off_list_subject   = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_OFF_LIST_SUBJECT');
		$event->notify_off_list_body      = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_OFF_LIST_BODY');

		$event->notify                 = 1;
		$event->activate               = 0;
		$event->notify_subject         = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_SUBJECT');
		$event->notify_body            = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_BODY');
		$event->notify_confirm_subject = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_CONFIRM_SUBJECT');
		$event->notify_confirm_body    = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_CONFIRM_BODY');

		$event->review_message       = JText::_('COM_REDEVENT_SAMPLE_EVENT_REVIEW_MESSAGE');
		$event->confirmation_message = JText::_('COM_REDEVENT_SAMPLE_EVENT_CONFIRMATION_MESSAGE');

		$event->show_names           = 0;
		$event->showfields           = '';

		$event->submission_types         = 'webform';
		$event->submission_type_email    = null;
		$event->submission_type_external = null;
		$event->submission_type_phone    = null;
		$event->max_multi_signup = 1;
		$event->submission_type_formal_offer = null;
		$event->submission_type_formal_offer_subject = null;
		$event->submission_type_formal_offer_body    = null;
		$event->submission_type_email_body           = null;
		$event->submission_type_email_pdf            = null;
		$event->submission_type_formal_offer_pdf     = null;
		$event->submission_type_webform              = JText::_('COM_REDEVENT_SAMPLE_EVENT_WEBFORM');
		$event->submission_type_email_subject        = null;
		$event->submission_type_webform_formal_offer = null;

		$event->send_pdf_form = 0;
		$event->pdf_form_data = 0;

		$event->paymentaccepted   = JText::_('COM_REDEVENT_SAMPLE_EVENT_PAYMENTACCEPTED');
		$event->paymentprocessing = JText::_('COM_REDEVENT_SAMPLE_EVENT_PAYMENTPROCESSING');

		$event->categories = array($category);

		if ($event->check() && $event->store())
		{
			return $event->id;
		}
		else
		{
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_event'));

			return false;
		}
	}

	/**
	 * creates a sample event
	 *
	 * @param   int  $event  event id
	 * @param   int  $venue  venue id
	 *
	 * @return xref id
	 */
	private function createXref($event, $venue)
	{
		$row = RTable::getAdminInstance('session');
		$row->eventid        = $event;
		$row->venueid        = $venue;
		$row->details        = 'Sample date';
		$row->dates          = strftime('%Y-%m-%d', strtotime('+3 days'));
		$row->times          = '14:00';
		$row->enddates       = strftime('%Y-%m-%d', strtotime('+4 days'));
		$row->endtimes       = '15:00';
		$row->published      = 1;

		if ($row->check() && $row->store())
		{
			return $row->id;
		}
		else
		{
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_event_session'));

			return false;
		}
	}
}
