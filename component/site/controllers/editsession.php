<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Redevent Edit session controller
 *
 * @package  Redevent.Site
 * @since    3.0
 */
class RedeventControllerEditsession extends RControllerForm
{
	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return JRoute::_($returnUrl . $append, false);
		}
		else
		{
			return JRoute::_(RedeventHelperRoute::getMyeventsRoute() . $append, false);
		}
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 */
	public function edit($key = null, $urlVar = null)
	{
		$res = parent::edit('id', 's_id');

		return $res;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		return parent::save('id', 's_id');
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$e_id = $this->input->getInt('e_id');

		if ($e_id)
		{
			$append .= '&e_id=' . $e_id;
		}

		return $append;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   RModelAdmin  $model      The data model object.
	 * @param   array        $validData  The validated data.
	 *
	 * @return  void
	 */
	protected function postSaveHook(RModelAdmin $model, $validData = array())
	{
		$isNew = isset($validData['id']) && $validData['id'] ? false : true;

		// Update waiting list if not new
		if (!$isNew)
		{
			$model_wait = RModel::getAdminInstance('waitinglist');
			$xref = $model->getState($this->context . '.id');
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();
		}

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onSessionEdited', array($model->getState($this->context . '.id'), $isNew));

		parent::postSaveHook($model, $validData);
	}
}
