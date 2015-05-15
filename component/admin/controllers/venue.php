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
class RedeventControllerVenue extends RControllerForm
{
	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   object  &$model     The data model object.
	 * @param   array   $validData  The validated data.
	 *
	 * @return  void
	 */
	protected function postSaveHook(&$model, $validData = array())
	{
		parent::postSaveHook($model, $validData);

		// It's in fact better to use onContentAfterSave event....
		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterVenueSaved', array($model->getState($this->context . '.id')));
	}
}
