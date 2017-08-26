<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class RedeventHelperCountries
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventHelperCountries
{
	/**
	 * return countries array indexed by iso3 code
	 *
	 * @return array
	 */
	public static function getCountries()
	{
		static $country;

		if (is_null($country))
		{
			$lang = JFactory::getLanguage();
			$langIso2 = RedeventHelperLanguages::getIso2(substr($lang->getTag(), 0, 2));

			$data = json_decode(file_get_contents(__DIR__ . '/countries.json'));

			$country = array();

			foreach ($data as $row)
			{
				$name = isset($row->translations->$langIso2) ? $row->translations->$langIso2 : $row->name;
				$country[$row->cca3] = array('iso2' => $row->cca2, 'name' => $name->official, 'common' => $name->common, 'latlng' => $row->latlng);
			}
		}

		return $country;
	}

	/**
	 * Get country coordinate
	 *
	 * @return array
	 */
	public static function getCountrycoordArray()
	{
		$coord = array();

		foreach (self::getCountries() as $country)
		{
			$coord[$country['iso2']] = $country['latlng'];
		}

		return $coord;
	}

	/**
	 * Get options for countries
	 *
	 * @param   string  $value_tag   value tag
	 * @param   string  $text_tag    text tag
	 * @param   bool    $add_select  add select option
	 *
	 * @return array
	 */
	public static function getCountryOptions($value_tag = 'value', $text_tag = 'text', $add_select = false)
	{
		$options = array();

		if ($add_select)
		{
			$options[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_SELECT_COUNTRY'));
		}

		foreach (self::getCountries() AS $country)
		{
			$options[] = JHTML::_('select.option', $country['iso2'], $country['common'], $value_tag, $text_tag);
		}

		return $options;
	}

	/**
	 * Convert iso code 2 to 3
	 *
	 * @param   string  $iso_code_2  iso code 2
	 *
	 * @return string|boolean
	 */
	public static function convertIso2to3($iso_code_2)
	{
		foreach (self::getCountries() as $iso3 => $country)
		{
			if ($country['iso2'] == $iso_code_2)
			{
				return $iso3;
			}
		}

		return false;
	}

	/**
	 * Convert iso code 3 to 2
	 *
	 * @param   string  $iso_code_3  iso code 3
	 *
	 * @return string|boolean
	 */
	public static function convertIso3to2($iso_code_3)
	{
		$countries = self::getCountries();

		if (isset($countries[$iso_code_3]))
		{
			return $countries[$iso_code_3]['iso2'];
		}

		return false;
	}

	/**
	 * return flag url from iso code
	 *
	 * @param   string  $iso_code  iso code
	 *
	 * @return string url
	 */
	public static function getIsoFlag($iso_code)
	{
		if (strlen($iso_code) == 3)
		{
			$iso_code = self::convertIso3to2($iso_code);
		}

		if ($iso_code)
		{
			$path = RHelperAsset::image('com_redevent/flags/' . strtolower($iso_code) . '.gif', '', null, true, true);

			return $path;
		}
		else
		{
			return null;
		}
	}

	/**
	 * example: echo self::getCountryFlag($country);
	 *
	 * @param   string  $countrycode  an iso country code (2 or 3)
	 * @param   string  $attributes   additional html attributes for the img tag
	 *
	 * @return string: html code for the flag image
	 */
	public static function getCountryFlag($countrycode, $attributes = '')
	{
		$src = self::getIsoFlag($countrycode);

		if (!$src)
		{
			return '';
		}

		$html = '<img src="' . $src . '" alt="' . self::getCountryName($countrycode) . '" ';
		$html .= 'title="' . self::getCountryName($countrycode) . '" ' . $attributes . ' />';

		return $html;
	}

	/**
	 * get country name
	 *
	 * @param   string  $iso  an iso country code, e.g AUT
	 *
	 * @return string: a country name
	 */
	public static function getCountryName($iso)
	{
		if (!$country = self::getCountry($iso))
		{
			return false;
		}

		return $country['common'];
	}

	/**
	 * get country long name
	 *
	 * @param   string  $iso  an iso country code, e.g AUT or FR
	 *
	 * @return string: a country full name
	 */
	public static function getCountryFullName($iso)
	{
		if (!$country = self::getCountry($iso))
		{
			return false;
		}

		return $country['name'];
	}

	/**
	 * get country short name
	 *
	 * @param   string  $iso  an iso country code, e.g AUT or FR
	 *
	 * @return string: a country name, short form
	 */
	public static function getShortCountryName($iso)
	{
		return self::getCountryName($iso);
	}

	/**
	 * get country latitude
	 *
	 * @param   string  $iso  an iso country code, e.g AUT or FR
	 *
	 * @return float or false
	 */
	public static function getLatitude($iso)
	{
		if (strlen($iso) == 3)
		{
			$iso = self::convertIso3to2($iso);
		}

		$coords = self::getCountrycoordArray();

		if (isset($coords[$iso]))
		{
			return $coords[$iso][0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * get country longitude
	 *
	 * @param   string  $iso  an iso country code, e.g AUT or FR
	 *
	 * @return float or false
	 */
	public static function getLongitude($iso)
	{
		if (strlen($iso) == 3)
		{
			$iso = self::convertIso3to2($iso);
		}

		$coords = self::getCountrycoordArray();

		if (isset($coords[$iso]))
		{
			return $coords[$iso][1];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check if code is valid
	 *
	 * @param   string  $iso  iso code 2 or 3
	 *
	 * @return boolean
	 */
	public static function isValid($iso)
	{
		if (!(strlen($iso) == 2 || strlen($iso) == 3))
		{
			return false;
		}

		if (strlen($iso) == 2)
		{
			return static::convertIso2to3($iso) ? true : false;
		}
		else
		{
			return static::convertIso3to2($iso) ? true : false;
		}
	}

	/**
	 * Get country data
	 *
	 * @param   string  $iso  iso code 2 or 3
	 *
	 * @return boolean|mixed
	 *
	 * @since  __deploy_version__
	 */
	private static function getCountry($iso)
	{
		if (!self::isValid($iso))
		{
			return false;
		}

		if (strlen($iso) == 2)
		{
			$iso = self::convertIso2to3($iso);
		}

		$countries = self::getCountries();

		return $countries[$iso];
	}
}
