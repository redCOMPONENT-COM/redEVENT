<?php
/**
 * @package     redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license	    GNU General Public License version 2 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class RedeventsyncViewSync extends FOFViewHtml
{
	/**
	 * Executes before rendering a generic page, default to actions necessary
	 * for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onDisplay($tpl = null)
	{
		$view = $this->input->getCmd('view', 'cpanel');

		if (in_array($view, array('cpanel', 'cpanels', 'sync', 'syncs')))
		{
			return;
		}

		return parent::onDisplay($tp);
	}
}
