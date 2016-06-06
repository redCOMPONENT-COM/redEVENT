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
		$templateId = $this->createTemplate();
		$event    = $this->createEvent($category, $templateId);
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
	 * @param   int  $category    category id
	 * @param   int  $templateId  template id
	 *
	 * @return unknown_type
	 */
	private function createEvent($category, $templateId)
	{
		$event = RTable::getAdminInstance('event');
		$event->title          = JText::_('COM_REDEVENT_SAMPLE_EVENT_TITLE');
		$event->datdescription = JText::_('COM_REDEVENT_SAMPLE_EVENT_DESCRIPTION');
		$event->published      = 1;

		$event->registra       = 1;
		$event->unregistra     = 0;
		$event->max_multi_signup = 1;

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
	 * creates a sample template
	 *
	 * @return int
	 */
	private function createTemplate()
	{
		$object = RTable::getAdminInstance('eventtemplate');
		$object->name          = 'Event template';
		$object->redform_id     = 1;
		$object->juser          = 0;

		$object->notify                 = 1;
		$object->notify_on_list_subject    = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_ON_LIST_SUBJECT');
		$object->notify_on_list_body       = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_ON_LIST_BODY');
		$object->notify_off_list_subject   = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_OFF_LIST_SUBJECT');
		$object->notify_off_list_body      = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_OFF_LIST_BODY');

		$object->activate               = 0;
		$object->notify_subject         = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_SUBJECT');
		$object->notify_body            = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_BODY');
		$object->notify_confirm_subject = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_CONFIRM_SUBJECT');
		$object->notify_confirm_body    = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_CONFIRM_BODY');

		$object->review_message       = JText::_('COM_REDEVENT_SAMPLE_EVENT_REVIEW_MESSAGE');
		$object->confirmation_message = JText::_('COM_REDEVENT_SAMPLE_EVENT_CONFIRMATION_MESSAGE');

		$object->show_names           = 0;
		$object->showfields           = '';

		$object->submission_types         = 'webform';
		$object->submission_type_email    = null;
		$object->submission_type_external = null;
		$object->submission_type_phone    = null;
		$object->submission_type_formal_offer = null;
		$object->submission_type_formal_offer_subject = null;
		$object->submission_type_formal_offer_body    = null;
		$object->submission_type_email_body           = null;
		$object->submission_type_email_pdf            = null;
		$object->submission_type_formal_offer_pdf     = null;
		$object->submission_type_webform              = JText::_('COM_REDEVENT_SAMPLE_EVENT_WEBFORM');
		$object->submission_type_email_subject        = null;
		$object->submission_type_webform_formal_offer = null;

		$object->send_pdf_form = 0;
		$object->pdf_form_data = 0;

		$object->paymentaccepted   = JText::_('COM_REDEVENT_SAMPLE_EVENT_PAYMENTACCEPTED');
		$object->paymentprocessing = JText::_('COM_REDEVENT_SAMPLE_EVENT_PAYMENTPROCESSING');

		if ($object->check() && $object->store())
		{
			return $object->id;
		}
		else
		{
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_template'));

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
