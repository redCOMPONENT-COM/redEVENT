<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML registration View class of the redEvent component
 *
 * @package  RedEvent
 * @since    1.5
 */
class RedeventViewRegistration extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$document   = JFactory::getDocument();

		$event = $this->get('SessionDetails');

		// Start the tag replacer
		$tags = new RedeventTags;
		$tags->setXref($event->xref);

		if ($this->getLayout() == 'confirmed')
		{
			$message = $event->confirmation_message;
			$document->setTitle($event->title . ' - ' . JText::_('COM_REDEVENT_REGISTRATION_CONFIRMED_PAGE_TITLE'));
			$this->addTracking();
		}
		elseif ($this->getLayout() == 'review')
		{
			$message = $event->review_message;
			$document->setTitle($event->title . ' - ' . JText::_('COM_REDEVENT_REGISTRATION_REVIEW_PAGE_TITLE'));
			$tags->setOption('isReview', true);
		}
		elseif ($this->getLayout() == 'edit')
		{
			return $this->displayEdit($tpl);
		}
		elseif ($this->getLayout() == 'cancel')
		{
			return $this->displayCancel($tpl);
		}
		else
		{
			echo 'layout not defined';

			return false;
		}

		$message = $tags->replaceTags($message);

		$this->assignRef('tags',    $tags);
		$this->assignRef('message', $message);
		$this->assignRef('event',   $event);

		return parent::display($tpl);
	}

	/**
	 * Display edit form
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	protected function displayEdit($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$acl  = RedeventUserAcl::getInstance();
		$xref = $app->input->getInt('xref');
		$submitter_id = $app->input->getInt('submitter_id');

		if (!$submitter_id)
		{
			JError::raiseError(0, 'Registration id required');

			return false;
		}

		$model  = $this->getModel();
		$model->setXref($xref);
		$course = $this->get('SessionDetails');

		$registration = $model->getRegistration($submitter_id);

		if (!$registration)
		{
			JError::raiseError(0, $model->getError());

			return false;
		}

		if ($acl->canManageAttendees($registration->xref) && $app->input->get('task') == 'manageredit')
		{
			$action = JRoute::_(RedeventHelperRoute::getRegistrationRoute($xref, 'registration.managerupdate'));
			$this->return = base64_encode(RedeventHelperRoute::getManageAttendees($xref));
		}
		elseif ($registration->uid == $user->get('id'))
		{
			$action = JRoute::_(RedeventHelperRoute::getRegistrationRoute($xref, 'registration.update'));
			$this->return = base64_encode(RedeventHelperRoute::getDetailsRoute($xref));
		}
		else
		{
			JError::raiseError(403, 'NOT AUTHORIZED');

			return false;
		}

		$prices = $this->get('Pricegroups');

		$field = new RedeventRfieldSessionprice;
		$field->setOptions($prices);
		$field->setFormIndex(1);

		if ($registration->sessionpricegroup_id)
		{
			$field->setValue($registration->sessionpricegroup_id);
		}

		$rfoptions = array();
		$rfoptions['extrafields'] = array(1 => array($field));
		$rfoptions['currency'] = $registration->currency;

		$rfcore = RdfCore::getInstance();
		$rfields = $rfcore->getFormFields($course->redform_id, array($submitter_id), 1, $rfoptions);

		$this->assign('action',   $action);
		$this->assign('rfields',  $rfields);
		$this->assign('xref',     $xref);

		return parent::display($tpl);
	}

	/**
	 * Display cancel form
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	protected function displayCancel($tpl)
	{
		$xref = JFactory::getApplication()->input->getInt('xref');
		$rid  = JFactory::getApplication()->input->getInt('rid');

		$document 	= JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CANCEL_REGISTRATION'));

		$model  = $this->getModel();
		$model->setXref($xref);
		$course = $this->get('SessionDetails');
		$course->dateinfo = RedeventHelperDate::formatdate($course->dates, $course->times);

		$cancellink = JRoute::_(RedeventHelperRoute::getDetailsRoute($course->slug, $course->xslug) . '&task=registration.delreguser&rid=' . $rid);

		$this->assignRef('course',     $course);
		$this->assignRef('xref',       $xref);
		$this->assignRef('rid',        $rid);
		$this->assignRef('cancellink', $cancellink);
		$this->assignRef('action',     JRoute::_('index.php?option=com_redevent&xref=' . $xref . '&rid=' . $rid));

		return parent::display($tpl);
	}

	/**
	 * Add google analytics
	 *
	 * @return void
	 */
	protected function addTracking()
	{
		$config = RedeventHelper::config();

		if ($config->get('ga_tracking_on_confirm', 1) && RdfHelperAnalytics::isEnabled())
		{
			$submit_key = JFactory::getApplication()->input->get('submit_key');
			$details = $this->get('SessionDetails');

			$options = array();
			$options['affiliation'] = JText::_('COM_REDEVENT_GA_AFFILIATION');
			$options['sku'] = 'session-signup-' . $details->xref;
			$options['productname'] = $details->venue . ' - ' . $details->xref . ' ' . $details->event_name
				. ($details->session_name ? ' / ' . $details->session_name : '');

			$cats = array();

			foreach ($details->categories as $c)
			{
				$cats[] = $c->name;
			}

			$options['category'] = implode(', ', $cats);

			RdfHelperAnalytics::recordSubmission($submit_key, $options);
		}
	}
}
