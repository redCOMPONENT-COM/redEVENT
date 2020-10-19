<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Languages helper
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventHelperLanguages
{
	/**
	 * return languages array indexed by iso 639-2 code
	 * must be defined in settings
	 *
	 * @return array indexed by iso-2
	 */
	private static function getAllCodes()
	{
		static $codes;

		if (is_null($codes))
		{
			$codes = array();
			$list = file_get_contents(__DIR__ . '/ISO-639-2_8859-1.txt');

			foreach (explode("\n", $list) as $line)
			{
				$parts = explode('|', $line);

				if (count($parts) <= 3)
				{
					continue;
				}

				$codes[$parts[0]] = array(
					'iso1' => $parts[2],
					'iso2' => $parts[0],
					'name' => $parts[3],
				);
			}
		}

		return $codes;
	}

	/**
	 * Get options
	 *
	 * @param   string  $value_tag   value key
	 * @param   string  $text_tag    text key
	 * @param   bool    $add_select  add 'select' option
	 * @param   array   $filter      array of iso2 codes for allowed languages
	 *
	 * @return array
	 */
	public static function getOptions($value_tag = 'value', $text_tag = 'text', $add_select = false, $filter = array())
	{
		$codes = self::getAllCodes();

		if (!empty($filter))
		{
			$codes = array_filter(
				$codes,
				function ($element) use ($filter)
				{
					return in_array($element['iso2'], $filter);
				}
			);
		}

		if ($add_select)
		{
			$options[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_SELECT'));
		}

		foreach ($codes AS $k => $c)
		{
			$options[] = JHTML::_('select.option', $c['iso2'], self::getName($c['iso2']), $value_tag, $text_tag);
		}

		return $options;
	}

	/**
	 * Get flag assocaited to language in config
	 *
	 * @param   string  $code  iso code
	 *
	 * @return mixed
	 */
	public static function getFlagUrl($code)
	{
		static $flags;

		if (is_null($flags))
		{
			$config = RedeventHelper::config();
			$def = $config->get('language_flags');

			if (!$def)
			{
				return false;
			}

			$flags = array();

			// Parse definitions
			foreach (explode("\n", $def) as $line)
			{
				if (strlen($line) && strpos($line, ';') && !(strpos($line, '#') === 0))
				{
					list($code, $url) = explode(';', $line);
					$flags[$code] = $url;
				}
			}
		}

		return isset($flags[$code]) ? $flags[$code] : false;
	}

	/**
	 * example: echo self::getFlag($code);
	 *
	 * @param   string  $code        iso code
	 * @param   array   $attributes  additional html attributes for the img tag
	 *
	 * @return   string: html code for the flag image
	 */
	public static function getFlag($code, $attributes = array())
	{
		$code = self::convertToIso2($code);
		$src = self::getFlagUrl($code);

		if (!$src)
		{
			if (isset($attributes['class']))
			{
				$attributes['class'] .= ' iso_language_no_flag';
			}
			else
			{
				$attributes['class'] = 'iso_language_no_flag';
			}

			return '<span ' . JArrayHelper::toString($attributes) . '>[' . $code . ']</span>';
		}

		$html = '<img src="' . $src . '" alt="' . self::getName($code) . '" ';
		$html .= 'title="' . self::getName($code) . '" ' . JArrayHelper::toString($attributes) . ' />';

		return $html;
	}

	/**
	 * example: echo self::getFlag($code);
	 *
	 * @param   string  $code        iso code
	 * @param   array   $attributes  additional html attributes for the img tag
	 *
	 * @return   string: html code for the flag image
	 */
	public static function getFormattedIso1($code, $attributes = array())
	{
		$code = self::convertToIso2($code);

		if (!$code)
		{
			return '';
		}

		$attributes['title'] = self::getName($code);

		return '<span ' . JArrayHelper::toString($attributes) . '>[' . self::getIso1($code) . ']</span>';
	}

	/**
	 * Get translated name (if not found in translation, fall back to english)
	 *
	 * @param   string  $iso  an iso code, e.g ENG
	 *
	 * @return string: a language name
	 */
	public static function getName($iso)
	{
		$code = self::convertToIso2($iso);

		$languageString = strtoupper('COM_REDEVENT_LANGUAGES_ISO_' . $code);

		if ($languageString == JText::_($languageString))
		{
			$codes = self::getAllCodes();

			if (isset($codes[$code]))
			{
				return $codes[$code]['name'];
			}
		}

		return JText::_($languageString);
	}

	/**
	 * Get translated name
	 *
	 * @param   string  $iso  an iso code, e.g ENG
	 *
	 * @return string: iso-1 code (2 letters)
	 */
	public static function getIso1($iso)
	{
		if (strlen($iso) == 2)
		{
			return $iso;
		}

		$codes = self::getAllCodes();

		if (isset($codes[$iso]))
		{
			return $codes[$iso]['iso1'];
		}

		return false;
	}

	/**
	 * Convert code to iso-2 (3 letters)
	 *
	 * @param   string  $iso  iso-1 code
	 *
	 * @return boolean
	 */
	private static function convertToIso2($iso)
	{
		if (strlen($iso) == 3)
		{
			return $iso;
		}

		$all = self::getAllCodes();

		foreach ($all as $language)
		{
			if ($language['iso1'] == $iso)
			{
				return $language['iso2'];
			}
		}

		return false;
	}
}
