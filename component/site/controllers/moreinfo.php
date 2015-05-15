<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component moreinfo Controller
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventControllerMoreinfo extends RedeventControllerFront
{
	/**
	 * Submit info
	 *
	 * @return void
	 */
	public function submitinfo()
	{
		jimport('joomla.mail.helper');

		$app = JFactory::getApplication();

		$params = JComponentHelper::getParams('com_redevent');

		if (!$params->get('enable_moreinfo', 1))
		{
			echo JText::_('COM_REDEVENT_MOREINFO_ERROR_DISABLED_BY_ADMIN');
			$app->close(403);
		}

		$xref = $this->input->getInt('xref');
		$email = $this->input->getString('email');

		$model = $this->getModel('details');
		$details = $model->getDetails();

		if ($xref && $email && JMailHelper::isEmailAddress($email))
		{
			$mailer = JFactory::getMailer();
			$mailer->IsHTML(true);

			$mailer->setSubject(JText::sprintf('COM_REDEVENT_MOREINFO_MAIL_SUBJECT', RedeventHelper::getSessionFullTitle($details)));
			$mailer->AddAddress($app->getCfg('mailfrom'), $app->getCfg('sitename'));

			$mailer->AddReplyTo(array($email, JRequest::getVar('name')));

			$data = array();

			if ($d = $this->input->getString('name'))
			{
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_NAME'), $d);
			}

			if ($d = $this->input->getString('email'))
			{
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_EMAIL'), $d);
			}

			if ($d = $this->input->getString('company'))
			{
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_COMPANY'), $d);
			}

			if ($d = $this->input->getString('phonenumber'))
			{
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_PHONENUMBER'), $d);
			}

			if ($d = $this->input->getString('comments'))
			{
				$data[] = array(Jtext::_('COM_REDEVENT_MOREINFO_LABEL_COMMENTS'), str_replace("\n", "<br/>", $d));
			}

			$table = '<table>';

			foreach ($data as $d)
			{
				$table .= '<tr><td>' . $d[0] . '</td><td>' . $d[1] . '</td></tr>';
			}

			$table .= '</table>';

			$link = JRoute::_(JURI::base() . RedeventHelperRoute::getDetailsRoute($details->did, $details->xslug));
			$link = JHTML::link($link, RedeventHelper::getSessionFullTitle($details));

			$body = JText::sprintf('COM_REDEVENT_MOREINFO_MAIL_BODY', $link, $table);

			$mailer->msgHTML($body);

			$mailer->send();
		}

		// Confirm sending
		$this->setRedirect('index.php?option=com_redevent&view=moreinfo&layout=final&xref=' . $xref);
	}
}
