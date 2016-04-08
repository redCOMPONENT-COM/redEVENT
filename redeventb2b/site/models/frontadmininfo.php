<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class Redeventb2bModelFrontadmininfo extends JModelLegacy
{
	/**
	 * @var int
	 */
	private $xref;

	/**
	 * @var string
	 */
	private $question;

	/**
	 * @var JUser
	 */
	private $user;

	/**
	 * @var object
	 */
	private $sessionDetails;

	/**
	 * Send question to session admin
	 *
	 * @param   int     $xref      session id
	 * @param   JUser   $user      user sending the question
	 * @param   string  $question  question
	 *
	 * @throws RuntimeException
	 *
	 * @return void
	 */
	public function sendNotification($xref, $user, $question)
	{
		$this->xref = $xref;
		$this->question = $question;
		$this->user = $user;

		$details = $this->getSessionDetails();

		$mailer = RdfHelper::getMailer();
		$mailer->IsHTML(true);
		$mailer->AddReplyTo(array($user->get('email'), $user->get('name')));

		$mailer->setSubject(
			JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_SUBJECT',
				$details->event_title,
				$details->course_code . ($details->session_code ? '-' . $details->session_code : ''),
				$details->venue,
				RedeventHelperDate::formatdate($details->dates, false)
			)
		);

		$posterInfo = $this->getPosterInfo();
		$body = $posterInfo . $this->question;
		$mailer->setBody($body);

		$recipientsHelper = new RedeventHelperSessionadmins;
		$recipient = $recipientsHelper->getVenueContactEmail($this->xref);

		if (!JMailHelper::isEmailAddress($recipient))
		{
			throw new RuntimeException('Invalid venue admin contact email');
		}

		$mailer->addAddress($recipient);

		if (!$mailer->send())
		{
			throw new RuntimeException('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_SENDING_ERROR');
		}
	}

	/**
	 * Get session details
	 *
	 * @return object
	 */
	private function getSessionDetails()
	{
		if (!$this->sessionDetails)
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_redevent/models');
			$model = $this->getInstance('Details', 'RedeventModel');
			$model->setXref($this->xref);
			$this->sessionDetails = $model->getDetails();
		}

		return $this->sessionDetails;
	}

	/**
	 * Get html admin info
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function getPosterInfo()
	{
		$user = JFactory::getUser();

		$data = RedmemberApi::getUser($user->get('id'));

		if (!$data)
		{
			throw new Exception('Missing redmember data for admin');
		}

		$text = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_POSTER_DETAILS',
			$data->name,
			$data->email,
			isset($data->rm_mobile) && $data->rm_mobile ? $data->rm_mobile : '-'
		);

		return $text;
	}
}
