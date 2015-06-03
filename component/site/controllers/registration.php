<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Registration Controller
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventControllerRegistration extends RedeventControllerFront
{
	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('manageredit', 'edit');
		$this->registerTask('managerupdate', 'update');
		$this->registerTask('managedelreguser', 'delreguser');
		$this->registerTask('review', 'confirm');
	}

	/**
	 * handle registration
	 *
	 * @TODO: refactor, much too long
	 *
	 * @return void
	 */
	public function register()
	{
		if ($this->input->get('cancel', '', 'post'))
		{
			return $this->cancelreg();
		}

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();

		$app = JFactory::getApplication();

		$xref = $this->input->getInt('xref');
		$review = $this->input->getInt('hasreview', 0);
		$isedit = $this->input->getInt('isedit', 0);

		$nbPosted = $this->input->getInt('nbactive', 1);
		$pricegroups = array();

		for ($i = 1; $i < $nbPosted + 1; $i++)
		{
			$pricegroups[] = $this->input->getInt('sessionprice_' . $i);
		}

		if (!$xref)
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_XREF');
			$this->setRedirect('index.php', $msg, 'error');

			return false;
		}

		$status = RedeventHelper::canRegister($xref);

		if (!$status->canregister)
		{
			$msg = $status->status;
			$this->setRedirect('index.php', $msg, 'error');

			return false;
		}

		$model = $this->getModel('registration');
		$model->setXref($xref);

		$details = $model->getSessionDetails();

		// Get prices associated to pricegroups
		$currency = null;

		$options = array();
		$extrafields = array();

		$i = 1;

		if ($model->getPricegroups())
		{
			foreach ($pricegroups as $regPricegroup)
			{
				if (!$regPricegroup)
				{
					$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_PRICE');
					$this->setRedirect('index.php', $msg, 'error');

					return false;
				}

				$field = new RedeventRfieldSessionprice;
				$field->setOptions(array($regPricegroup));
				$field->setValue($regPricegroup->id);
				$field->setFormIndex($i);

				$extrafields[$i++] = array($field);
				$currency = $regPricegroup->currency;
			}

			$options['extrafields'] = $extrafields;
			$options['currency'] = $currency;
		}

		if ($review)
		{
			$options['savetosession'] = 1;
		}

		$rfcore = RdfCore::getInstance();
		$result = $rfcore->saveAnswers('redevent', $options);

		if (!$result)
		{
			// Redform saving failed
			$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED') . ' - ' . $rfcore->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($details->did, $xref)), $msg, 'error');

			return false;
		}

		// Trigger before registration plugin, that can alter redform data, or even stop the registration process
		$notification = false;
		$dispatcher->trigger('onBeforeRegistration', array($xref, &$result, &$notification));

		if ($notification)
		{
			echo $notification;

			return;
		}

		$submit_key = $result->submit_key;
		$this->input->setVar('submit_key', $submit_key);

		if ($review)
		{
			// Remember set prices groups
			$app->setUserState('spgids' . $submit_key, $pricegroups);
		}
		else
		{
			$app->setUserState('spgids' . $submit_key, null);
		}

		if (!$isedit && !$review)
		{
			// Redform saved fine, now add the attendees
			$user = JFactory::getUser();

			if (!$user->get('id') && $details->juser)
			{
				if ($new = $model->createUser($result->posts[0]['sid']))
				{
					$user = $new;
				}
			}

			$k = 0;

			foreach ($result->posts as $rfpost)
			{
				$pricegroup = isset($pricegroups[$k]) ? $pricegroups[$k] : null;
				$k++;

				if (!$res = $model->register($user, $rfpost['sid'], $result->submit_key, $pricegroup))
				{
					$msg = JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_FAILED');
					$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($details->did, $xref)), $msg, 'error');

					return false;
				}

				$dispatcher->trigger('onAttendeeCreated', array($res->id));
			}

			if ($details->notify)
			{
				$model->sendNotificationEmail($submit_key);
			}

			$model->notifyManagers($submit_key);

			$dispatcher->trigger('onEventUserRegistered', array($xref));
		}

		if (!$review)
		{
			$cache = JFactory::getCache('com_redevent');
			$cache->clean();

			$gateway = $app->input->get('gw');

			$rfredirect = $rfcore->getFormRedirect($details->redform_id);

			if ($rfredirect)
			{
				$link = $rfredirect;
			}
			elseif ($gateway)
			{
				$link = RdfHelperRoute::getPaymentProcessRoute($submit_key, $gateway);
			}
			else
			{
				$link = RedeventHelperRoute::getRegistrationRoute($xref, 'registration.confirm', $submit_key);
			}
		}
		else
		{
			$link = RedeventHelperRoute::getRegistrationRoute($xref, 'registration.review', $submit_key);
		}

		if ($app->input->getInt('modal', 0))
		{
			$link .= '&tmpl=component';
		}

		// Redirect to prevent resending the form on refresh
		$this->setRedirect(JRoute::_($link, false));
	}

	/**
	 * Deletes a registered user
	 *
	 * @return void
	 */
	public function delreguser()
	{
		$app = JFactory::getApplication();

		$msgtype = 'message';
		$task = $app->input->getCmd('task');
		$id = $app->input->getInt('id', 0);
		$xref = $app->input->getInt('xref', 0);

		$params = $app->getParams('com_redevent');

		if ($this->cancelRegistration())
		{
			if ($task == 'managedelreguser')
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_REMOVAL_SUCCESSFULL');
			}
			else
			{
				$msg = JText::_('COM_REDEVENT_UNREGISTERED_SUCCESSFULL');
			}
		}
		else
		{
			$msg = $this->getError();
			$msgtype = 'error';
		}

		// Redirect
		if ($task == 'managedelreguser')
		{
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref, 'registration.manageattendees'), false), $msg, $msgtype);
		}
		else
		{
			if ($params->get('details_attendees_layout', 0))
			{
				$this->setRedirect(
					JRoute::_('index.php?option=com_redevent&view=details&id=' . $id . '&tpl=attendees&xref=' . $xref, false), $msg, $msgtype
				);
			}
			else
			{
				$this->setRedirect(
					JRoute::_('index.php?option=com_redevent&view=details&id=' . $id . '&tpl=attendees_table&xref=' . $xref, false), $msg, $msgtype
				);
			}
		}
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function cancelreg()
	{
		$submit_key = $this->input->get('submit_key');
		$xref = $this->input->getInt('xref');

		$model = $this->getModel('registration');
		$model->setXref($xref);
		$model->cancel($submit_key);
		$eventdata = $model->getSessionDetails();

		$msg = JText::_('COM_REDEVENT_Registration_cancelled');
		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($eventdata->did, $xref)), $msg);
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function edit()
	{
		$this->input->set('view', 'registration');
		$this->input->set('layout', 'edit');

		parent::display();
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function manageattendees()
	{
		$acl = RedeventUserAcl::getInstance();
		$xref = $this->input->getInt('xref');

		if ($acl->canManageAttendees($xref))
		{
			$layout = 'manageattendees';
		}
		elseif ($acl->canViewAttendees($xref))
		{
			$layout = 'default';
		}
		else
		{
			$this->setRedirect(RedeventHelperRoute::getMyEventsRoute(), JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'), 'error');
			$this->redirect();
		}

		$this->input->set('view', 'attendees');
		$this->input->set('layout', $layout);

		parent::display();
	}

	/**
	 * Update a registration
	 *
	 * @return void
	 */
	public function update()
	{
		$xref = $this->input->getInt('xref');
		$task = $this->input->get('task');

		if (!$xref)
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_XREF');
			$this->setRedirect('index.php', $msg, 'error');

			return;
		}

		if ($this->input->get('return'))
		{
			$this->setRedirect(base64_decode($this->input->get('return')));
		}
		elseif ($task == 'managerupdate')
		{
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)));
		}
		else
		{
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)));
		}

		if ($this->input->get('cancel', '', 'post'))
		{
			$this->setMessage(JText::_('COM_REDEVENT_Registration_Edit_cancelled'));
			$this->redirect();
		}

		$model = $this->getModel('registration');
		$model->setXref($this->input->getInt('xref'));
		$details = $model->getSessionDetails();

		$submit_key = $this->input->get('submit_key');

		$pricegroup = $this->input->getInt('sessionpricegroup_id');
		$regPricegroup = $model->getRegistrationPrice($pricegroup);

		if (!$regPricegroup)
		{
			$this->setMessage(JText::_('COM_REDEVENT_REGISTRATION_MISSING_PRICE'), 'error');
			$this->redirect();
		}

		$price = $regPricegroup->price;

		// First, ask redform to save it's fields, and return the corresponding sids.
		$options = array('baseprice' => $price, 'currency' => $regPricegroup->currency, 'edit' => 1);
		$rfcore = RdfCore::getInstance();
		$result = $rfcore->saveAnswers('redevent', $options);

		if (!$result)
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED') . ' - ' . $rfcore->getError();

			$this->setMessage($msg, 'error');
			$this->redirect();
		}

		$this->input->set('submit_key', $result->submit_key);
		$sid = $result->posts[0]['sid'];

		// Redform save fine, now save corresponding bookings
		if (!$model->update($sid, $result->submit_key, $regPricegroup->id))
		{
			$this->setMessage(JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_UPDATE_FAILED'), 'error');
			$this->redirect();
		}

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$this->setMessage(JText::_('COM_REDEVENT_REGISTRATION_UPDATED'));
		$this->redirect();
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function confirm()
	{
		if ($this->input->get('task') == 'review')
		{
			$this->input->set('layout', 'review');
		}
		else
		{
			$this->input->set('layout', 'confirmed');
		}

		$this->input->set('view', 'registration');

		parent::display();
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function activate()
	{
		$mainframe = JFactory::getApplication();
		$msgtype = 'message';

		/* Get the confirm ID */
		$confirmid = $this->input->get('confirmid', '', 'get');

		/* Get the details out of the confirmid */
		list($uip, $xref, $uid, $register_id, $submit_key) = explode("x", $confirmid);

		/* Confirm sign up via mail */
		$model = $this->getModel('Registration', 'RedeventModel');
		$model->setXref($xref);
		$eventdata = $model->getSessionDetails();

		/* This loads the tags replacer */
		$this->input->set('xref', $xref);
		$tags = new RedeventTags;
		$tags->setXref($xref);
		$tags->setSubmitkey($submit_key);

		$regdata = $model->getRegistrationFromActivationLink($submit_key, $register_id, $uid, $xref);

		if ($regdata && $regdata->confirmed == 0)
		{
			$model->confirm($register_id);

			// Send activation confirmation email if activated
			if ($eventdata->enable_activation_confirmation)
			{
				$this->_Mailer();

				$rfcore = RdfCore::getInstance();
				$addresses = $rfcore->getSubmissionContactEmails($submit_key);

				/* Check if there are any addresses to be mailed */
				if (count($addresses) > 0)
				{
					/* Start mailing */
					foreach ($addresses as $key => $sid)
					{
						foreach ($sid as $email)
						{
							/* Send a off mailinglist mail to the submitter if set */
							/* Add the email address */
							$this->mailer->AddAddress($email['email']);

							/* Mail submitter */
							$htmlmsg = '<html><head><title></title></title></head><body>'
								. $tags->ReplaceTags($eventdata->notify_confirm_body)
								. '</body></html>';

							// Convert urls
							$htmlmsg = RedeventHelperOutput::ImgRelAbs($htmlmsg);

							$this->mailer->setBody($htmlmsg);
							$this->mailer->setSubject($tags->ReplaceTags($eventdata->notify_confirm_subject));

							/* Send the mail */
							if (!$this->mailer->Send())
							{
								$mainframe->enqueueMessage(JText::_('COM_REDEVENT_THERE_WAS_A_PROBLEM_SENDING_MAIL'));
								RedeventHelperLog::simpleLog('Error sending confirm email' . ': ' . $this->mailer->error);
							}

							/* Clear the mail details */
							$this->mailer->ClearAddresses();
						}
					}
				}
			}

			$msg = JText::_('COM_REDEVENT_REGISTRATION_ACTIVATION_SUCCESSFULL');

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$res = $dispatcher->trigger('onUserConfirmed', array($register_id));
		}
		elseif ($regdata && $regdata->confirmed == 1)
		{
			$msg = JText::_('COM_REDEVENT_YOUR_SUBMISSION_HAS_ALREADY_BEEN_CONFIRMED');
			$msgtype = 'error';
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_YOUR_SUBMISSION_CANNOT_BE_CONFIRMED');
			$msgtype = 'error';
		}

		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($eventdata->did, $xref)), $msg, $msgtype);
	}

	/**
	 * Ajax cancel registration
	 *
	 * @return void
	 */
	public function ajaxcancelregistration()
	{
		$resp = new stdClass;

		if ($this->cancelRegistration())
		{
			$resp->status = 1;
		}
		else
		{
			$resp->status = 0;
			$resp->error = $this->getError();
		}

		echo json_encode($resp);
		JFactory::getApplication()->close();
	}

	/**
	 * Initialise the mailer object to start sending mails
	 *
	 * @return JMail
	 */
	private function _Mailer()
	{
		if (empty($this->mailer))
		{
			$mainframe = JFactory::getApplication();
			jimport('joomla.mail.helper');
			/* Start the mailer object */
			$this->mailer = JFactory::getMailer();
			$this->mailer->isHTML(true);
			$this->mailer->From = $mainframe->getCfg('mailfrom');
			$this->mailer->FromName = $mainframe->getCfg('sitename');
			$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		}

		return $this->mailer;
	}

	/**
	 * Actually do the cancel work (with notifications)
	 *
	 * @return bool
	 */
	protected function cancelRegistration()
	{
		$app = JFactory::getApplication();

		$rid = $app->input->getInt('rid', 0);
		$xref = $app->input->getInt('xref', 0);

		// Get/Create the model
		$model = $this->getModel('Registration', 'RedeventModel');

		if (!$model->cancelregistration($rid, $xref))
		{
			$msg = $model->getError();
			$this->setError($msg);

			return false;
		}

		/* Check if we have space on the waiting list */
		$model_wait = RModel::getAdminInstance('waitinglist');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAttendeeCancelled', array($rid));

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		// Send unreg notification email
		$key = RedeventHelper::getAttendeeSubmitKey($rid);
		$model->notifyManagers($key, true, $rid);

		return true;
	}
}
