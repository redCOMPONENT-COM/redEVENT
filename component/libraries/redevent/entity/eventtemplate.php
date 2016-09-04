<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Event template entity.
 *
 * @since  3.1
 */
class RedeventEntityEventtemplate extends RedeventEntityBase
{
	/**
	 * Return associated redform form
	 *
	 * @return RdfEntityForm
	 */
	public function getForm()
	{
		$item = $this->getItem();

		if (!empty($item))
		{
			return RdfEntityForm::load($item->redform_id);
		}

		return false;
	}
}
