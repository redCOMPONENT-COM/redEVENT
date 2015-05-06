<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * CSV Details View class of the redEVENT component
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewDetails extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() == 'exportattendees')
		{
			return $this->_displayAttendees($tpl);
		}
		echo 'layout not found';
	}

	/**
	 * Creates the attendees output for the details view
	 *
	 * @since 2.0
	 */
	function _displayAttendees($tpl = null)
	{
		jimport('joomla.filesystem.file');

		$model = $this->getModel();

		if (!$this->get('ManageAttendees'))
		{
			JError::raiseError(403, 'Not authorized');
		}

		$event = $this->get('Details');
		$registers = $model->getRegisters(true, true);

		$text = "";
		if (count($registers))
		{
			$fields = array();
			foreach ($registers[0]->fields AS $f)
			{
				$fields[] = $f;
			}
			$text .= $this->writecsvrow($fields);

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
				$text .= $this->writecsvrow($data);
			}
		}
		else
		{
			//$text = "no attendees";
		}
		if (!RedeventHelper::isValidDate($event->dates))
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
	}

	function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
	{
		$delimiter_esc = preg_quote($delimiter, '/');
		$enclosure_esc = preg_quote($enclosure, '/');

		$output = array();
		foreach ($fields as $field)
		{
			$output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
				$enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
			) : $field;
		}

		return join($delimiter, $output) . "\n";
	}
}
