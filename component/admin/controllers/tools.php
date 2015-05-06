<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Tools Controller
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventControllerTools extends RControllerAdmin
{
	/**
	 * import eventlist events, categories, and venues.
	 *
	 * @return void
	 */
	public function importeventlist()
	{
		$model = $this->getModel('importeventlist');

		$result = $model->importeventlist();

		$link = 'index.php?option=com_redevent&view=tools';

		if (!$result)
		{
			$msg = $model->getError();
			$this->setRedirect($link, $msg, 'error');
		}
		else
		{
			$msg = JText::sprintf(
				'COM_REDEVENT_EVENTLIST_IMPORT_SUCCESS_D_EVENTS_D_CATEGORIES_D_VENUES',
				$result['events'],
				$result['categories'],
				$result['venues']
			);
			$this->setRedirect($link, $msg);
		}
	}

	/**
	 * triggers the autoarchive function
	 *
	 * @return void
	 */
	public function autoarchive()
	{
		RedeventHelper::cleanup(1);
		$msg = JText::_('COM_REDEVENT_AUTOARCHIVE_DONE');
		$link = 'index.php?option=com_redevent&view=tools';
		$this->setRedirect($link, $msg);
	}

	/**
	 * Add sample data
	 *
	 * @return void
	 */
	public function sampledata()
	{
		$model = $this->getModel('sample',  'RedeventModel');
		$model->create();
		$this->setRedirect('index.php?option=com_redevent', JText::_('COM_REDEVENT_Sample_data_created'));
	}
}
