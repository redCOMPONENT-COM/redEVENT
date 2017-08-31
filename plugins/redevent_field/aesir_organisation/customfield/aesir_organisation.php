<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

use Aesir\Core\Helper\ModelFinder;

// Load redITEM Library
\JLoader::import('reditem.library');

/**
 * Renders a select Custom field
 *
 * @package  Redevent.Library
 * @since    __deploy_version__
 */
class RedeventCustomfieldAesir_Organisation extends \RedeventCustomfieldSelect
{
	/**
	 * return options
	 *
	 * @return array
	 *
	 * @since  __deploy_version__
	 */
	protected function getOptions()
	{
		$modelState = array(
			'filter.state' => 1,
			'filter.access' => 1
		);

		$user = \JFactory::getUser();

		if (!$user->authorise('core.manage', 'com_reditem'))
		{
			$member = \ReditemEntityMember::getInstance();
			$member->loadActive();

			$modelState['filter.member'] = $member->isValid() ? $member->id : 0;
		}

		$items = ModelFinder::findAdmin('Organisations', 'com_reditem')->searchCollection($modelState);

		if (!$items)
		{
			return array();
		}

		$options = array(JHtml::_('select.option', '', JText::_('JSELECT')));

		foreach ($items as $organisation)
		{
			$options[] = JHtml::_('select.option', $organisation->id, $organisation->name);
		}

		return $options;
	}
}
