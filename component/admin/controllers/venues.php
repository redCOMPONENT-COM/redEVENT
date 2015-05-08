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
	 * The method => state map.
	 *
	 * @var  array
	 */
	protected $states = array(
		'publish' => 1,
		'unpublish' => 0,
		'archive' => -1
	);

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

	/**
	 * trigger save event for plugins
	 *
	 * @return void
	 */
	public function triggersave()
	{
		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

		foreach ($cid as $id)
		{
			$table = RTable::getAdminInstance('Venue');
			$table->load($id);
			$dispatcher->trigger('onContentAfterSave', array('com_redevent.venue', $table, false));
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
