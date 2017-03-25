<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component featured events list Model
 *
 * @package     Joomla
 * @subpackage  Redevent
 * @since       2.5
 */
class RedeventModelFeatured extends RedeventModelBasesessionlist
{
	/**
	 * Build the where clause
	 *
	 * @param   object  $query  query
	 *
	 * @return object
	 */
	protected function buildWhere($query)
	{
		$query = parent::buildWhere($query);
		$query->where('x.featured = 1');

		return $query;
	}
}
