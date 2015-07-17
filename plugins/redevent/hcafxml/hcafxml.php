<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Config.Example
 * @since       2.5
 */
class plgRedeventHcafxml extends JPlugin
{
	/**
	 * @var DOMDocument
	 */
	private $domtree;

	/**
	 * @var DOMElement
	 */
	private $xmlRoot;

	/**
	 * @var array
	 */
	private $venues;

	/**
	 * Alters component parameters
	 *
	 * @param   JRegistry  $params  parameters
	 *
	 * @return bool true on success
	 */
	public function onAjaxAlleventsxml()
	{
		$this->loadLib();
		$this->loadLanguage();

		header ("Content-Type:text/xml");

		$this->domtree = new DOMDocument('1.0', 'UTF-8');

		$this->xmlRoot = $this->domtree->createElement("data");
		$this->xmlRoot = $this->domtree->appendChild($this->xmlRoot);

		$this->addProvider();
		$this->addEvents();

		echo $this->domtree->saveXML();

		JFactory::getApplication()->close();
	}

	/**
	 * Add provider to xml
	 *
	 * @return void
	 */
	private function addProvider()
	{
		$provider = $this->domtree->createElement("provider");
		$provider = $this->xmlRoot->appendChild($provider);

		$provider->appendChild($this->createDomTextElement('name', $this->params->get('provider_name')));
		$provider->appendChild($this->createDomTextElement('email', $this->params->get('provider_email')));
		$provider->appendChild($this->createDomTextElement('phone', $this->params->get('provider_phone')));
	}

	/**
	 * Add events to xml
	 *
	 * @return void
	 */
	private function addEvents()
	{
		if (!$sessions = $this->getSessions())
		{
			return;
		}

		$eventsRoot = $this->domtree->createElement("events");
		$eventsRoot = $this->xmlRoot->appendChild($eventsRoot);

		foreach ($sessions as $session)
		{
			$this->addEvent($eventsRoot, $session);
		}
	}

	/**
	 * Add session data
	 *
	 * @param   DOMElement  $eventsRoot  events xml root
	 * @param   Object      $session     session data
	 *
	 * @return void
	 */
	private function addEvent($eventsRoot, $session)
	{
		$eventRoot = $this->domtree->createElement("event");
		$eventRoot = $eventsRoot->appendChild($eventRoot);
		$eventRoot->setAttribute('id', $session->xref);

		$eventRoot->appendChild($this->createDomTextElement('title', substr($session->title, 0, 100)));
		$eventRoot->appendChild($this->createDomTextElement('shortdescription', substr($session->summary, 0, 255)));
		$eventRoot->appendChild($this->createDomTextElement('longdescription', $session->details));

		if ($session->datimage)
		{
			$eventRoot->appendChild($this->createDomTextElement('pictureurl', JURI::root() . $session->datimage));
		}

		$categories = array_map(function($category){
			return $category->name;
		}, $session->categories);

		$eventRoot->appendChild($this->createDomTextElement('category', implode(', ', $categories)));
		$eventRoot->appendChild($this->createDomTextElement('target', JText::_('PLG_REDEVENT_HCAFXML_TARGET_ALL')));

		if (RedeventHelper::isValidDate($session->dates))
		{
			$date = JFactory::getDate($session->dates);
			$eventRoot->appendChild($this->createDomTextElement('startdate', $date->format('Y-m-d')));
		}

		if (RedeventHelper::isValidDate($session->enddates))
		{
			$date = JFactory::getDate($session->enddates);
			$eventRoot->appendChild($this->createDomTextElement('enddate', $date->format('Y-m-d')));
		}

		if (preg_match('/^([0-9]+:[0-9]+)/', $session->times, $match))
		{
			$time = str_replace(':', '.', $match[1]);

			if (preg_match('/^([0-9]+:[0-9]+)/', $session->endtimes, $match))
			{
				$time .= '-' . str_replace(':', '.', $match[1]);
			}

			$eventRoot->appendChild($this->createDomTextElement('time', $time));
		}

		$eventRoot->appendChild($this->createDomTextElement('url', htmlspecialchars(JURI::root().RedeventHelperRoute::getDetailsRoute($session->slug, $session->xslug))));
		$eventRoot->appendChild($this->createDomTextElement('email', $this->params->get('provider_email')));

		$this->addLocation($eventRoot, $session);
	}

	/**
	 * Add venue data
	 *
	 * @param   DOMElement  $eventRoot  event xml root
	 * @param   Object      $session    session data
	 *
	 * @return void
	 */
	private function addLocation($eventRoot, $session)
	{
		if (!$session->venue_id)
		{
			return;
		}

		$venue = $this->getVenue($session->venue_id);

		$venueRoot = $this->domtree->createElement("location");
		$venueRoot = $eventRoot->appendChild($venueRoot);
		$venueRoot->setAttribute('id', $venue->id);

		$venueRoot->appendChild($this->createDomTextElement('locationname', $venue->venue));
		$venueRoot->appendChild($this->createDomTextElement('locationaddress', $venue->street));
		$venueRoot->appendChild($this->createDomTextElement('locationzipcode', $venue->plz));
		$venueRoot->appendChild($this->createDomTextElement('locationzipcity', $venue->city));
		$venueRoot->appendChild($this->createDomTextElement('locationcountry', $venue->country));
		$venueRoot->appendChild($this->createDomTextElement(
			'locationurl',
			$venue->url ? htmlspecialchars($venue->url) : htmlspecialchars(JURI::root() . RedeventHelperRoute::getVenueEventsRoute($venue->id)))
		);
		$venueRoot->appendChild($this->createDomTextElement('locationdescription', $venue->locdescription));

		if ($venue->categories)
		{
			$categories = array_map(function($category){
				return $category->name;
			}, $venue->categories);
			$venueRoot->appendChild($this->createDomTextElement('locationtype', implode(', ', $categories)));
		}

		$venueRoot->appendChild($this->createDomTextElement('locationemail', $venue->email));
	}

	/**
	 * Get session to add to xml
	 *
	 * @return mixed
	 */
	private function getSessions()
	{
		$model = RModel::getFrontInstance('Simplelist', array('ignore_request' => true), 'com_redevent');
		$model->setState('limit', $this->params->get('limit', 0));
		$model->setState('filter.published', 1);

		$sessions = $model->getData();

		// We are stripping tags from session description, they only seem to be using [session_read_more] and [venues] that can be blanked in xml
		foreach ($sessions as &$session)
		{
			$session->details = preg_replace('/\[([^\]\s]+)(?:\s*)([^\]]*)\]/i', '', $session->details);
		}

		return $sessions;
	}

	/**
	 * Get venue data
	 *
	 * @param   int  $id  venue id
	 *
	 * @return mixed
	 */
	private function getVenue($id)
	{
		if (!$this->venues)
		{
			$this->venues = array();
		}

		if (!isset($this->venues[$id]))
		{
			$model = RModel::getFrontInstance('Venue', array('ignore_request' => true), 'com_redevent');
			$model->setId($id);
			$this->venues[$id] = $model->getData();
		}

		return $this->venues[$id];
	}

	/**
	 * Load lib
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function loadLib()
	{
		// Load redEVENT library
		$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

		if (!file_exists($redeventLoader))
		{
			throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
		}

		include_once $redeventLoader;

		RedeventBootstrap::bootstrap();
	}

	/**
	 * Return DOMElement: tag containing text
	 *
	 * @param   string  $tag   tag name
	 * @param   string  $text  text content
	 *
	 * @return DOMElement
	 */
	private function createDomTextElement($tag, $text)
	{
		$element = $this->domtree->createElement($tag);
		$element->appendChild(
			$this->domtree->createTextNode($text)
		);

		return $element;
	}
}
