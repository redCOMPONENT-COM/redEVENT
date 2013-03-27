<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class RedeventModelFrontadmin extends JModel
{
	/**
	 * returns events as options for filter
	 *
	 * @return array
	 */
	public function getEventsOptions()
	{
		return array();
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getSessionsOptions()
	{
		return array();
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getVenuesOptions()
	{
		return array();
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getCategoriesOptions()
	{
		return array();
	}
}
