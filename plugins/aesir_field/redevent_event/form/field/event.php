<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Aesir_Field.Text
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

require_once JPATH_LIBRARIES . '/reditem/library.php';

use Aesir\Field\CustomField;

JLoader::import('redevent.bootstrap');
RedeventBootstrap::bootstrap();

/**
 * Text field.
 *
 * @since  1.0.0
 */
class PlgAesir_FieldRedevent_EventFormFieldEvent extends CustomField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'event';

	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();
		$data['options'] = $this->getEventOptions();

		return $data;
	}

	/**
	 * Get the layout paths.
	 *
	 * @return  array
	 */
	protected function getLayoutPaths()
	{
		$app = \JFactory::getApplication();

		$template  = $app->getTemplate();

		$fieldType = 'redevent_event';

		$baseAppPath = $app->isSite() ? JPATH_SITE : JPATH_ADMINISTRATOR;

		return array(
			JPATH_THEMES . "/" . $template . '/html/layouts/com_reditem/customfields/' . $fieldType,
			$baseAppPath . '/components/' . \JApplicationHelper::getComponentName() . '/layouts/customfields/' . $fieldType,
			$baseAppPath . '/components/' . \JApplicationHelper::getComponentName() . '/layouts',
			JPATH_SITE . '/plugins/aesir_field/' . $fieldType . '/layouts',
			JPATH_SITE . '/layouts'
		);
	}

	/**
	 * Get options
	 *
	 * @return array
	 */
	protected function getEventOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from('#__redevent_events')
			->order('title ASC');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (!$res)
		{
			return array();
		}

		return array_map(
			function($element)
			{
				return JHtml::_('select.option', $element->value, $element->text);
			},
			$res
		);
	}
}
