<?php
/**
 * @package     redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license	    GNU General Public License version 2 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

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
