<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * redEVENT labels plugin
 *
 * @since  __deploy_version__
 */
class PlgSystemRedevent_Labels extends JPlugin
{
	/**
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * Intercepts task
	 *
	 * @return void
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$task = $input->get('task');

		if ($input->get('option') !== 'com_redevent')
		{
			return;
		}

		switch ($task)
		{
			case 'attendees.labels':
				exit('TBD');
		}
	}

	/**
	 * Override toolbar
	 *
	 * @param   RedeventViewAdmin  $view     the view object
	 * @param   RToolbar           $toolbar  the toolbar
	 *
	 * @return void
	 *
	 * @since  __deploy_version__
	 */
	public function onRedeventViewGetToolbar(RedeventViewAdmin $view, RToolbar &$toolbar)
	{
		if ($view instanceof RedeventViewAttendees)
		{
			$group = new RToolbarButtonGroup;
			$button = RToolbarBuilder::createStandardButton(
				'attendees.labels',
				JText::_('PLG_SYSTEM_REDEVENT_LABELS_BUTTON_GET_LABELS'), '', 'icon-print', false
			);
			$group->addButton($button);

			$toolbar->addGroup($group);
		}
	}
}
