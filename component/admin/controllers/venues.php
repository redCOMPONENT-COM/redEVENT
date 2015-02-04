<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Venues Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerVenues extends RControllerAdmin
{
	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   JModelLegacy  $model  The data model object.
	 * @param   integer       $id     The validated data.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function postDeleteHook(JModelLegacy $model, $id = null)
	{
		parent::postDeleteHook($model, $id);

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();

		foreach ($id as $cid)
		{
			$dispatcher->trigger('onAfterVenueRemoved', array($cid));
		}
	}
}
