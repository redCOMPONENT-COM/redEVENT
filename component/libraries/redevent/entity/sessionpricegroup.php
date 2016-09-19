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
 * Session pricegroup entity.
 *
 * @since  1.0
 */
class RedeventEntitySessionpricegroup extends RedeventEntityBase
{
	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __get($property)
	{
		switch ($property)
		{
			case 'currency':
				return parent::__get($property) ?: $this->getSession()->getEvent()->getForm()->currency;
		}

		return parent::__get($property);
	}

	/**
	 * Return associated event
	 *
	 * @return RedeventEntityEvent
	 */
	public function getPricegroup()
	{
		$item = $this->getItem();

		if (!empty($item))
		{
			return RedeventEntityPricegroup::load($item->pricegroup_id);
		}

		return false;
	}

	/**
	 * Return associated event
	 *
	 * @return RedeventEntitySession
	 */
	public function getSession()
	{
		$item = $this->getItem();

		if (!empty($item))
		{
			return RedeventEntitySession::load($item->xref);
		}

		return false;
	}

	/**
	 * Get the associated table
	 *
	 * @param   string  $name  Main name of the Table. Example: Article for ContentTableArticle
	 *
	 * @return  RTable
	 */
	protected function getTable($name = null)
	{
		if (null === $name)
		{
			$name = 'sessionpricegroup';
		}

		return RTable::getAdminInstance($name, array(), $this->getComponent());
	}
}
