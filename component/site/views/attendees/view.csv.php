<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * CSV Attendees View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewAttendees extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		jimport('joomla.filesystem.file');

		$model = $this->getModel();

		if (!$this->get('ViewAttendees'))
		{
			JError::raiseError(403, 'Not authorized');
		}

		$event     = $this->get('Session');
		$registers = $model->getRegisters(true, true);

		$text = "";

		if (count($registers))
		{
			$fields = array();

			foreach ($registers[0]->fields AS $f)
			{
				$fields[] = $f;
			}

			$text .= RedeventHelper::writecsvrow($fields);

			foreach ((array) $registers as $r)
			{
				$data = array();

				foreach ($r->answers as $val)
				{
					if (stristr($val, '~~~'))
					{
						$val = str_replace('~~~', '\n', $val);
					}

					$data[] = $val;
				}

				$text .= RedeventHelper::writecsvrow($data);
			}
		}

		if (!RedeventHelperDate::isValidDate($event->dates))
		{
			$event->dates = JText::_('COM_REDEVENT_OPEN_DATE');
		}

		$title = JFile::makeSafe(RedeventHelper::getSessionFullTitle($event) . '_' . $event->dates . '_' . $event->venue . '.csv');

		$doc = JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="' . $title . '"');
		header('Pragma: no-cache');
		echo $text;

		JFactory::getApplication()->close();

		return true;
	}
}
