<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Parsed Tag container
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventTagsParsed
{
	/**
	 * full tag, including delimiters and parameters
	 *
	 * @var string
	 */
	protected $full_tag;

	/**
	 * tag name
	 *
	 * @var string
	 */
	protected $tag;

	/**
	 * array of parameters
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * constructor
	 *
	 * @param   string  $full_tag  full tag, including delimiters and parameters
	 *
	 * @throws Exception
	 */
	public function __construct($full_tag)
	{
		$this->full_tag = $full_tag;

		if (!preg_match('/\[([^\]\s]+)\s*([^\]]*)\]/u', $this->full_tag, $matches))
		{
			throw new Exception(JText::_('COM_REDEVENT_TAGS_WRONG_TAG_SYNTAX') . ':' . $this->full_tag);
		}

		$this->tag = trim($matches[1]);

		if (count($matches) > 2)
		{
			preg_match_all('/\s*([^=\s]+)=(")([^\'"]*)\2\s*/u', $matches[2], $match_params_array, PREG_SET_ORDER);

			foreach ($match_params_array as $m)
			{
				$this->params[] = array('key' => strtolower($m[1]), 'value' => $m[3]);
			}
		}
	}

	/**
	 * returns full tag text, including delimiters and parameters
	 *
	 * @return string
	 */
	public function getFullMatch()
	{
		return $this->full_tag;
	}

	/**
	 * returns tag name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->tag;
	}

	/**
	 * return tag parameter value
	 *
	 * @param   string  $name     parameter name
	 * @param   mixed   $default  default value if tag not found
	 *
	 * @return mixed value
	 */
	public function getParam($name, $default = null)
	{
		$values = array();

		foreach ($this->params as $param)
		{
			if ($name == $param['key'])
			{
				$values[] = $param['value'];
			}
		}

		if (empty($values))
		{
			return $default;
		}

		if (count($values) == 1)
		{
			return $values[0];
		}

		return $values;
	}
}
