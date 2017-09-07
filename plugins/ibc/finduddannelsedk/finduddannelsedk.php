<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Ibc.Sessionsdump
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

jimport('redevent.bootstrap');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @since       1.0
 */
class PlgIbcFinduddannelsedk extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since       1.0
	 */
	protected $context = 'finduddannelsedk';

	/**
	 * @var RedeventEntitySession[]
	 * @since  1.0
	 */
	private $sessions;

	/**
	 * @var RedeventEntityVenue[]
	 * @since  1.0
	 */
	private $locations;

	/**
	 * @var RedeventEntityEvent[]
	 * @since  1.0
	 */
	private $events;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @since       1.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);
		RedeventBootstrap::bootstrap();
	}

	/**
	 * Ajax handler
	 *
	 * @return void
	 *
	 * @since       1.0
	 */
	public function onAjaxFinduddannelsedkxml()
	{
		$message = new SimpleXMLElement('<informationUpdateBatch/>');

		$message->addAttribute('xmlns', 'http://educations.com/XmlImport');
		$message->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$message->addAttribute('version', '2.0');

		$institute = $message->addChild('institute');
		$institute->addAttribute('id', $this->params->get('institute_id'));

		$this->addLocations($institute);
		$this->addEducations($institute);

		$xml = $message->asXML();

		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		libxml_use_internal_errors(true);

		if (!$dom->loadXML($xml))
		{
			$errors = libxml_get_errors();
			var_dump($errors);
			JFactory::getApplication()->close();
		}

		if (JFactory::getApplication()->input->getInt('asfile', 0))
		{
			$doc = JFactory::getDocument();
			$doc->setMimeEncoding('text/xml');
			$date = md5(date('Y-m-d-h-i-s'));
			$title = JFile::makeSafe('courses' . $date . '.xml');
			header('Content-Disposition: attachment; filename="' . $title . '"');
		}
		else
		{
			header("Content-type: application/xml");
		}

		echo $dom->saveXML();

		JFactory::getApplication()->close();
	}

	/**
	 * Add locations node
	 *
	 * @param   SimpleXMLElement  $institute  parent node
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	private function addLocations(SimpleXMLElement $institute)
	{
		$locations = $institute->addChild('locations');

		foreach ($this->getLocations() as $venue)
		{
			$location = $locations->addChild('location');
			$location->addAttribute('uniqueIdentifier', 'location' . $venue->id);
			$location->addAttribute('name', $venue->name);
			$location->addChild('place', $venue->name);

			if (!empty($venue->latitude) && !empty($venue->longitude))
			{
				$coords = $location->addChild('coordinates');
				$coords->addAttribute('latitude', $venue->latitude);
				$coords->addAttribute('longitude', $venue->longitude);
			}

			if (!empty($venue->city) || !empty($venue->zip) || !empty($venue->country))
			{
				$address = $location->addChild('visitingAddress');
				$address->addAttribute('street', $venue->street);
				$address->addAttribute('city', $venue->city);
				$address->addAttribute('country', RedeventHelperCountries::getCountryName($venue->country));
				$address->addAttribute('zip', $venue->plz);
			}

			if (!empty($venue->locdescription))
			{
				$location->addChild('description', $this->cleanHtml($venue->locdescription));
			}
		}
	}

	/**
	 * Add Educations node
	 *
	 * @param   SimpleXMLElement  $institute  parent node
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	private function addEducations(SimpleXMLElement $institute)
	{
		$educations = $institute->addChild('educations');
		$educationTypeIds = array(
			10 => 'AMU',
			15 => 'Andet kursus',
		);

		foreach ($this->getEvents() as $event)
		{
			$eductationTypeId = array_search($event->educationTypeId, $educationTypeIds);

			if ($eductationTypeId === false)
			{
				continue;
			}

			$education = $educations->addChild('education');
			$education->addAttribute('uniqueIdentifier', 'education' . $event->id);
			$education->addAttribute('name', $event->title);
			$education->addAttribute('educationTypeID', $eductationTypeId);
			$education->addChild('link', JRoute::_(RedeventHelperRoute::getDetailsRoute($event->id), true, -1));

			$contentFields = $education->addChild('contentFields');

			if (!empty($event->summary))
			{
				$description = $contentFields->addChild('field', $this->cleanHtml($event->summary));
				$description->addAttribute('xmlns:xsi:type', 'default');
				$description->addAttribute('name', 'description');
			}

			$this->addSessions($education, $event);

			$categoriesNode = $education->addChild('categories');

			if ($categories = $event->getCategories())
			{
				foreach ($categories as $category)
				{
					$categoryNode = $categoriesNode->addChild('category');
					$categoryNode->addAttribute('name', $category->name);
				}
			}
		}
	}

	/**
	 * Add events nodes
	 *
	 * @param   SimpleXMLElement     $education  parent node
	 * @param   RedeventEntityEvent  $event      event
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	private function addSessions(SimpleXMLElement $education, RedeventEntityEvent $event)
	{
		$events = $education->addChild('events');

		foreach ($this->getSessions() as $session)
		{
			if ($session->eventid !== $event->id || !$session->venueid)
			{
				continue;
			}

			$eventNode = $events->addChild('event');
			$eventNode->addAttribute('xmlns:xsi:type', 'LocationEvent');
			$eventNode->addAttribute('uniqueIdentifier', 'session' . $session->id);
			$eventNode->addAttribute('eventTypeID', $this->params->get('eventTypeID', 1));
			$eventNode->addAttribute('locationUID', 'location' . $session->venueid);

			$start = $eventNode->addChild('start');

			if ($session->isOpenDate())
			{
				$start->addAttribute('xmlns:xsi:type', 'Text');
				$start->addAttribute('description', JText::_('LIB_REDEVENT_OPEN_DATE'));
			}
			else
			{
				$start->addAttribute('xmlns:xsi:type', 'Fixed');

				$start->addAttribute('startDate', $session->getFormattedStartDate('Y-m-d'));

				if (!$session->isAllDay())
				{
					$start->addAttribute('startTime', $session->getFormattedStartDate('h:i:s'));
				}

				if (RedeventHelperDate::isValidDate($session->enddate))
				{
					$start->addAttribute('endDate', $session->getFormattedEndDate('Y-m-d'));
				}

				if (!$session->isAllDay() && RedeventHelperDate::isValidTime($session->endtime))
				{
					$start->addAttribute('endTime', $session->getFormattedEndDate('h:i:s'));
				}
			}
		}
	}

	/**
	 * Get sessions
	 *
	 * @return RedeventEntitySession[]
	 *
	 * @since       1.0
	 */
	private function getSessions()
	{
		if (is_null($this->sessions))
		{
			$model = RModel::getAdminInstance('Sessions', array('ignore_request' => true), 'com_redevent');
			$sessions = $model->getItems();
			$this->sessions = array();

			foreach ($sessions as $session)
			{
				$entity = RedeventEntitySession::getInstance($session->id);
				$entity->bind($session);

				$this->sessions[] = $entity;
			}
		}

		return $this->sessions;
	}

	/**
	 * Get sessions
	 *
	 * @return RedeventEntityVenue[]
	 *
	 * @since       1.0
	 */
	private function getLocations()
	{
		if (is_null($this->locations))
		{
			$this->locations = array();
			$ids = array();

			foreach ($this->getSessions() as $session)
			{
				$venue = $session->getVenue();

				if (!in_array($venue->id, $ids))
				{
					$this->locations[] = $venue;
					$ids[] = $venue->id;
				}
			}
		}

		return $this->locations;
	}

	/**
	 * Get events
	 *
	 * @return RedeventEntityEvent[]
	 *
	 * @since       1.0
	 */
	private function getEvents()
	{
		if (is_null($this->events))
		{
			$this->events = array();
			$ids = array();

			foreach ($this->getSessions() as $session)
			{
				$event = $session->getEvent();

				if (!in_array($event->id, $ids))
				{
					$event->educationTypeId = $session->custom5;
					$this->events[] = $event;
					$ids[] = $event->id;
				}
			}
		}

		return $this->events;
	}

	/**
	 * Clean html
	 *
	 * @param   string  $text  text to clean
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private function cleanHtml($text)
	{
		return str_replace(array('&nbsp;'), array(''), $text);
	}
}
