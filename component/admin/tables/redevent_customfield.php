<?php
/**
 * @version 1.0 $Id: cleanup.php 298 2009-06-24 07:42:35Z julien $
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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include library dependencies
jimport('joomla.filter.input');

/**
* Table class
*
* @package		Redevent
* @since 2.0
*/
class Redevent_customfield extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	/**
	 * field name
	 *
	 * @var string
	 */
  var $name;
  /**
   * tag name, must be unique
   *
   * @var string
   */
  var $tag;
  /**
   * field type
   *
   * @var string
   */
  var $type;
  /**
   * for tooltip
   *
   * @var string
   */
  var $tips;
  /**
   * is it searchable
   *
   * @var boolean
   */
  var $searchable;
  /**
   * show in lists ?
   *
   * @var boolean
   */
  var $in_lists;
  /**
   * allow to edit in frontend
   *
   * @var boolean
   */
  var $frontend_edit;
  /**
   * is the field required
   *
   * @var boolean
   */
  var $required;
  /**
   * object key (individual / round / ...)
   *
   * @var string
   */
  var $object_key;
  /**
   * for options
   *
   * @var string
   */
  var $options;  
  /**
   * min size (textbox...)
   *
   * @var int
   */
  var $min = 0;
  /**
   * max size (textbox...)
   *
   * @var int
   */
  var $max = 100;
  /**
   * field default value
   * 
   * @var string
   */
  var $default_value;
  
  
  var $checked_out;
  var $checked_out_time;    
  var $ordering;
  var $published;


	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db) {
		parent::__construct('#__redevent_fields', 'id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function check()
	{
		// check that there is only alphanumerics in tag ?
		
		// check tag unicity
		$exists = ELAdmin::checkTagExists($this->tag);
		
		if ($exists && !($exists->section == 'custom' && $exists->id == $this->id)) {
			$this->setError(JText::sprintf('COM_REDEVENT_ERROR_TAG_ALREADY_EXISTS', $exists->section));
			return false;
		}
		
		return true;
	}
}
