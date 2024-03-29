<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Frontend edit venue model
 *
 * @package  Redevent.front
 * @since    0.9
 */
class RedeventModelEditvenue extends RModelAdmin
{
	protected $formName = 'venue';

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);
		$form->setFieldAttribute('locimage', 'directory', RedeventHelper::config()->get('default_image_path', 'redevent/events'));

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		if ($result && $result->id)
		{
			$helper = new RedeventHelperAttachment;
			$files = $helper->getAttachments('venue' . $result->id, JFactory::getUser()->getAuthorisedViewLevels());
			$result->attachments = $files;

			$result->categories = $this->getVenueCategories($result);
		}
		else
		{
			$params = RedeventHelper::config();

			$result->attachments = array();
			$result->categories = array();
			$result->map = $params->get('showmapserv', 1);
		}

		return $result;
	}

	/**
	 * Get the associated JTable
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  JTable
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		if (empty($name))
		{
			$name = 'Venue';
		}

		return parent::getTable($name, $prefix, $config);
	}

	/**
	 * Method to get the category data
	 *
	 * @param   object  $result  result to get categories from
	 *
	 * @return  array
	 */
	private function getVenueCategories($result)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('c.id');
		$query->from('#__redevent_venues_categories AS c');
		$query->join('INNER', '#__redevent_venue_category_xref AS x ON x.category_id = c.id');
		$query->where('x.venue_id = ' . $result->id);

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$userAcl = RedeventUserAcl::getInstance();
		$pk = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');

		if (!$pk && !$userAcl->canPublishVenue())
		{
			$data['published'] = RedeventHelper::config()->get('default_submit_published_state');
		}

		$result = parent::save($data);

		if ($result)
		{
			$id = $this->getState($this->getName() . '.id');

			// Attachments
			$helper = new RedeventHelperAttachment;
			$helper->store('venue' . $id);

			$isNew = isset($data['id']) && $data['id'] ? false : true;
			$this->notify($id, $isNew);
		}

		return $result;
	}

	/**
	 * Send notifications
	 *
	 * @param   int      $id     venue id
	 * @param   boolean  $isNew  is new
	 *
	 * @return void
	 */
	private function notify($id, $isNew)
	{
		$row = $this->getItem($id);

		$this->notifyAdmins($row, $isNew);
		$this->notifyUser($row, $isNew);
	}

	/**
	 * Send notification to admins
	 *
	 * @param   object   $row    venue data
	 * @param   boolean  $isNew  is new
	 *
	 * @return void
	 */
	private function notifyAdmins($row, $isNew)
	{
		$params = RedeventHelper::config();

		// Create mail
		if (($params->get('mailinform') == 2) || ($params->get('mailinform') == 3))
		{
			$app = JFactory::getApplication();
			$SiteName = $app->getCfg('sitename');
			$MailFrom = $app->getCfg('mailfrom');
			$FromName = $app->getCfg('fromname');

			$link = JRoute::_(JURI::base() . RedeventHelperRoute::getVenueEventsRoute($row->id), false);

			$user = JFactory::getUser();

			$mail = RdfHelper::getMailer();

			$state = $row->published ? JText::sprintf('COM_REDEVENT_MAIL_VENUE_PUBLISHED', $link) : JText::_('COM_REDEVENT_MAIL_VENUE_UNPUBLISHED');

			If (!$isNew)
			{
				$modified_ip = getenv('REMOTE_ADDR');
				$edited = JHTML::Date($row->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$mailbody = JText::sprintf(
					'COM_REDEVENT_MAIL_EDIT_VENUE',
					$user->name,
					$user->username,
					$user->email,
					$modified_ip,
					$edited,
					$row->venue,
					$row->url,
					$row->street,
					$row->plz,
					$row->city,
					$row->country,
					$row->locdescription,
					$state
				);
				$mail->setSubject($SiteName . JText::_('COM_REDEVENT_EDIT_VENUE_MAIL'));
			}
			else
			{
				$created = JHTML::Date($row->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$mailbody = JText::sprintf(
					'COM_REDEVENT_MAIL_NEW_VENUE',
					$user->name,
					$user->username,
					$user->email,
					$row->author_ip,
					$created,
					$row->venue,
					$row->url,
					$row->street,
					$row->plz,
					$row->city,
					$row->country,
					$row->locdescription,
					$state
				);
				$mail->setSubject($SiteName . JText::_('COM_REDEVENT_NEW_VENUE_MAIL'));
			}

			$recipients = explode(',', trim($params->get('mailinformrec')));

			$mail->addRecipient($recipients);
			$mail->setSender(array($MailFrom, $FromName));
			$mail->setBody($mailbody);

			if (!$mail->Send())
			{
				RedeventHelperLog::simpleLog('Error sending created/edited venue notification to site owner');
			}
		}
	}

	/**
	 * Send notification to user
	 *
	 * @param   object   $row    venue data
	 * @param   boolean  $isNew  is new
	 *
	 * @return void
	 */
	private function notifyUser($row, $isNew)
	{
		$params = RedeventHelper::config();

		// Create the mail for the user
		if (($params->get('mailinformuser') == 2) || ($params->get('mailinformuser') == 3))
		{
			$app = JFactory::getApplication();
			$SiteName = $app->getCfg('sitename');
			$MailFrom = $app->getCfg('mailfrom');
			$FromName = $app->getCfg('fromname');

			$user = JFactory::getUser();
			$usermail = RdfHelper::getMailer();

			$link = JRoute::_(JURI::base() . RedeventHelperRoute::getVenueEventsRoute($row->id), false);

			$state = $row->published ?
				JText::sprintf('COM_REDEVENT_USER_MAIL_VENUE_PUBLISHED', $link) : JText::_('COM_REDEVENT_USER_MAIL_VENUE_UNPUBLISHED');

			if (!$isNew)
			{
				$edited = JHTML::Date($row->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));

				$mailbody = JText::sprintf('COM_REDEVENT_USER_MAIL_EDIT_VENUE',
					$user->name, $user->username, $edited, $row->venue, $row->url, $row->street,
					$row->plz, $row->city, $row->country, $row->locdescription, $state
				);

				$usermail->setSubject($SiteName . JText::_('COM_REDEVENT_EDIT_USER_VENUE_MAIL'));
			}
			else
			{
				$created = JHTML::Date($row->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));

				$mailbody = JText::sprintf('COM_REDEVENT_USER_MAIL_NEW_VENUE',
					$user->name, $user->username, $created, $row->venue, $row->url, $row->street,
					$row->plz, $row->city, $row->country, $row->locdescription, $state
				);

				$usermail->setSubject($SiteName . JText::_('COM_REDEVENT_USER_MAIL_NEW_VENUE_SUBJECT'));
			}

			$usermail->addRecipient($user->email);
			$usermail->setSender(array($MailFrom, $FromName));
			$usermail->setBody($mailbody);

			if (!$usermail->Send())
			{
				RedeventHelperLog::simpleLog('Error sending created/edited venue notification to venue owner');
			}
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   $registerIds
	 */
	protected function canEditState($record)
	{
		$userAcl = RedeventUserAcl::getInstance();

		return $userAcl->canPublishVenue($record->id);
	}
}
