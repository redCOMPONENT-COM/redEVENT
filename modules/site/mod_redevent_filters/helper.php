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

/**
 * RedEvent filters Module helper
 *
 * @package     Redevent
 * @subpackage  mod_redevent_filters
 * @since       2.5
*/
class ModRedeventFiltersHelper
{
	/**
	 * Return form for display
	 *
	 * @param   JRegistry  $params  plugin params
	 *
	 * @return mixed
	 */
	public function getData(JRegistry $params)
	{
		$input = JFactory::getApplication()->input;

		$result = new stdclass;

		if (!($input->get('option') == 'com_redevent'
			&& ($input->get('view') == 'simplelist'))
		)
		{
			// We are only displaying this module whe user is viewing an event list
			return false;
		}

		$model = self::getModel();

		if ($params->get('category_filter'))
		{
			$result->category = $model->getCategoriesOptions();
		}

		if ($params->get('venue_filter'))
		{
			$result->venue = $model->getVenuesOptions();
		}

		if ($params->get('custom_filter'))
		{
			$result->custom = $model->getCustomFilters();
		}

		return $result;
	}

	/**
	 * Return model associated to the view
	 *
	 * @return mixed
	 */
	public function getModel()
	{
		static $model;

		if (!($model))
		{
			$input = JFactory::getApplication()->input;
			$view = $input->getCmd('view', 'simplelist');

			$model = JModel::getInstance(ucfirst($view), 'RedeventModel');
		}

		return $model;
	}
}
