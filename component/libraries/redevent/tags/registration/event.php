<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Handles redform tag
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventTagsRegistrationEvent
{
	/**
	 * @var RdfCore
	 */
	private $rfcore;

	/**
	 * @var RedeventEntityEvent
	 */
	private $event;

	/**
	 * Constructor
	 *
	 * @param   int  $eventId  event id
	 */
	public function __construct($eventId)
	{
		$this->rfcore = RdfCore::getInstance();
		$this->event = RedeventEntityEvent::load($eventId);
	}

	/**
	 * Return tag html
	 *
	 * @return string
	 */
	public function getHtml()
	{
		try
		{
			$this->checkCanRegister();

			return $this->process();
		}
		catch (Exception $e)
		{
			return '<span class="error">' . $e->getMessage() . '</span>';
		}
	}

	/**
	 * Do the work
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function process()
	{
		$form = $this->getRedformForm();

		$options = array('extrafields' => array());
		$options['eventId'] = $this->event->id;

		$field = new RedeventRfieldEventsessionprice;
		$field->setEvent($this->event->id);
		$field->setFormIndex(1);

		$options['extrafields'][1] = array($field);

		$options['currency'] = $this->event->getForm()->currency;

		if (RedeventHelper::config()->get('payBeforeConfirm'))
		{
			$options['selectPaymentGateway'] = 1;
		}

		$redformHtml = $this->rfcore->getFormFields($this->event->redform_id, null, 1, $options);
		$event = $this->event;

		$html = RedeventLayoutHelper::render('redevent.registration.event', compact('form', 'redformHtml', 'event'));

		if (RdfHelperAnalytics::isEnabled())
		{
			$event = new stdclass;
			$event->category = 'registration form';
			$event->action = 'display';
			$event->label = "display registration form for event " . $this->event->title;
			$event->value = null;
			RdfHelperAnalytics::trackEvent($event);
		}

		return $html;
	}

	/**
	 * Check the user can register to the session
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function checkCanRegister()
	{
		$sessions = $this->event->getSessions(null, null, array('published' => 1));

		foreach ($sessions as $session)
		{
			$status = RedeventHelper::canRegister($session->id);

			if ($status->canregister)
			{
				return true;
			}
		}

		throw new Exception(JText::_('LIB_REDEVENT_EVENT_REGISTRATION_USER_CANT_REGISTER'));
	}

	/**
	 * Get redFORM form
	 *
	 * @return RdfEntityForm
	 *
	 * @throws Exception
	 */
	private function getRedformForm()
	{
		$form = $this->event->getForm();

		if (!$form->checkFormStatus())
		{
			throw new RuntimeException($form->getStatusMessage(), 500);
		}

		return $form;
	}
}
