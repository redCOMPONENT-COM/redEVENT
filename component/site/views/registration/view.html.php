<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
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
		$app        = JFactory::getApplication();
		$document   = JFactory::getDocument();
		$user       = JFactory::getUser();
		$dispatcher = JDispatcher::getInstance();

		$config     = RedeventHelper::config();
		$acl        = RedeventUserAcl::getInstance();

		$submit_key = JFactory::getApplication()->input->get('submit_key');

		$event = $this->get('SessionDetails');

		if ($this->getLayout() == 'confirmed')
		{
			$message = $event->confirmation_message;
			$document->setTitle($event->title . ' - ' . JText::_('COM_REDEVENT_REGISTRATION_CONFIRMED_PAGE_TITLE'));
		}
		elseif ($this->getLayout() == 'review')
		{
			$message = $event->review_message;
			$document->setTitle($event->title . ' - ' . JText::_('COM_REDEVENT_REGISTRATION_REVIEW_PAGE_TITLE'));
		}
		elseif ($this->getLayout() == 'edit')
		{
			return $this->_displayEdit($tpl);
		}
		elseif ($this->getLayout() == 'cancel')
		{
			return $this->_displayCancel($tpl);
		}
		else
		{
			echo 'layout not defined';

			return;
		}

		/* Start the tag replacer */
		$tags = new RedeventTags;
		$tags->setXref(JRequest::getInt('xref'));
		$message = $tags->ReplaceTags($message);

		$this->assignRef('tags',    $tags);
		$this->assignRef('message', $message);
		$this->assignRef('event',   $event);
		parent::display($tpl);
	}

	/**
	 * Display edit form
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	protected function _displayEdit($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$acl  = RedeventUserAcl::getInstance();
		$xref = JRequest::getInt('xref');
		$submitter_id = JRequest::getInt('submitter_id');

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

		parent::display($tpl);
	}

	/**
	 * Display cancel form
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	protected function _displayCancel($tpl)
	{
		$user = JFactory::getUser();
		$uri  = JFactory::getURI();

		$xref = JRequest::getInt('xref');
		$rid  = JRequest::getInt('rid');
		$key  = JRequest::getVar('submit_key', '', 'request', 'string');

		$document 	= JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CANCEL_REGISTRATION'));

		$model  = $this->getModel();
		$model->setXref($xref);
		$course = $this->get('SessionDetails');
		$course->dateinfo = RedeventHelperOutput::formatdate($course->dates, $course->times);

		$cancellink = JRoute::_(RedeventHelperRoute::getDetailsRoute($course->slug, $course->xref) . '&task=delreguser&rid=' . $rid);

		$this->assignRef('course',     $course);
		$this->assignRef('xref',       $xref);
		$this->assignRef('rid',        $rid);
		$this->assignRef('cancellink', $cancellink);
		$this->assignRef('action',     JRoute::_('index.php?option=com_redevent&xref=' . $xref . '&rid=' . $rid));

		parent::display($tpl);
	}
}
