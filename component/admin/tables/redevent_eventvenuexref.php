<?php
/**
 * @version 1.0 $Id: redevent_register.php 30 2009-05-08 10:22:21Z roland $
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

defined('_JEXEC') or die('Restricted access');

/**
 * EventList registration Model class
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEvent_eventvenuexref extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 		= null;
	/** @var int */
	var $eventid 		= null;
  /** @var int */
  var $venueid    = null;
  /** @var string */
  var $dates    = null;
  /** @var string */
  var $enddates    = null;
  /** @var string */
  var $times    = null;
  /** @var string */
  var $endtimes    = null;
  /** @var string */
  var $details    = '';
  /** @var int */
  var $maxattendees    = 0;
  /** @var int */
  var $maxwaitinglist    = 0;
  /** @var int */
  var $course_credit    = 0;
  /** @var int */
  var $course_price    = null;
  /** @var int */
  var $published = 0;
	

	function RedEvent_eventvenuexref(& $db) {
		parent::__construct('#__redevent_event_venue_xref', 'id', $db);
	}
}
?>