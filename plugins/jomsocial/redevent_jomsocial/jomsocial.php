<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.plugin.plugin');

// include jomsocial core
include_once( JPATH_BASE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');

class plgRedeventjomsocial extends JPlugin {
 
	public function plgRedeventjomsocial(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
	}

	public function onEventEdited($event_id, $isNew)
	{
		// only add to activity stream if selected
		if ($isNew && !$this->params->get('activity_addevent', '1')) {
			return true;
		}
		else if (!$isNew && !$this->params->get('activity_editevent', '1')) {
      return true;
    }
		
		$db = & JFactory::getDBO();
    $user = & JFactory::getUser();
		
    $query = ' SELECT a.id, a.title, '
           . ' x.id as xref, '
           . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
           . ' FROM #__redevent_events AS a '
           . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
           . ' WHERE a.id = ' . $event_id;
    $db->setQuery($query);
    
    if (!$event = $db->loadObject()) {
      if ($db->getErrorNum()) {
        RedeventError::raiseWarning('0', $db->getErrorMsg());
      }
    	return false;
    }
    
    $eventlink = '<a href="' .JRoute::_('index.php?option=com_redevent&view=details&xref='.$event->xref.'&id='.$event->slug) .'">'.$event->title.'</a>';
		
		
		$act = new stdClass();
		$act->cmd   = 'redevent.editevent';
		$act->actor   = $user->get('id');
		$act->target  = 0; // no target
		$act->title   = sprintf(($isNew) ? JText::_('{actor} added event %s') : JText::_('{actor} edited event %s'), $eventlink);
		$act->content   = '';
		$act->app   = 'redevent';
		$act->cid   = $event_id;
		 
		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);
		
		// user points system
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php');
	  if ($isNew) {
      CuserPoints::assignPoint('redevent.event.add');
    }
    else {
      CuserPoints::assignPoint('redevent.event.edit');    	
    }
    
    return true;
	}

  public function onVenueEdited($venue_id, $isNew)
  {
    // only add to activity stream if selected
    if ($isNew && !$this->params->get('activity_addvenue', '1')) {
      return true;
    }
    else if (!$isNew && !$this->params->get('activity_editvenue', '1')) {
      return true;
    }
    
    $db = & JFactory::getDBO();
    $user = & JFactory::getUser();
    
    $query = ' SELECT a.id, a.venue, '
           . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
           . ' FROM #__redevent_venues AS a '
           . ' WHERE a.id = ' . $venue_id;
    $db->setQuery($query);
    
    if (!$venue = $db->loadObject()) {
	    if ($db->getErrorNum()) {
	      RedeventError::raiseWarning('0', $db->getErrorMsg());
	    }
      return false;
    }
    $link = '<a href="' .JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$venue->slug) .'">'.$venue->venue.'</a>';
    
    $act = new stdClass();
    $act->cmd   = 'redevent.editvenue';
    $act->actor   = $user->get('id');
    $act->target  = 0; // no target
    $act->title   = sprintf(($isNew) ? JText::_('{actor} added venue %s') : JText::_('{actor} edited venue %s'), $link);
    $act->content   = '';
    $act->app   = 'redevent';
    $act->cid   = $venue_id;
     
    CFactory::load('libraries', 'activities');
    CActivityStream::add($act);
    
    // user points system
    include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php');
    if ($isNew) {
      CuserPoints::assignPoint('redevent.venue.add');
    }
    else {
      CuserPoints::assignPoint('redevent.venue.edit');     
    }
    
    return true;
  }
	
  public function onEventUserRegistered($xref)
  {
    // only add to activity stream if selected
    if (!$this->params->get('activity_eventregister', '1')) {
      return true;
    }
    
    $db = & JFactory::getDBO();
    // link to event
    $query = ' SELECT a.id, a.title, a.created_by, '
           . ' x.id as xref, '
           . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
           . ' FROM #__redevent_events AS a '
           . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
           . ' WHERE x.id = ' . $xref;
    $db->setQuery($query);    
    if (!$event = $db->loadObject()) {
      if ($db->getErrorNum()) {
        RedeventError::raiseWarning('0', $db->getErrorMsg());
      }
      return false;
    }    
    $eventlink = '<a href="' .JRoute::_('index.php?option=com_redevent&view=details&xref='.$event->xref.'&id='.$event->slug) .'">'.$event->title.'</a>';
      	
    $user = & JFactory::getUser();
    
    $act = new stdClass();
    $act->cmd   = 'redevent.register';
    $act->actor   = $user->get('id');
    $act->target  = 0; // no target
    $act->title   = sprintf(JText::_('{actor} registered to %s'), $eventlink);
    $act->content   = '';
    $act->app   = 'redevent';
    $act->cid   = $user->get('id');
     
    CFactory::load('libraries', 'activities');
    CActivityStream::add($act);
    
    // user points system
    include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php');
    if ($user->get('id') != $event->created_by) {
      CuserPoints::assignPoint('redevent.event.registered');
      CuserPoints::assignPoint('redevent.event.gotregistration', $event->created_by);      
    }
    return true;
  }  

  public function onEventUserUnregistered($xref)
  {   
    // only add to activity stream if selected
    if (!$this->params->get('activity_eventunregister', '1')) {
      return true;
    }
    
    $db = & JFactory::getDBO();
    // link to event
    $query = ' SELECT a.id, a.title, a.created_by, '
           . ' x.id as xref, '
           . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug '
           . ' FROM #__redevent_events AS a '
           . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id '
           . ' WHERE x.id = ' . $xref;
    $db->setQuery($query);    
    if (!$event = $db->loadObject()) {    
	    if ($db->getErrorNum()) {
	      RedeventError::raiseWarning('0', $db->getErrorMsg());
	    }
      return false;
    }    
    $eventlink = '<a href="' .JRoute::_('index.php?option=com_redevent&view=details&xref='.$event->xref.'&id='.$event->slug) .'">'.$event->title.'</a>';
    
    $user = & JFactory::getUser();
    
    $act = new stdClass();
    $act->cmd   = 'redevent.unregister';
    $act->actor   = $user->get('id');
    $act->target  = 0; // no target
    $act->title   = sprintf(JText::_('{actor} unregistered from %s'), $eventlink);
    $act->content   = '';
    $act->app   = 'redevent';
    $act->cid   = $user->get('id');
     
    CFactory::load('libraries', 'activities');
    CActivityStream::add($act);
    
    // user points system
    include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php');
    if ($user->get('id') != $event->created_by) {
      CuserPoints::assignPoint('redevent.event.unregistered');
      CuserPoints::assignPoint('redevent.event.gotunregistration', $event->created_by);
    }
    
    return true;
  }
  
  public function onAttendeeDisplay($user_id, &$text)
  {
  	JHTML::_('behavior.tooltip');
  	$user = & CFactory::getUser($user_id);
  	$avatar = $user->getThumbAvatar();
  	$name = $user->getDisplayName();
  	$link = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user_id);
  	$text = '<a href="' . $link . '" alt="'. $user->get('username') .'"><img class="hasTip" src="'.$avatar.'" title="'. $name .'"/></a>';
  	return true;
  }
    
  public function onEventCreatorDisplay($user_id, &$object)
  {
    JHTML::_('behavior.tooltip');
    $user = & CFactory::getUser($user_id);
    $avatar = $user->getThumbAvatar();
    $name = $user->getDisplayName();
    $link = CRoute::_('index.php?option=com_community&view=profile&userid=' . $user_id);
    $object->text = '<a href="' . $link . '" alt="'. $user->get('username') .'"><img class="hasTip" src="'.$avatar.'" title="'. $name .'"/></a>';
    return true;
  }
}
