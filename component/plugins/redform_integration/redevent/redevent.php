<?php
/**
 * @package    Redevent.integration
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Class plgRedform_integrationRedevent
 *
 * @package  Redevent.integration
 * @since    2.5
 */
class plgRedform_integrationRedevent extends JPlugin
{
	private $rfcore;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * returns a title for the object reference in redform
	 *
	 * @param   string  $object_key            should be 'redevent' for this plugin to do something
	 * @param   string  $submit_key            submit ley
	 * @param   object  &$paymentDetailFields  object to return
	 *
	 * @return bool true on success
	 *
	 * @throws Exception
	 */
	public function getRFSubmissionPaymentDetailFields($object_key, $submit_key, &$paymentDetailFields)
	{
		if ($object_key !== 'redevent')
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.title, x.dates, x.enddates, x.times, x.endtimes, e.course_code, r.id AS attendee_id');
		$query->select('v.venue, x.id AS xref');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_register AS r ON r.xref = x.id');
		$query->join('LEFT', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('r.submit_key = ' . $db->Quote($submit_key));

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res)
		{
			throw new Exception('Registration not found for specified key');
		}

		if ($res->dates && strtotime($res->dates))
		{
			if ($res->times && $res->times != '00:00:00')
			{
				$date = strftime('%c', strtotime($res->dates . ' ' . $res->times));
			}
			else
			{
				$date = strftime('%x', strtotime($res->dates));
			}
		}
		else
		{
			$date = JText::_('PLG_REDFORM_INTEGRATION_REDFORM_OPEN_DATE');
		}

		$paymentDetailFields = new stdclass;
		$paymentDetailFields->title = JText::sprintf('PLG_REDFORM_INTEGRATION_REDFORM_TITLE',
			$res->title,
			$res->venue,
			$date
		);

		$fullname = $this->getFullname($submit_key);

		$paymentDetailFields->adminDesc = JText::sprintf('PLG_REDFORM_INTEGRATION_REDFORM_ADMIN_DESC',
			$fullname,
			$res->title,
			$res->venue,
			$date
		);

		$paymentDetailFields->uniqueid = RedeventHelper::getRegistrationUniqueId($res);

		return true;
	}

	/**
	 * Return fullname(s) associated to sumbission
	 *
	 * @param   string  $submit_key  submit_key
	 *
	 * @return string
	 */
	private function getFullname($submit_key)
	{
		$submissions = $this->getRedformCore()->getAnswers($submit_key)->getSingleSubmissions();

		$fullnames = array();

		foreach ($submissions as $answers)
		{
			if ($fullname = $answers->getFullname())
			{
				$fullnames[] = $fullname;
			}
		}

		return implode(', ', $fullnames);
	}

	/**
	 * return redformcore object
	 *
	 * @return RedformCore
	 */
	private function getRedformCore()
	{
		if (!$this->rfcore)
		{
			$this->rfcore = new RdfCore;
		}

		return $this->rfcore;
	}
}
