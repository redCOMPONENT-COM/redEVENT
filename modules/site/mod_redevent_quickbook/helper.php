<?php
/**
 * @package     Redevent
 * @subpackage  mod_redevent_quickbook
 * @copyright   (C) 2014 redcomponent.com
 * @license     GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redevent/helpers/route.php';
require_once JPATH_SITE . '/components/com_redform/redform.core.php';

/**
 * RedEvent Categories Module helper
 *
 * @package     Redevent
 * @subpackage  mod_redevent_quickbook
 * @since       2.5
*/
class ModRedeventQuickbookHelper
{
	/**
	 * Return form for display
	 *
	 * @param   JRegistry  $params  plugin params
	 *
	 * @return mixed
	 */
	public function getForm(JRegistry $params)
	{
		$input = JFactory::getApplication()->input;

		if ($input->get('option') == 'com_redevent'
			&& $input ->get('view') == 'details'
		)
		{
			$eventId = $input->getInt('id', 0);
			$xref    = $input->getInt('xref', 0);
		}
		else
		{
			// We are only displaying this module whe user is viewing an event details page
			return;
		}

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if ($params->get('mode', 0))
		{ // tree display
			$res = self::_getTree($res);
		}

		return $res;
	}
}
