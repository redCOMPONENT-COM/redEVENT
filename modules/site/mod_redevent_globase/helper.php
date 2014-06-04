<?php
/**
 * @package     Redevent
 * @subpackage  mod_redevent_globase
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


// Register library prefix
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
JLoader::registerPrefix('RedForm', JPATH_LIBRARIES . '/redform');

require_once JPATH_SITE . '/components/com_redevent/helpers/route.php';

/**
 * RedEvent Categories Module helper
 *
 * @package     Redevent
 * @subpackage  mod_redevent_globase
 * @since       2.5
*/
class ModRedeventGlobaseHelper
{
	/**
	 * Return options for nyhedsbrev
	 *
	 * @param   JRegistry  $params  plugin params
	 *
	 * @return array|mixed
	 */
	public static function getNyhedsbrevOptions(JRegistry $params)
	{
		$text = $params->get('nyhedsbrev');

		return self::cleanTextOptions($text);
	}

	protected static function cleanTextOptions($text)
	{
		$options = array();

		if (!$text)
		{
			return $options;
		}

		$lines = explode("\n", $text);

		foreach ($lines as $l)
		{
			if (trim($l))
			{
				$options[] = JHtml::_('select.option', $l, $l);
			}
		}

		return $options;
	}
}
