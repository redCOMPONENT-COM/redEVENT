<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for Events screen
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewEventscsv extends RViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		return false;
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @throws  RuntimeException
	 */
	public function display($tpl = null)
	{
		/** @var RModelList $model */
		$model = $this->getModel();

		// For additional filtering and formating if needed
		$model->setState('streamOutput', 'csv');

		// Prepare the items
		$items = $model->getItems();
		$csvLines[0] = array_keys(current($items));
		$csvLines = array_merge($csvLines, $items);

		// Get the file name
		$fileName = $this->getFileName();
		setlocale(LC_ALL, $this->localeEncoding);

		// Send the headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-type: text/csv; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"$fileName.csv\";");
		header("Content-Transfer-Encoding: binary");

		// Send the csv
		$stream = @fopen('php://output', 'w');

		if (!is_resource($stream))
		{
			throw new RuntimeException('Failed to open the output stream');
		}

		foreach ($csvLines as $line)
		{
			fputcsv($stream, $line, $this->delimiter, $this->enclosure);
		}

		fclose($stream);

		JFactory::getApplication()->close();
	}

	/**
	 * Get the csv file name.
	 *
	 * @return  string  The file name.
	 */
	protected function getFileName()
	{
		$date = md5(date('Y-m-d-h-i-s'));
		$fileName = 'events_' . $date;

		return $fileName;
	}
}
