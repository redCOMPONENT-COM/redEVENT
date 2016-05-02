<?php
/**
 * @package    Redeventsync.admin
 *
 * @copyright  Copyright (C) 2013 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * Class RedeventsyncViewSync
 *
 * @since  2.5
 */
class RedeventsyncViewSync extends ResyncView
{
	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDEVENTSYNC_PAGETITLE_VIEW_SYNC');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		return $toolbar;
	}
}
