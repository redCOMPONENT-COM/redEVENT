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

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

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
	public static function getData(JRegistry $params)
	{
		$input = JFactory::getApplication()->input;
		$result = new stdclass;

		$eventId = 0;

		if ($params->get('eventid'))
		{
			$eventId = (int) $params->get('eventid');
		}
		elseif ($input->get('option') == 'com_redevent'
			&& $input ->get('view') == 'details'
		)
		{
			$eventId = $input->getInt('id', 0);
		}

		if (!$eventId)
		{
			return false;
		}

		// Allowed formIds
		if ($allowed = trim($params->get('formIds')))
		{
			$allowed = explode(',', $allowed);
			JArrayHelper::toInteger($allowed);
		}

		// Get the model to get the sessions of the event
		$model = RModel::getInstance('Basesessionlist', 'RedeventModel');
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

		$rfcore = RdfCore::getInstance();

		if (!$rfcore->getFormStatus($formId))
		{
			return false;
		}

		$result->form = $rfcore->getForm($formId);
		$result->sessionsOptions = self::getSessionsOptions($sessions, $params);
		$result->pricegroups = self::getPriceGroups($sessions);

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
	protected static function getSessionsOptions($sessions, JRegistry $params)
	{
		$options = array();

		foreach ($sessions as $s)
		{
			$session = RedeventEntitySession::load($s->xref);

			if (!$session->canRegister())
			{
				continue;
			}

			$value = $s->xref;

			if (!RedeventHelperDate::isValidDate($s->dates))
			{
				$date = JText::_('COM_REDEVENT_OPEN_DATE');
			}
			else
			{
				$date = RedeventHelperDate::formatdate($s->dates, $s->times);
			}

			$text = $date . ' - ' . $s->venue;

			$options[] = JHtml::_('select.option', $value, $text);
		}

		return $options;
	}

	/**
	 * Returns sessions prices
	 *
	 * @param   array  $sessions  sessions
	 *
	 * @return array
	 */
	protected static function getPriceGroups($sessions)
	{
		$prices = [];

		foreach ($sessions as $s)
		{
			if ($s->prices && is_array($s->prices))
			{
				foreach ($s->prices as $p)
				{
					$prices[] = [
						'xref' => $p->xref,
						'name' => $p->name,
						'id' => $p->id,
					];
				}
			}
		}

		return $prices;
	}
}
