<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelSimpleList extends RedeventModelSimpleListDefault
{
	/**
	 * Build the query
	 *
	 * @return string
	 */
	protected function _buildQuery()
	{
		$query = parent::_buildQuery();
		$query->select('x.session_language');

		return $query;
	}
}
