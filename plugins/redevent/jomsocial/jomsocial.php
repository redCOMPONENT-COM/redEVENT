<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

// Include jomsocial core
include_once JPATH_BASE . '/components/com_community/libraries/core.php';
include_once JPATH_BASE . '/components/com_community/libraries/userpoints.php';

/**
 * Jomsocial integration
 *
 * @since  2.5
 */
class PlgRedeventjomsocial extends JPlugin
{
	/**
	 * handle event
	 *
	 * @param   int   $event_id  event id
	 * @param   bool  $isNew     is new
	 *
	 * @return boolean
	 */
	public function onEventEdited($event_id, $isNew)
	{
		// Only add to activity stream if selected
		if ($isNew && !$this->params->get('activity_addevent', '1'))
		{
			return true;
		}
		elseif (!$isNew && !$this->params->get('activity_editevent', '1'))
		{
			return true;
		}

		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$query = ' SELECT a.id, a.title, '
			. ' x.id as xref, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
			. ' FROM #__redevent_events AS a '
			. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
			. ' WHERE a.id = ' . $event_id;
		$db->setQuery($query);

		if (!$event = $db->loadObject())
		{
			if ($db->getErrorNum())
			{
				RedeventError::raiseWarning('0', $db->getErrorMsg());
			}

			return false;
		}

		$eventlink = '<a href="' . JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug) . '">'
			. $event->title . '</a>';

		$act = new stdClass;
		$act->cmd = 'redevent.editevent';
		$act->actor = $user->get('id');
		$act->target = 0;

		$act->title = sprintf(($isNew) ? JText::_('{actor} added event %s') : JText::_('{actor} edited event %s'), $eventlink);
		$act->content = '';
		$act->app = 'redevent';
		$act->cid = $event_id;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);

		// User points system
		if ($isNew)
		{
			CuserPoints::assignPoint('redevent.event.add');
		}
		else
		{
			CuserPoints::assignPoint('redevent.event.edit');
		}

		return true;
	}

	/**
	 * Handle venue edited
	 *
	 * @param   int   $venue_id  venue id
	 * @param   bool  $isNew     is new
	 *
	 * @return boolean
	 */
	public function onVenueEdited($venue_id, $isNew)
	{
		// Only add to activity stream if selected
		if ($isNew && !$this->params->get('activity_addvenue', '1'))
		{
			return true;
		}
		elseif (!$isNew && !$this->params->get('activity_editvenue', '1'))
		{
			return true;
		}

		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$query = ' SELECT a.id, a.venue, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
			. ' FROM #__redevent_venues AS a '
			. ' WHERE a.id = ' . $venue_id;
		$db->setQuery($query);

		if (!$venue = $db->loadObject())
		{
			if ($db->getErrorNum())
			{
				RedeventError::raiseWarning('0', $db->getErrorMsg());
			}

			return false;
		}

		$link = '<a href="' . JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $venue->slug) . '">' . $venue->venue . '</a>';

		$act = new stdClass;
		$act->cmd = 'redevent.editvenue';
		$act->actor = $user->get('id');
		$act->target = 0;

		$act->title = sprintf(($isNew) ? JText::_('{actor} added venue %s') : JText::_('{actor} edited venue %s'), $link);
		$act->content = '';
		$act->app = 'redevent';
		$act->cid = $venue_id;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);

		// User points system
		if ($isNew)
		{
			CuserPoints::assignPoint('redevent.venue.add');
		}
		else
		{
			CuserPoints::assignPoint('redevent.venue.edit');
		}

		return true;
	}

	/**
	 * Handle session edited
	 *
	 * @param   int  $xref  session id
	 *
	 * @return boolean
	 */
	public function onEventUserRegistered($xref)
	{
		// Only add to activity stream if selected
		if (!$this->params->get('activity_eventregister', '1'))
		{
			return true;
		}

		$db = JFactory::getDBO();

		// Link to event
		$query = ' SELECT a.id, a.title, a.created_by, '
			. ' x.id as xref, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
			. ' FROM #__redevent_events AS a '
			. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
			. ' WHERE x.id = ' . $xref;
		$db->setQuery($query);

		if (!$event = $db->loadObject())
		{
			if ($db->getErrorNum())
			{
				RedeventError::raiseWarning('0', $db->getErrorMsg());
			}

			return false;
		}

		$eventlink = '<a href="' . JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug) . '">'
			. $event->title . '</a>';

		$user = JFactory::getUser();

		$act = new stdClass;
		$act->cmd = 'redevent.register';
		$act->actor = $user->get('id');
		$act->target = 0;

		$act->title = sprintf(JText::_('{actor} registered to %s'), $eventlink);
		$act->content = '';
		$act->app = 'redevent';
		$act->cid = $user->get('id');

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);

		// User points system
		if ($user->get('id') != $event->created_by)
		{
			CuserPoints::assignPoint('redevent.event.registered');
			CuserPoints::assignPoint('redevent.event.gotregistration', $event->created_by);
		}

		return true;
	}

	/**
	 * handles user unregistration event
	 *
	 * @param   int  $xref  session id
	 *
	 * @return boolean
	 */
	public function onEventUserUnregistered($xref)
	{
		// Only add to activity stream if selected
		if (!$this->params->get('activity_eventunregister', '1'))
		{
			return true;
		}

		$db = JFactory::getDBO();

		// Link to event
		$query = ' SELECT a.id, a.title, a.created_by, '
			. ' x.id as xref, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
			. ' FROM #__redevent_events AS a '
			. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
			. ' WHERE x.id = ' . $xref;
		$db->setQuery($query);

		if (!$event = $db->loadObject())
		{
			if ($db->getErrorNum())
			{
				RedeventError::raiseWarning('0', $db->getErrorMsg());
			}

			return false;
		}

		$eventlink = '<a href="' . JRoute::_('index.php?option=com_redevent&view=details&xref=' . $event->xref . '&id=' . $event->slug) . '">'
			. $event->title . '</a>';

		$user = JFactory::getUser();

		$act = new stdClass;
		$act->cmd = 'redevent.unregister';
		$act->actor = $user->get('id');
		$act->target = 0;

		$act->title = sprintf(JText::_('{actor} unregistered from %s'), $eventlink);
		$act->content = '';
		$act->app = 'redevent';
		$act->cid = $user->get('id');

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);

		// User points system
		if ($user->get('id') != $event->created_by)
		{
			CuserPoints::assignPoint('redevent.event.unregistered');
			CuserPoints::assignPoint('redevent.event.gotunregistration', $event->created_by);
		}

		return true;
	}

	/**
	 * On attendee display
	 *
	 * @param   int     $user_id  user id
	 * @param   string  $text     text
	 *
	 * @return boolean
	 */
	public function onAttendeeDisplay($user_id, &$text)
	{
		JHTML::_('behavior.tooltip');
		$user = &CFactory::getUser($user_id);
		$avatar = $user->getThumbAvatar();
		$name = $user->getDisplayName();
		$link = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user_id);
		$text = '<a href="' . $link . '" alt="' . $user->get('username') . '">'
			. '<img class="hasTooltip" src="' . $avatar . '" title="' . $name . '"/></a>';

		return true;
	}

	/**
	 * On event createor display
	 *
	 * @param   int     $user_id  user id
	 * @param   Object  $object   object
	 *
	 * @return boolean
	 */
	public function onEventCreatorDisplay($user_id, &$object)
	{
		JHTML::_('behavior.tooltip');
		$user = &CFactory::getUser($user_id);
		$avatar = $user->getThumbAvatar();
		$name = $user->getDisplayName();
		$link = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user_id);
		$object->text = '<a href="' . $link . '" alt="' . $user->get('username') . '">'
			. '<img class="hasTooltip" src="' . $avatar . '" title="' . $name . '"/></a>';

		return true;
	}
}
