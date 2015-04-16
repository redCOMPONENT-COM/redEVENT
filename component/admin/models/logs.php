<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Logs
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedEventModelLogs extends RModelList
{
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		$app = JFactory::getApplication();

		$contents = '';
		$file = $app->getCfg('log_path') . '/com_redevent.log';

		if (file_exists($file))
		{
			$handle = fopen($file, "r");

			if (!$handle)
			{
				$app->enqueueMessage('error opening: '. $file, 'warning');

				return false;
			}

			$contents = '';

			while (!feof($handle))
			{
				$contents .= fread($handle, 8192);
			}

			fclose($handle);
		}

		if (empty($contents))
		{
			$contents = array(JText::_('COM_REDEVENT_No_log'));
		}
		else
		{
			$contents = explode("\n", $contents);
			array_shift($contents);
		}

		return $contents;
	}
}
