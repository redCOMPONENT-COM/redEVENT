<?php
/**
 * @package     Aesir.Plugin
 * @subpackage  Aesir_Field.Redevent_session
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('reditem.library');
JLoader::registerPrefix('PlgAesir_FieldRedevent_session', __DIR__);

use Aesir\Plugin\AbstractFieldPlugin;
use Aesir\Entity\FieldInterface;

/**
 * Redevent_session field
 *
 * @since  1.0.0
 */
final class PlgAesir_FieldRedevent_session extends AbstractFieldPlugin
{
	/**
	 * Type for the form type="redevent_session" tag
	 *
	 * @var  string
	 */
	protected $formFieldType = 'PlgAesir_FieldRedevent_session.session';

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method for replace value tag of customfield
	 *
	 * @param   mixed   $field     Field object of customfield
	 * @param   string  &$content  HTML content
	 * @param   object  &$item     Item object
	 *
	 * @return  boolean
	 */
	public function onReditemFieldReplaceValueTag(FieldInterface $field, &$content, &$item)
	{
		if (!$field->isLoaded() || $field->type !== $this->_name)
		{
			return false;
		}

		if (empty($content) || empty($item))
		{
			return false;
		}

		$matches = array();

		if (preg_match_all('/{' . $field->fieldcode . '_value[^}]*}/i', $content, $matches) <= 0)
		{
			return false;
		}

		$matches = $matches[0];
		$value   = '';

		if (empty($field->value))
		{
			$customFieldValues = $item->customfield_values;

			if (isset($customFieldValues[$field->fieldcode]))
			{
				$value = $customFieldValues[$field->fieldcode];
			}
		}
		else
		{
			$value = $field->value;
		}

		foreach ($matches as $match)
		{
			$tagParams = explode('|', str_replace(array('{', '}'), '', $match));

			if (isset($tagParams[1]))
			{
				$value = JHTML::_('string.truncate', $value, (int) $tagParams[1], true, false);
			}

			$layoutData = array(
				'field' => $field,
				// Backward compatibility
				'tag'   => $field,
				'value' => $value,
				'item'  => $item
			);

			$contentHtml = $this->getFieldRenderer($field, 'view')->render($layoutData);

			$content = str_replace($match, $contentHtml, $content);
		}

		return true;
	}
}
