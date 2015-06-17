<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Customfields Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerCustomfields extends RControllerAdmin
{
	/**
	 * display import screen
	 *
	 * @return void
	 */
	public function import()
	{
		$this->layout = 'import';

		parent::display();
	}

	/**
	 * export
	 *
	 * @return void
	 */
	public function export()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('customfields');
		$rows = $model->export();

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=customfields.csv');
		header('Pragma: no-cache');

		$export = '';

		if (count($rows))
		{
			$header = current($rows);
			$export .= RedeventHelper::writecsvrow(array_keys($header));

			foreach ($rows as $data)
			{
				$export .= RedeventHelper::writecsvrow($data);
			}

			echo $export;
		}

		$app->close();
	}

	/**
	 * do the import
	 *
	 * @return void
	 */
	private function doimport()
	{
		$replace = $this->input->get('replace', 0, 'post', 'int');

		$msg = '';

		if ($file = $this->input->get('import', null, 'files', 'array'))
		{
			$handle = fopen($file['tmp_name'], 'r');

			if (!$handle)
			{
				$msg = JText::_('COM_REDEVENT_Cannot_open_uploaded_file.');
				$this->setRedirect('index.php?option=com_redevent&view=customfields&task=import', $msg, 'error');

				return;
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
				$msg .= "<p>Error parsing column names. Are you sure this is a proper csv export ?<br />";
				$msg .= "try to export first to get an example of formatting</p>\n";
				$this->setRedirect('index.php?option=com_redevent&view=customfields&task=import', $msg, 'error');

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
					$r = arrat();

					// Only extract columns with validated header, from previous step.
					foreach ($fields as $k => $v)
					{
						$r[$v] = $data[$k];
					}

					$records[] = $r;
				}

				$row++;
			}

			fclose($handle);
			$msg .= "<p>total records found: " . count($records) . "<br /></p>\n";

			// Database update
			if (count($records))
			{
				$model = $this->getModel('customfields');
				$result = $model->import($records, $replace);
				$msg .= "<p>total added records: " . $result['added'] . "<br /></p>\n";
				$msg .= "<p>total updated records: " . $result['updated'] . "<br /></p>\n";
			}

			$this->setRedirect('index.php?option=com_redevent&view=customfields&task=import', $msg);
		}
		else
		{
			$this->setRedirect('index.php?option=com_redevent&view=customfields&task=import');
		}
	}
}
