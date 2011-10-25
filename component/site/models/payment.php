<?php
/**
 * @version 1.0 $Id: details.php 3056 2010-01-20 11:50:16Z julien $
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEvent Component payment Model
 *
 * @package Joomla
 * @subpackage redevent
 * @since		2.0
 */
class RedeventModelPayment extends JModel
{	
	var $_event = null;
	
	var $_submit_key = null;
	

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	function __construct()
	{
		parent::__construct();

		$submit_key = JRequest::getVar('submit_key');
		$this->setSubmitKey($submit_key);
	}
	
	function setSubmitKey($key)
	{
		$this->_submit_key = $key;
	}
	
	/**
	 * get event details associated to submit_key
	 * @return object
	 */
	function getEvent()
	{
		if (empty($this->_event))
		{
			if (empty($this->_submit_key)) {
				JError::raiseError(0, JText::_('COM_REDEVENT_Missing_key'));
				return false;
			}
			
			$query = ' SELECT e.*, x.*, x.id as xref '
			       . ' FROM #__redevent_register AS r '
			       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
			       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
			       . ' WHERE r.submit_key = '. $this->_db->Quote($this->_submit_key)
			       ;
			$this->_db->setQuery($query, 0, 1);
			$this->_event = $this->_db->loadObject();
		}
		return $this->_event;
	}
}