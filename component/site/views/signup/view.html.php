<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML Details View class of the EventList component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewSignup extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'edit')
		{
			return $this->displayEdit();
		}

		$app = JFactory::getApplication();

		$this->tmp_xref = $app->input->getInt('xref');
		$this->tmp_id = $app->input->getInt('id');

		$document = JFactory::getDocument();
		$params = $app->getParams();
		$menu = $app->getMenu();
		$user = JFactory::getUser();
		$item = $menu->getActive();

		/* Load the event details */
		$course = $this->get('Details');
		$venue = $this->get('Venue');

		$pagetitle = $params->set('page_title', JText::_('COM_REDEVENT_SIGNUP_PAGE_TITLE'));
		$document->setTitle($pagetitle);

		// Print
		$params->def('print', !$app->getCfg('hidePrint'));
		$params->def('icons', $app->getCfg('icons'));

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		/* This loads the tags replacer */
		$tags = new RedeventTags;
		$tags->setEventId($this->tmp_id);
		$tags->setXref($this->tmp_xref);
		$this->assignRef('tags', $tags);

		switch (JFactory::getApplication()->input->getCmd('subtype', 'webform'))
		{
			case 'email':
				if (JFactory::getApplication()->input->get('sendmail') == '1')
				{
					$model_signup = $this->getModel('Signup');
					/* Send the user the signup email */
					$result = $model_signup->getSendSignupEmail($tags, $course->send_pdf_form);
					$this->assignRef('result', $result);
					JFactory::getApplication()->input->set('xref', $this->tmp_xref);
					JFactory::getApplication()->input->set('id', $this->tmp_id);
				}

				/* Load the view */
				$this->assignRef('page', $course->submission_type_email);
				$tpl = 'email';
				break;

			case 'formaloffer':
				if (JFactory::getApplication()->input->get('sendmail') == '1')
				{
					$model_details = $this->getModel('Details');
					$model_signup = $this->getModel('Signup');
					$model_details->getDetails();
					$venues = $model_details->getVenues();
					/* Send the user the formal offer email */
					$result = $model_signup->getSendFormalOfferEmail($tags);
					$this->assignRef('result', $result);
					JRequest::setVar('xref', $this->tmp_xref);
					JRequest::setVar('id', $this->tmp_id);
				}

				/* Load the view */
				$this->assignRef('page', $course->submission_type_formal_offer);
				$tpl = 'formaloffer';
				break;

			case 'phone':
				/* Load the view */
				$this->assignRef('page', $course->submission_type_phone);
				$tpl = 'phone';
				break;

			case 'webform':
			default:
				if ($params->get('user_before_registration', 0))
				{
					if (!$user->get('id') && $course->juser)
					{
						$message = JText::_('COM_REDEVENT_SETTINGS_CREATE_USER_BEFORE_REGISTRATION_MESSAGE');

						JPluginHelper::importPlugin('redevent');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('onRequireUserBeforeRegistration', array($message));

						// If we are still here, it means weren't redirected yet, so default to regular joomla user manager
						$uri = JFactory::getURI();
						$app->redirect('index.php?option=com_users&view=login&return=' . base64_encode($uri->toString()), $message);
					}
				}

				$options = array();

				// Is pricegroup forced ?
				if ($pg = $app->input->getInt('pg'))
				{
					$options['pricegroupId'] = $pg;
				}

				$page = $tags->replaceTags($course->submission_type_webform, $options);
				$print_link = JRoute::_(
					'index.php?option=com_redevent&view=signup&subtype=webform&task=signup&xref='
					. $this->tmp_xref . '&id=' . $this->tmp_id . '&pop=1&tmpl=component'
				);

				$this->assign('page', $page);
				$this->assign('print_link', $print_link);
				break;
		}

		// The replaceTag function can sometime call the layout directly. This variable allows to make the difference with regular
		// call
		$fullpage = true;

		$this->assignRef('course', $course);
		$this->assignRef('venue', $venue);
		$this->assignRef('params', $params);
		$this->assignRef('pagetitle', $pagetitle);
		$this->assignRef('fullpage', $fullpage);

		return parent::display($tpl);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @throws RuntimeException
	 */
	public function  displayEdit($tpl = null)
	{
		$user = JFactory::getUser();
		$submitter_id = JRequest::getInt('submitter_id', 0);

		if (!$submitter_id)
		{
			throw new RuntimeException('Registration id required');
		}

		$course = $this->get('Details');
		$model = $this->getModel();

		$registration = $model->getRegistration($submitter_id);

		if (!$registration)
		{
			throw new RuntimeException($model->getError);
		}

		$rfcore = RdfCore::getInstance();
		$rfields = $rfcore->getFormFields($course->redform_id, array($submitter_id), 1);

		$this->assign('rfields', $rfields);

		if ($model->getManageAttendees($registration->xref) && JRequest::getVar('task') == 'registration.manageredit')
		{
			$this->assign('edittask', 'registration.manageredit');
		}
		elseif ($registration->uid == $user->get('id'))
		{
			$this->assign('edittask', 'registration.edit');
		}
		else
		{
			throw new RuntimeException('NOT AUTHORIZED', 403);
		}

		return parent::display($tpl);
	}
}
