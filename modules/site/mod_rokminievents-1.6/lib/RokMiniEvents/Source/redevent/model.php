<?php
/**
 * @version 1.0 $Id: eventlist.php 1027 2009-09-27 21:50:56Z julien $
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

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

class RokMiniEventsSourceRedEventModel extends RedeventModelBaseeventlist {

	protected $_params = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct($params)
	{
		parent::__construct();

		$this->_params = $params;
		$this->setState('limit', $params->get('redevent_total',10));
		$this->setState('limitstart', 0);
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildWhere()
	{
		$user		= & JFactory::getUser();

		$where = array();

		// First thing we need to do is to select only needed events
		if ($this->_params->get('redevent_include_archived', 0)) {
			$where[] = ' (x.published = 1 OR x.published = -1) ';
		}
		else {
			$where[] = ' x.published = 1 ';
		}

		if ($this->_params->get('redevent_featured', 0)) {
			$where[] = ' x.featured = 1 ';
		}

		if ($cat = $this->_params->get('redevent_category'))
		{
    	$category = $this->getCategory((int) $cat);
    	if ($category) {
				$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
    	}
		}

		if ($v = $this->_params->get('redevent_venue'))
		{
    	$where[] = ' l.id = ' . $this->_db->Quote($v);
		}

		$query_start_date = null;
		$query_end_date = null;

		if ($this->_params->get('time_range') == 'time_span' || $this->_params->get('rangespan') != 'all_events')
		{
			$query_start_date = $this->_params->get('startmin');
			$startMax = $this->_params->get('startmax', false);
			if ($startMax !== false)
			{
				$query_end_date = $startMax;
			}
		}

		$dates_start='';
		if (!empty($query_start_date)) {
			$where[] = ' x.dates >= ' . $this->_db->Quote($query_start_date);
		}
		$dates_end ='';
		if (!empty($query_end_date)) {
			$where[] = ' x.enddates <= ' . $this->_db->Quote($query_end_date);
		}

		return ' WHERE '.implode(' AND ', $where);
	}

}
