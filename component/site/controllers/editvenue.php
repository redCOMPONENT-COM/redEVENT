<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Edit venue controller
 *
 * @package  Redevent.Site
 * @since    3.0
 */
class RedeventControllerEditvenue extends RControllerForm
{
	/**
	 * Method to add a new record.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 */
	public function add()
	{
		if (!parent::add())
		{
			return false;
		}

		if ($isModal = $this->input->get('modal', 0))
		{
			// Redirect back to the edit screen.
			$this->setRedirect(
				$this->getRedirectToItemRoute(
					$this->getRedirectToItemAppend() . '&tmpl=component&modal=1&layout=modal&fieldId='
					. $this->input->get('fieldId')
				)
			);
		}

		return true;
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
		$dispatcher->trigger('onVenueEdited', array($model->getState($this->context . '.id'), $isNew));

		$venueid = $model->getState($this->context . '.id');

		if ($this->input->get('modal', 0))
		{
			$this->setRedirect(
				'index.php?option=com_redevent&view=editvenue&layout=modal_close&tmpl=component&fieldId=' . $this->input->get('fieldId')
				. '&venueId=' . $venueid . '&name=' . urlencode($validData['venue'])
			);
		}
		else
		{
			$config = RedeventHelper::config();

			if (!$this->input->get('return') && $config->get('redirect_after_front_edit') == "viewitem")
			{
				$venue = RedeventEntityVenue::load($venueid);

				if ($venue->published)
				{
					$this->setRedirect(RedeventHelperRoute::getVenueEventsRoute($venueid));
				}
			}
		}

		parent::postSaveHook($model, $validData);
	}

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
			return JRoute::_(RedeventHelperRoute::getVenuesRoute() . $append, false);
		}
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

		if ($itemId = $this->input->get('Itemid'))
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
		$acl = RedeventUserAcl::getInstance();

		return $acl->canEditVenue($recordId);
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
		$acl = RedeventUserAcl::getInstance();

		return $acl->canAddVenue();
	}
}
