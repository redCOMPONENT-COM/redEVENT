<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskregistration
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskregistration
 * @since       2.5
 */
class plgRedeventMaerskregistration extends JPlugin
{
	protected $registrationId;
	protected $answers;

	/**
	 * intercepts onAttendeeCreated
	 *
	 * @param   int  $rid  registration id
	 *
	 * @return true on success
	 */
	public function onAttendeeCreated($rid)
	{
		$this->registrationId = $rid;
		$this->updatePoNumber();
		$this->updateComments();

		return true;
	}

	protected function getAnswers()
	{
		if (!$this->answers)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, sid');
			$query->from('#__redevent_register');
			$query->where('id = ' . $this->registrationId);

			$db->setQuery($query);
			$registration = $db->loadObject();

			$rfcore = new RedformCore;
			$answers = $rfcore->getSidsFieldsAnswers(array($registration->sid));
			$this->answers = $answers[$registration->sid];
		}

		return $this->answers;
	}

	protected function updatePoNumber()
	{
		// Get ids for ponumber
		$text = $this->params->get('ponumberFieldIds');
		$fieldIds = $this->cleanIds($text);
		$answers = $this->getAnswers();

		foreach ($answers as $a)
		{
			if (in_array($a->id, $fieldIds))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__redevent_register');
				$query->set('ponumber = ' . $db->quote($a->answer));
				$query->where('id = ' . $this->registrationId);

				$db->setQuery($query);
				$res = $db->execute();

				return true;
			}
		}

		return true;
	}

	protected function updateComments()
	{
		// Get ids for ponumber
		$text = $this->params->get('commentsFieldIds');
		$fieldIds = $this->cleanIds($text);
		$answers = $this->getAnswers();

		foreach ($answers as $a)
		{
			if (in_array($a->id, $fieldIds))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__redevent_register');
				$query->set('comments = ' . $db->quote($a->answer));
				$query->where('id = ' . $this->registrationId);

				$db->setQuery($query);
				$res = $db->execute();

				return true;
			}
		}

		return true;
	}

	protected function cleanIds($text)
	{
		$ids = array();

		if (!$text)
		{
			return $ids;
		}

		$lines = explode(",", $text);
		$lines = array_map('trim', $lines);

		foreach ($lines as $l)
		{
			if (strlen($l))
			{
				$ids[] = (int) $l;
			}
		}

		return $ids;
	}
}
