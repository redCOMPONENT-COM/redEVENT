<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component text snippets Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerTextsnippets extends RControllerAdmin
{
	/**
	 * redirect to import
	 *
	 * @return void
	 */
	public function import()
	{
		$this->setRedirect('index.php?option=com_redevent&view=textsnippetsimport');
	}

	/**
	 * Import csv
	 *
	 * @return void
	 */
	public function doimport()
	{
		$app = JFactory::getApplication();
		$file = $app->input->files->get('import');
		$replace = $app->input->getInt('replace');
		$msgs = array();
		$this->setRedirect('index.php?option=com_redevent&view=textsnippets');

		if ($file)
		{
			try
			{
				$handle = fopen($file['tmp_name'], 'r');

				if (!$handle)
				{
					throw new Exception(JText::_('COM_REDEVENT_Cannot_open_uploaded_file'));
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
					$msg = "<p>Error parsing column names. Are you sure this is a proper csv export ?<br />";
					$msg .= "try to export first to get an example of formatting</p>\n";
					throw new Exception($msg);
				}

				$msgs[] = "<p>" . $numfields . " fields found in first row</p>\n";
				$msgs[] = "<p>" . count($fields) . " fields were kept</p>\n";

				// Now get the records, meaning the rest of the rows.
				$records = array();
				$row = 1;

				while (($data = fgetcsv($handle, 0, ',', '"')) !== false)
				{
					$num = count($data);

					if ($numfields != $num)
					{
						$msgs[] = "<p>Wrong number of fields ($num) record $row<br /></p>\n";
					}
					else
					{
						$r = array();

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
				$msgs[] = "<p>total records found: " . count($records) . "<br /></p>\n";

				// Database update
				if (count($records))
				{
					$model = $this->getModel('textsnippets');

					$added = 0;
					$updated = 0;
					$errors = 0;

					foreach ($records as $k => $record)
					{
						try
						{
							$res = $model->import($record, $replace);
							$added += $res['added'];
							$updated += $res['updated'];
						}
						catch (Exception $e)
						{
							$msgs[] = "<p class='error'>Error importing row " . $k . ": " . $e->getMessage() . "</p>\n";
							$errors++;
						}
					}

					$msgs[] = "<p>total added records: " . $added . "</p>\n";
					$msgs[] = "<p>total updated records: " . $updated . "</p>\n";
				}

				$msgType = $errors ? 'warning' :  '';
				$this->setMessage(implode("\n", $msgs), $msgType);
			}
			catch (Exception $e)
			{
				$this->setRedirect('index.php?option=com_redevent&view=textsnippetsimport&task=import', $e->getMessage(), 'error');
			}
		}
	}

	/**
	 * Cancel import
	 *
	 * @return void
	 */
	public function cancelimport()
	{
		$this->setRedirect('index.php?option=com_redevent&view=textsnippets');
	}
}
