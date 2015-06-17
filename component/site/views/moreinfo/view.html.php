<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the moreinfo View
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewMoreinfo extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_redevent');

		if (!$params->get('enable_moreinfo', 1))
		{
			echo Jtext::_('COM_REDEVENT_MOREINFO_ERROR_DISABLED_BY_ADMIN');

			return;
		}

		if ($this->getLayout() == 'final')
		{
			parent::display($tpl);
		}

		$xref = JFactory::getApplication()->input->getInt('xref');
		$user = JFactory::getUser();

		if (!$xref)
		{
			echo JText::_('COM_REDEVENT_MOREINFO_ERROR_MISSING_XREF');
		}

		RHelperAsset::load('site/moreinfo.css');

		$this->assign('xref', $xref);
		$this->assign('action', JRoute::_(RedeventHelperRoute::getMoreInfoRoute($xref)));
		$this->assignRef('user', $user);

		parent::display($tpl);
	}
}
