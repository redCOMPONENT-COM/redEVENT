<?php
/**
 * @version 1.0 $Id: archive.php 30 2009-05-08 10:22:21Z roland $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
* Renders a Custom field, generic class
*
* @author Julien Vonthron <jlv@jlv-solutions.com>
* @package redevent
* @since 2.0
*/

require_once ('customfield'.DS.'textbox.php');
require_once ('customfield'.DS.'textarea.php');
require_once ('customfield'.DS.'date.php');
require_once ('customfield'.DS.'radio.php');
require_once ('customfield'.DS.'checkbox.php');
require_once ('customfield'.DS.'select.php');
require_once ('customfield'.DS.'selectmultiple.php');
require_once ('customfield'.DS.'wysiwyg.php');

class TCustomfield extends JObject {
	/**
	 * custom field id
	 *
	 * @var int
	 */
  var $id;
  /**
   * name to display as label
   *
   * @var string
   */
  var $name;
  /**
   * name to display as tag
   *
   * @var string
   */
  var $tag;
  /**
   * type of field
   *
   * @var string
   */
  var $type;
  /**
   * tooltip
   *
   * @var string
   */
  var $tips;
  /**
   * tooltip
   *
   * @var string
   */
  var $required;
  /**
   * options
   *
   * @var string
   */
  var $options;
  /**
   * min lenght
   *
   * @var int
   */
  var $min;
  /**
   * max length
   *
   * @var int
   */
  var $max;
  /**
   * value
   *
   * @var string
   */
  var $value;
  
 /**
   * Constructor
   *
   * @access protected
   */
  function __construct() {
  	
  }
  
  /**
   * returns element form html code
   *
   * @param array $attributes
   */
  function render($attributes = array()) {
		return;
	}
	
	/**
	 * bind properties to an object or array
	 *
	 * @param object or array $source
	 */
	function bind($source)
	{
		if (is_object($source))
		{
			$source = get_object_vars($source);
		}
		if (is_array($source))
		{
  		$obj_keys = array_keys(get_object_vars($this));
			foreach ($source AS $key => $value)
			{
				if (in_array($key, $obj_keys)) {
					$this->$key = $value;
				}
			}
		}
	}
	
	/**
	 * returns form field for filtering
	 *
	 * @param unknown_type $attributes
	 */
	function renderFilter($attributes = array(), $selected = null) {
		return 'no filter';
	}
	
	/**
	 * returns the value
	 *
	 */
	function renderValue()
	{
		return $this->value;
	}
	
	/**
	 * return the attributes array as a html tag property string
	 * @param $attributes
	 * @return string
	 */
	function attributesToString($attributes)
	{
		$res = array();
		foreach ((array) $attributes as $k => $v) {
			$res[] = $k.'="'.$v.'"';
		}
		return implode(' ', $res);
	}
}
