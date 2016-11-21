<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Email attendees Category
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelEmailattendees extends RModel
{
	/**
	 * Get attendees emails
	 *
	 * @return array
	 */
	public function getEmails()
	{
		$sids = $this->getSids();
		$rfcore = new RdfCore;

		$emails = array();

		foreach ($sids as $sid)
		{
			if ($contacts = $rfcore->getSidContactEmails($sid))
			{
				foreach ($contacts as $contact)
				{
					$emails[$contact['email']] = $contact;
				}
			}
		}

		return $emails;
	}

	/**
	 * Get session info
	 *
	 * @return mixed
	 */
	public function getSession()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('x.dates, x.id AS xref')
			->select('e.title')
			->select('v.venue')
			->from('#__redevent_events AS e')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
			->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id')
			->where('x.id = ' . $this->getState('sessionId'));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get attendees sids
	 *
	 * @return mixed
	 */
	private function getSids()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('sid')
			->from('#__redevent_register');

		if ($cids = $this->getState('cids'))
		{
			$cids = RHelperArray::quote($cids);
			$cids = implode(',', $cids);
			$query->where('id IN (' . $cids . ')');
		}
		else
		{
			$query->where('xref = ' . $this->getState('sessionId'));

			if (is_numeric($this->getState('confirmed')))
			{
				$query->where('confirmed = ' . $this->getState('confirmed'));
			}

			if (is_numeric($this->getState('cancelled')))
			{
				$query->where('cancelled = ' . $this->getState('cancelled'));
			}

			if (is_numeric($this->getState('waiting')))
			{
				$query->where('waitinglist = ' . $this->getState('waiting'));
			}
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * send mail to selected attendees
	 *
	 * @param   string  $subject   subject
	 * @param   string  $body      body
	 * @param   string  $from      from email
	 * @param   string  $fromname  from name
	 * @param   string  $replyto   reply to email
	 *
	 * @return boolean
	 */
	public function send($subject, $body, $from = null, $fromname = null, $replyto = null)
	{
		$app = JFactory::getApplication();
		$emails = $this->getEmails();

		$taghelper = new RedeventTags;
		$taghelper->setXref($this->getState('sessionId'));
		$subject = $taghelper->replaceTags($subject);
		$body    = $taghelper->replaceTags($body);

		$mailer = RdfHelper::getMailer();
		$mailer->setSubject($subject);
		$mailer->MsgHTML('<html><body>' . $body . '</body></html>');

		if (!empty($from) && JMailHelper::isEmailAddress($from))
		{
			$fromname = !empty($fromname) ? $fromname : $app->getCfg('sitename');
			$mailer->setSender(array($from, $fromname));
		}

		if (!empty($replyto) && JMailHelper::isEmailAddress($replyto))
		{
			$mailer->addReplyTo($replyto);
		}

		foreach ($emails as $e)
		{
			$mailer->clearAllRecipients();

			if (isset($e['fullname']))
			{
				$mailer->addAddress($e['email'], $e['fullname']);
			}
			else
			{
				$mailer->addAddress($e['email']);
			}

			if (!$mailer->send())
			{
				$this->setError(JText::sprintf('COM_REDEVENT_EMAIL_ATTENDEES_ERROR_SENDING_EMAIL_TO'), $e['email']);

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$input = JFactory::getApplication()->input;

		$data = $input->get('filter', array(), 'array');

		$sessionId = isset($data['session']) ? (int) $data['session'] : $input->getInt('xref');

		if (!$sessionId)
		{
			throw new RuntimeException('mission session id');
		}

		$this->setState('sessionId', $sessionId);

		$this->setState('confirmed', isset($data['filter.confirmed']) ? $data['filter.confirmed'] : null);
		$this->setState('waiting', isset($data['filter.waiting']) ? $data['filter.waiting'] : null);
		$this->setState('cancelled', isset($data['filter.cancelled']) ? $data['filter.cancelled'] : null);

		if ($cids = $input->get('cid', array(), 'array'))
		{
			JArrayHelper::toInteger($cids);
			$this->setState('cids', $cids);
		}
		else
		{
			$this->setState('cids', array());
		}
	}
}
