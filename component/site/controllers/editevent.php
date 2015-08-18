<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Edit event controller
 *
 * @package  Redevent.Site
 * @since    3.0
 */
class RedeventControllerEditevent extends RControllerForm
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
	 * Get the JRoute object for a redirect to item.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToItemRoute($append = null)
	{
		return JRoute::_(
			'index.php?option=' . $this->option . '&view=' . $this->view_item
			. $append, false
		);
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
		$res = parent::edit('id', 'e_id');

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
		return parent::save('id', 'e_id');
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

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onEventEdited', array($model->getState($this->context . '.id'), $isNew));

		if ($isNew)
		{
			$useracl = RedeventUserAcl::getInstance();

			if ($useracl->canAddSession())
			{
				$this->setRedirect(RedeventHelperRoute::getEditXrefRoute($model->getState($this->context . '.id')));
				$this->setMessage(JText::_('COM_REDEVENT_EVENT_SAVED_PLEASE_CREATE_SESSION'), 'success');
			}
		}

		parent::postSaveHook($model, $validData);
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

		$itemId = $this->input->get('Itemid') ? $this->input->get('Itemid') : RedeventHelperRoute::getViewItemId('editevent');

		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}

		return $append;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();

		if ($user->authorise('re.editevent', $this->option))
		{
			return true;
		}

		// Check own item
		if ($user->authorise('core.edit.own', $this->option))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;

			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $user->id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 */
	protected function allowAdd($data = array())
	{
		return JFactory::getUser()->authorise('re.createevent', $this->option);
	}

	/**
	 * Delete a session
	 *
	 * @return void
	 */
	public function delete()
	{
		$acl = RedeventUserAcl::getInstance();
		$id = $this->input->getInt('id');

		$return = $this->input->getBase64('return')
			? base64_decode($this->input->getBase64('return'))
			: JRoute::_(RedeventHelperRoute::getMyEventsRoute());

		$model = $this->getModel('editevent');
		$pks = array($id);

		if ($model->delete($pks))
		{
			$msg = JText::_('COM_REDEVENT_EVENT_DELETED');
			$this->setRedirect($return, $msg);
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_EVENT_DELETE_ERROR') . '<br>' . $model->getError();
			$this->setRedirect($return, $msg, 'error');
		}
	}
}
