<?php
/**
 * @package     Redevent.Site
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Bundle Model
 *
 * @package     Redevent.Site
 * @subpackage  Models
 * @since       3.2.0
 */
class RedeventModelBundle extends RModel
{
	/**
	 * @var RedeventEntityBundle
	 */
	protected $item = null;

	/**
	 * Get data
	 *
	 * @param   int  $pk  key to load
	 *
	 * @return RedeventEntityBundle
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

		return RedeventEntityBundle::load($pk);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$pk = JFactory::getApplication()->input->getInt('id');
		$this->setState($this->getName() . '.id', $pk);

		parent::populateState();
	}
}
