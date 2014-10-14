<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

class RedeventHelperLanguages
{
	/**
	 * return languages array indexed by iso 639-2 code
	 * must be defined in settings
	 *
	 * @return array
	 */
	private static function getAllCodes()
	{
		$allcodes = array(
			"ab" => "Abkhazian",
			"abk" => "Abkhazian",
			"aa" => "Afar",
			"aar" => "Afar",
			"af" => "Afrikaans",
			"afr" => "Afrikaans",
			"sq" => "Albanian",
			"alb" => "Albanian",
			"sqi" => "Albanian",
			"am" => "Amharic",
			"amh" => "Amharic",
			"ar" => "Arabic",
			"ara" => "Arabic",
			"an" => "Aragonese",
			"arg" => "Aragonese",
			"hy" => "Armenian",
			"arm" => "Armenian",
			"hye" => "Armenian",
			"as" => "Assamese",
			"asm" => "Assamese",
			"ae" => "Avestan",
			"ave" => "Avestan",
			"ay" => "Aymara",
			"aym" => "Aymara",
			"az" => "Azerbaijani",
			"aze" => "Azerbaijani",
			"ba" => "Bashkir",
			"bak" => "Bashkir",
			"eu" => "Basque",
			"baq" => "Basque",
			"eus" => "Basque",
			"be" => "Belarusian",
			"bel" => "Belarusian",
			"bn" => "Bengali",
			"ben" => "Bengali",
			"bh" => "Bihari",
			"bih" => "Bihari",
			"bi" => "Bislama",
			"bis" => "Bislama",
			"bs" => "Bosnian",
			"bos" => "Bosnian",
			"br" => "Breton",
			"bre" => "Breton",
			"bg" => "Bulgarian",
			"bul" => "Bulgarian",
			"my" => "Burmese",
			"bur" => "Burmese",
			"mya" => "Burmese",
			"ca" => "Catalan",
			"cat" => "Catalan",
			"ch" => "Chamorro",
			"cha" => "Chamorro",
			"ce" => "Chechen",
			"che" => "Chechen",
			"zh" => "Chinese",
			"chi" => "Chinese",
			"zho" => "Chinese",
			"cu" => "Church Slavic, Slavonic, Old Bulgarian",
			"chu" => "Church Slavic, Slavonic, Old Bulgarian",
			"cv" => "Chuvash",
			"chv" => "Chuvash",
			"kw" => "Cornish",
			"cor" => "Cornish",
			"co" => "Corsican",
			"cos" => "Corsican",
			"hr" => "Croatian",
			"hrv" => "Croatian",
			"scr" => "Croatian",
			"cs" => "Czech",
			"cze" => "Czech",
			"ces" => "Czech",
			"da" => "Danish",
			"dan" => "Danish",
			"dv" => "Divehi, Dhivehi, Maldivian",
			"div" => "Divehi, Dhivehi, Maldivian",
			"nl" => "Dutch",
			"dut" => "Dutch",
			"nld" => "Dutch",
			"dz" => "Dzongkha",
			"dzo" => "Dzongkha",
			"en" => "English",
			"eng" => "English",
			"eo" => "Esperanto",
			"epo" => "Esperanto",
			"et" => "Estonian",
			"est" => "Estonian",
			"fo" => "Faroese",
			"fao" => "Faroese",
			"fj" => "Fijian",
			"fij" => "Fijian",
			"fi" => "Finnish",
			"fin" => "Finnish",
			"fr" => "French",
			"fre" => "French",
			"fra" => "French",
			"gd" => "Gaelic, Scottish Gaelic",
			"gla" => "Gaelic, Scottish Gaelic",
			"gl" => "Galician",
			"glg" => "Galician",
			"ka" => "Georgian",
			"geo" => "Georgian",
			"kat" => "Georgian",
			"de" => "German",
			"ger" => "German",
			"deu" => "German",
			"el" => "Greek, Modern",
			"gre" => "Greek, Modern",
			"ell" => "Greek, Modern",
			"gn" => "Guarani",
			"grn" => "Guarani",
			"gu" => "Gujarati",
			"guj" => "Gujarati",
			"ht" => "Haitian, Haitian Creole",
			"hat" => "Haitian, Haitian Creole",
			"ha" => "Hausa",
			"hau" => "Hausa",
			"he" => "Hebrew",
			"heb" => "Hebrew",
			"hz" => "Herero",
			"her" => "Herero",
			"hi" => "Hindi",
			"hin" => "Hindi",
			"ho" => "Hiri Motu",
			"hmo" => "Hiri Motu",
			"hu" => "Hungarian",
			"hun" => "Hungarian",
			"is" => "Icelandic",
			"ice" => "Icelandic",
			"isl" => "Icelandic",
			"io" => "Ido",
			"ido" => "Ido",
			"id" => "Indonesian",
			"ind" => "Indonesian",
			"ia" => "Interlingua",
			"ina" => "Interlingua",
			"ie" => "Interlingue",
			"ile" => "Interlingue",
			"iu" => "Inuktitut",
			"iku" => "Inuktitut",
			"ik" => "Inupiaq",
			"ipk" => "Inupiaq",
			"ga" => "Irish",
			"gle" => "Irish",
			"it" => "Italian",
			"ita" => "Italian",
			"ja" => "Japanese",
			"jpn" => "Japanese",
			"jv" => "Javanese",
			"jav" => "Javanese",
			"kl" => "Kalaallisut",
			"kal" => "Kalaallisut",
			"kn" => "Kannada",
			"kan" => "Kannada",
			"ks" => "Kashmiri",
			"kas" => "Kashmiri",
			"kk" => "Kazakh",
			"kaz" => "Kazakh",
			"km" => "Khmer",
			"khm" => "Khmer",
			"ki" => "Kikuyu, Gikuyu",
			"kik" => "Kikuyu, Gikuyu",
			"rw" => "Kinyarwanda",
			"kin" => "Kinyarwanda",
			"ky" => "Kirghiz",
			"kir" => "Kirghiz",
			"kv" => "Komi",
			"kom" => "Komi",
			"ko" => "Korean",
			"kor" => "Korean",
			"kj" => "Kuanyama, Kwanyama",
			"kua" => "Kuanyama, Kwanyama",
			"ku" => "Kurdish",
			"kur" => "Kurdish",
			"lo" => "Lao",
			"lao" => "Lao",
			"la" => "Latin",
			"lat" => "Latin",
			"lv" => "Latvian",
			"lav" => "Latvian",
			"li" => "Limburgan, Limburger, Limburgish",
			"lim" => "Limburgan, Limburger, Limburgish",
			"ln" => "Lingala",
			"lin" => "Lingala",
			"lt" => "Lithuanian",
			"lit" => "Lithuanian",
			"lb" => "Luxembourgish, Letzeburgesch",
			"ltz" => "Luxembourgish, Letzeburgesch",
			"mk" => "Macedonian",
			"mac" => "Macedonian",
			"mkd" => "Macedonian",
			"mg" => "Malagasy",
			"mlg" => "Malagasy",
			"ms" => "Malay",
			"may" => "Malay",
			"msa" => "Malay",
			"ml" => "Malayalam",
			"mal" => "Malayalam",
			"mt" => "Maltese",
			"mlt" => "Maltese",
			"gv" => "Manx",
			"glv" => "Manx",
			"mi" => "Maori",
			"mao" => "Maori",
			"mri" => "Maori",
			"mr" => "Marathi",
			"mar" => "Marathi",
			"mh" => "Marshallese",
			"mah" => "Marshallese",
			"mo" => "Moldavian",
			"mol" => "Moldavian",
			"mn" => "Mongolian",
			"mon" => "Mongolian",
			"na" => "Nauru",
			"nau" => "Nauru",
			"nv" => "Navaho, Navajo",
			"nav" => "Navaho, Navajo",
			"nd" => "Ndebele, North",
			"nde" => "Ndebele, North",
			"nr" => "Ndebele, South",
			"nbl" => "Ndebele, South",
			"ng" => "Ndonga",
			"ndo" => "Ndonga",
			"ne" => "Nepali",
			"nep" => "Nepali",
			"se" => "Northern Sami",
			"sme" => "Northern Sami",
			"no" => "Norwegian",
			"nor" => "Norwegian",
			"nb" => "Norwegian Bokmal",
			"nob" => "Norwegian Bokmal",
			"nn" => "Norwegian Nynorsk",
			"nno" => "Norwegian Nynorsk",
			"ny" => "Nyanja, Chichewa, Chewa",
			"nya" => "Nyanja, Chichewa, Chewa",
			"oc" => "Occitan, Provencal",
			"oci" => "Occitan, Provencal",
			"or" => "Oriya",
			"ori" => "Oriya",
			"om" => "Oromo",
			"orm" => "Oromo",
			"os" => "Ossetian, Ossetic",
			"oss" => "Ossetian, Ossetic",
			"pi" => "Pali",
			"pli" => "Pali",
			"pa" => "Panjabi",
			"pan" => "Panjabi",
			"fa" => "Persian",
			"per" => "Persian",
			"fas" => "Persian",
			"pl" => "Polish",
			"pol" => "Polish",
			"pt" => "Portuguese",
			"por" => "Portuguese",
			"ps" => "Pushto",
			"pus" => "Pushto",
			"qu" => "Quechua",
			"que" => "Quechua",
			"rm" => "Raeto-Romance",
			"roh" => "Raeto-Romance",
			"ro" => "Romanian",
			"rum" => "Romanian",
			"ron" => "Romanian",
			"rn" => "Rundi",
			"run" => "Rundi",
			"ru" => "Russian",
			"rus" => "Russian",
			"sm" => "Samoan",
			"smo" => "Samoan",
			"sg" => "Sango",
			"sag" => "Sango",
			"sa" => "Sanskrit",
			"san" => "Sanskrit",
			"sc" => "Sardinian",
			"srd" => "Sardinian",
			"sr" => "Serbian",
			"scc" => "Serbian",
			"srp" => "Serbian",
			"sn" => "Shona",
			"sna" => "Shona",
			"ii" => "Sichuan Yi",
			"iii" => "Sichuan Yi",
			"sd" => "Sindhi",
			"snd" => "Sindhi",
			"si" => "Sinhala, Sinhalese",
			"sin" => "Sinhala, Sinhalese",
			"sk" => "Slovak",
			"slo" => "Slovak",
			"slk" => "Slovak",
			"sl" => "Slovenian",
			"slv" => "Slovenian",
			"so" => "Somali",
			"som" => "Somali",
			"st" => "Sotho, Southern",
			"sot" => "Sotho, Southern",
			"es" => "Spanish, Castilian",
			"spa" => "Spanish, Castilian",
			"su" => "Sundanese",
			"sun" => "Sundanese",
			"sw" => "Swahili",
			"swa" => "Swahili",
			"ss" => "Swati",
			"ssw" => "Swati",
			"sv" => "Swedish",
			"swe" => "Swedish",
			"tl" => "Tagalog",
			"tgl" => "Tagalog",
			"ty" => "Tahitian",
			"tah" => "Tahitian",
			"tg" => "Tajik",
			"tgk" => "Tajik",
			"ta" => "Tamil",
			"tam" => "Tamil",
			"tt" => "Tatar",
			"tat" => "Tatar",
			"te" => "Telugu",
			"tel" => "Telugu",
			"th" => "Thai",
			"tha" => "Thai",
			"bo" => "Tibetan",
			"tib" => "Tibetan",
			"bod" => "Tibetan",
			"ti" => "Tigrinya",
			"tir" => "Tigrinya",
			"to" => "Tonga",
			"ton" => "Tonga",
			"ts" => "Tsonga",
			"tso" => "Tsonga",
			"tn" => "Tswana",
			"tsn" => "Tswana",
			"tr" => "Turkish",
			"tur" => "Turkish",
			"tk" => "Turkmen",
			"tuk" => "Turkmen",
			"tw" => "Twi",
			"twi" => "Twi",
			"ug" => "Uighur",
			"uig" => "Uighur",
			"uk" => "Ukrainian",
			"ukr" => "Ukrainian",
			"ur" => "Urdu",
			"urd" => "Urdu",
			"uz" => "Uzbek",
			"uzb" => "Uzbek",
			"vi" => "Vietnamese",
			"vie" => "Vietnamese",
			"vo" => "Volapuk",
			"vol" => "Volapuk",
			"wa" => "Walloon",
			"wln" => "Walloon",
			"cy" => "Welsh",
			"wel" => "Welsh",
			"cym" => "Welsh",
			"fy" => "Western Frisian",
			"fry" => "Western Frisian",
			"wo" => "Wolof",
			"wol" => "Wolof",
			"xh" => "Xhosa",
			"xho" => "Xhosa",
			"yi" => "Yiddish",
			"yid" => "Yiddish",
			"yo" => "Yoruba",
			"yor" => "Yoruba",
			"za" => "Zhuang, Chuang",
			"zha" => "Zhuang, Chuang",
			"zu" => "Zulu",
			"zul" => "Zulu"
		);

		return $allcodes;
	}

	/**
	 * Get all 3 letters codes
	 *
	 * @return array
	 */
	private static function get3lettersCodes()
	{
		$all = self::getAllCodes();

		$keys3 = array_filter(
			array_keys($all),
			function($key)
			{
				return strlen($key) == 3;
			}
		);

		$all3 = array_intersect_key($all, array_flip($keys3));

		return $all3;
	}

	/**
	 * Get options
	 *
	 * @param   string  $value_tag   value key
	 * @param   string  $text_tag    text key
	 * @param   bool    $add_select  add 'select' option
	 *
	 * @return array
	 */
	public static function getOptions($value_tag = 'value', $text_tag = 'text', $add_select = false)
	{
		$codes = self::get3lettersCodes();
		$options = array();

		if ($add_select)
		{
			$options[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_SELECT'));
		}

		foreach ($codes AS $k => $c)
		{
			$options[] = JHTML::_('select.option', $k, self::getName($k), $value_tag, $text_tag);
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
	 * Get translated name
	 *
	 * @param   string  $iso  an iso code, e.g ENG
	 *
	 * @return string: a language name
	 */
	public static function getName($iso)
	{
		$codes = self::get3lettersCodes();

		if ('COM_REDEVENT_LANGUAGES_ISO_' . $iso == JText::_('COM_REDEVENT_LANGUAGES_ISO_' . $iso))
		{
			if (isset($codes[$iso]))
			{
				return $codes[$iso];
			}
		}

		return JText::_('COM_REDEVENT_LANGUAGES_ISO_' . $iso);
	}
}
