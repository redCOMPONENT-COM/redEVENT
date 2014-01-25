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
require_once JPATH_SITE . '/components/com_redevent/models/baseeventslist.php';
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
	public function getData(JRegistry $params)
	{
		$input = JFactory::getApplication()->input;
		$result = new stdclass;

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
			return false;
		}

		// Allowed formIds
		if ($allowed = trim($params->get('formIds')))
		{
			$allowed = explode(',', $allowed);
			JArrayHelper::toInteger($allowed);
		}

		// Get the model to get the sessions of the event
		$model = JModel::getInstance('BaseEventList', 'RedeventModel');
		$model->setState('filter_event', $eventId);
		$model->setState('limit', 0);

		if (!$sessions = $model->getData())
		{
			return false;
		}
		elseif ($allowed && !in_array(reset($sessions)->redform_id, $allowed))
		{
			// Form id is not in allowed list
			return false;
		}

		$result->sessions = $sessions;

		// Let's get the form
		$formId = reset($sessions)->redform_id;

		$rfcore = new RedFormCore;

		if (!$rfcore->getFormStatus($formId))
		{
			return false;
		}

		$result->form = $rfcore->getForm($formId);

		$result->action = RedeventHelperRoute::getRegistrationRoute(reset($sessions)->xslug, 'register');

		$result->sessionsOptions = self::getSessionsOptions($sessions, $params);

		$result->pricegroupjs = self::jsPriceGroups($sessions);

		return $result;
	}

	/**
	 * Returns sessions as options
	 *
	 * @param   array      $sessions  sessions
	 * @param   JRegistry  $params    plugin params
	 *
	 * @return array
	 */
	protected function getSessionsOptions($sessions, JRegistry $params)
	{
		$options = array();

		foreach ($sessions as $s)
		{
			$value = $s->xref;

			if (!redEVENTHelper::isValidDate($s->dates))
			{
				$date = JText::_('COM_REDEVENT_OPEN_DATE');
			}
			else
			{
				$date = strftime($params->get('formatdate', '%d/%m/%Y'), strtotime($s->dates . ' ' . $s->times));
			}

			$text = $date . ' - ' . $s->venue;

			$options[] = JHtml::_('select.option', $value, $text);
		}

		return $options;
	}

	/**
	 * Returns js for sessions prices
	 *
	 * @param   array  $sessions  sessions
	 *
	 * @return array
	 */
	protected function jsPriceGroups($sessions)
	{
		$js = array();

		foreach ($sessions as $s)
		{
			if ($s->prices && is_array($s->prices))
			{
				foreach ($s->prices as $p)
				{
					$js[] = "var prices[] = {'xref' => '" . $p->xref . "'"
						. ", 'name' => '" . $p->name . "'"
						. ", 'id' => '" . $p->id . "'"
						. "}";
				}
			}
		}

		return implode("\n", $js);
	}
}
