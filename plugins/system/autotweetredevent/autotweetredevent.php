<?php
/**
 * @package     RedEVENT
 * @subpackage  RedEVENT.autotweet
 * @copyright   Copyright (C) 2010 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * autotweetredevent can be downloaded from www.redcomponent.com
 * autotweetredevent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * autotweetredevent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with autotweetredevent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.error');

$app = JFactory::getApplication();

// Check for component
if (!JComponentHelper::getComponent('com_autotweet', true)->enabled)
{
	$app->enqueueMessage('AutoTweet NG Component is not installed or not enabled. - ' . __FILE__, 'warning');

	return;
}

include_once JPATH_ROOT . '/administrator/components/com_autotweet/helpers/autotweetbase.php';


// Check for redEVENT extension
if (!JComponentHelper::getComponent('com_redevent', true)->enabled)
{
	$app->enqueueMessage('AutoTweet NG redEVENT-Plugin - redEVENT extension is not installed or not enabled.', 'warning');

	return;
}

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
JLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * redEVENT extension plugin for AutoTweet.
 *
 * @package     RedEVENT
 * @subpackage  RedEVENT.autotweet
 * @since       2.0
 */
class PlgSystemAutotweetRedevent extends plgAutotweetBase
{
	protected $text_template = '';

	protected $date_format = '';

	/**
	 * constructor
	 *
	 * @param   object  &$subject  subject
	 * @param   array   $params    params
	 */
	public function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();

		// Get Plugin info
		$pluginParams = $this->pluginParams;

		$this->text_template = $pluginParams->get('text_template', JText::_('PLG_SYSTEM_AUTOTWEET_REDEVENT_TEXT_TEMPLATE'));
		$this->date_format   = $pluginParams->get('date_format', JText::_('PLG_SYSTEM_AUTOTWEET_REDEVENT_DATE_FORMAT', false, false));
	}

	/**
	 * triggered after a session gets saved
	 *
	 * @param   int  $xref  session id
	 *
	 * @return boolean
	 */
	public function onAfterRedeventSessionSave($xref)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.summary, a.datimage AS image');
		$query->select('x.id as xref, x.dates, x.allday, x.times, x.enddates, x.endtimes');
		$query->select('v.venue, v.city, v.street');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->from('#__redevent_events AS a');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = a.id');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('x.id = ' . (int) $xref);

		$db->setQuery($query);
		$res = $db->loadObject();

		$this->postStatusMessage(
			$res->id,
			JFactory::getDate()->toSql(),
			$this->getFulltext($res),
			'eventsession',
			JRoute::_(JUri::root() . RedeventHelperRoute::getDetailsRoute($res->id, $res->title)),
			$res->image ? JUri::root() . $res->image : '',
			json_encode($res)
		);

		return true;
	}

	/**
	 * Add a button to session toolbar
	 *
	 * @param   RView     $view      view
	 * @param   RToolbar  &$toolbar  toolbar
	 *
	 * @return void
	 *
	 * @since 3.2.1
	 */
	public function onRedeventViewGetToolbar($view, &$toolbar)
	{
		if ($view->getName() !== 'session')
		{
			return;
		}

		$group = new RToolbarButtonGroup();
		$group->addButton(RToolbarBuilder::createStandardButton('session.saveAndTwit', JText::_('PLG_SYSTEM_AUTOTWEET_REDEVENT_SAVE_AND_TWEET'), '', 'icon-twitter', false));
		$toolbar->addGroup($group);
	}

	/**
	 * getExtendedData
	 *
	 * @param   string  $id              Param.
	 * @param   string  $typeinfo        Param.
	 * @param   string  &$native_object  Param.
	 *
	 * @return	array
	 *
	 * @since	1.5
	 */
	public function getExtendedData($id, $typeinfo, &$native_object)
	{
		$event = json_decode($native_object);

		// Return values
		$data = array (
				'title' => $event->title,
				'text' => $this->getFulltext($event),
				'url' => JURI::root() . RedeventHelperRoute::getDetailsRoute($event->slug, $event->xref),
				'fulltext' => $this->getFulltext($event),
				'is_valid' => true,
		);

		if ($event->image)
		{
			$data['image_url'] = JURI::root() . $event->image;
		}

		$ev = array();
		$ev['location'] = $event->venue;
		$ev['street'] = $event->street;
		$ev['city'] = $event->city;

		if (RedeventHelperDate::isValidDate($event->dates))
		{
			$date = RedeventHelperDate::formattime($event->dates, $event->times);
			$ev['start_time'] = $date;
		}

		if (RedeventHelperDate::isValidDate($event->enddates))
		{
			$date = RedeventHelperDate::formattime($event->enddates, $event->endtimes);
			$ev['end_time'] = $date;
		}

		$data['event'] = $ev;

		return $data;
	}

	/**
	 * returns full text
	 *
	 * @param   object  $event  event data
	 *
	 * @return mixed
	 */
	protected function getFulltext($event)
	{
		$app = JFactory::getApplication();
		$text = str_replace("{site_name}", $app->getCfg('sitename'), $this->text_template);
		$text = str_replace("{title}", $event->title, $text);
		$text = str_replace("{venue}", $event->venue, $text);

		if (!strtotime($event->dates))
		{
			$text = str_replace("{date}",  '', $text);
		}
		else
		{
			$date = JFactory::getDate($event->dates);
			$text = str_replace("{date}",  $date->format($this->date_format), $text);
		}

		return $text;
	}
}
