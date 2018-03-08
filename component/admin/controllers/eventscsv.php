<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Events csv export/import Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerEventscsv extends RControllerForm
{
	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  void
	 */
	public function edit($key = null, $urlVar = null)
	{
		$this->setRedirect('index.php?option=com_redevent&view=eventscsv');
	}

	/**
	 * Import data
	 *
	 * @return void
	 */
	public function import()
	{
		$jformFiles = JFactory::getApplication()->input->files->get('jform');
		$jformPost = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (!isset($jformFiles['import']))
		{
			$msg = JText::_('COM_REDEVENT_IMPORT_ERROR_MISSING_FILE');
			$this->setRedirect('index.php?options=com_redevent&view=eventscsv', $msg, 'error');
			$this->redirect();
		}

		$file = $jformFiles['import'][0];

		$duplicate_method = $jformPost['duplicate_method'];

		$handle = fopen($file['tmp_name'], 'r');
		$msg = '';

		if (!$handle)
		{
			$msg = JText::_('COM_REDEVENT_Cannot_open_uploaded_file.');
			$this->setRedirect('index.php?option=com_redevent&view=eventscsv', $msg, 'error');
			$this->redirect();
		}

		// Get fields, on first row of the file
		$fields = array();

		if (($data = fgetcsv($handle, 0, ',', '"')) !== false)
		{
			$numfields = count($data);

			for ($c = 0; $c < $numfields; $c++)
			{
				$fields[$c] = $data[$c];
			}
		}

		// If there is no validated fields, there is a problem...
		if (!count($fields))
		{
			$msg .= "<p>Error parsing column names. Are you sure this is a proper csv export ?<br />"
				. "try to export first to get an example of formatting</p>\n";
			$this->setRedirect('index.php?option=com_redevent&view=eventscsv', $msg, 'error');

			return;
		}
		else
		{
			$msg .= "<p>" . $numfields . " fields found in first row</p>\n";
			$msg .= "<p>" . count($fields) . " fields were kept</p>\n";
		}

		// Now get the records, meaning the rest of the rows.
		$records = array();
		$row = 1;

		while (($data = fgetcsv($handle, 0, ',', '"')) !== false)
		{
			$num = count($data);

			if ($numfields != $num)
			{
				$msg .= "<p>Wrong number of fields ($num) record $row<br /></p>\n";
			}
			else
			{
				$r = array();

				// Only extract columns with validated header, from previous step.
				foreach ($fields as $k => $v)
				{
					$r[$v] = $this->formatcsvfield($v, $data[$k]);
				}

				$records[] = $r;
			}

			$row++;
		}

		fclose($handle);
		$msg .= "<p>total records found: " . count($records) . "<br /></p>\n";

		$msgType = 'message';

		// Database update
		if (count($records))
		{
			$model = $this->getModel('eventscsvimport');
			$result = $model->import($records, $duplicate_method);

			if ($errors = $model->getErrorMessages())
			{
				$msgType = 'warning';

				foreach ($errors as $errorMsg)
				{
					$msg .= "<p>" . $errorMsg . "</p>\n";
				}
			}

			$msg .= "<p>total added events: " . $result['added'] . "</p>\n";
			$msg .= "<p>total updated events: " . $result['updated'] . "</p>\n";
			$msg .= "<p>total ignored events: " . $result['ignored'] . "</p>\n";

			$msg .= "<p>total added sessions: " . $result['addedSessions'] . "</p>\n";
			$msg .= "<p>total updated sessions: " . $result['updatedSessions'] . "</p>\n";
			$msg .= "<p>total ignored sessions: " . $result['ignoredSessions'] . "</p>\n";
		}

		$this->setRedirect('index.php?option=com_redevent&view=eventscsv', $msg, $msgType);
	}

	/**
	 * handle specific fields conversion if needed
	 *
	 * @param   string  $type   column name
	 * @param   string  $value  value
	 *
	 * @return string
	 */
	private function formatcsvfield($type, $value)
	{
		switch ($type)
		{
			case 'dates':
			case 'enddates':
			case 'registrationend':
				if (strtotime($value))
				{
					// Strtotime does a good job in converting various date formats...
					$field = strftime('%Y-%m-%d', strtotime($value));
				}
				else
				{
					$field = null;
				}
				break;

			default:
				$field = $value;
				break;
		}

		return $field;
	}
}
