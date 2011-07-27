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
* Renders a Textbox Custom field
*
 * @package		redEVENT
 * @since 2.0
*/

class TCustomfieldSelectmultiple extends TCustomfield {
 
  /**
  * Element name
  *
  * @access protected
  * @var    string
  */
  var $_name = 'select';

  /**
   * returns the html code for the form element
   *
   * @param array $attributes
   * @return string
   */
  function render($attributes = array())
  {    
  	if ($this->required) 
  	{
  		if (isset($attributes['class'])) {
  			$attributes['class'] .= ' required';
  		}
  		else {
  			$attributes['class'] = 'required';
  		}
  	}
  	
    $option_list = array();
    $options = explode("\n", $this->options);
    if ($options) 
    {
    	foreach ($options as $opt) {
    		$opt = trim($opt);
    		$option_list[] = JHTML::_('select.option', $opt, $opt);
    	}    	
    }
  
    // selected options
    $values = explode("\n", $this->value);    
    $default_values = explode("\n", $this->default_value);
    
    $default = array();
    foreach ($default_values as $d)
    {
    	$d = trim($d);
    	if (!empty($d)) {
    		$default[] = $d;
    	}
    }
    if (!is_null($this->value))
    {
    	$selected = $values;
    }
    else
    {
    	$selected = $default;
    }
    
    return JHTML::_('select.genericlist', $option_list, 'custom'.$this->id.'[]', 'multiple="multiple" size="'.min(10,count($options)).'" '.$this->attributesToString($attributes), 'value', 'text', $selected);
  }

  function renderFilter($attributes = array(), $selected = null) 
  {
    $app = & JFactory::getApplication();
  
    if ($selected) {
      $value = $selected;
    }
    else {
      $value = '';
    }
    
    $option_list = array();
    $option_list[] = JHTML::_('select.option', '', JText::_('Select'));
    $options = explode("\n", $this->options);
    if ($options) 
    {
      foreach ($options as $opt) {
        $opt = trim($opt);
        $option_list[] = JHTML::_('select.option', $opt, $opt);
      }     
    }
    return JHTML::_('select.genericlist', $option_list, 'filtercustom['.$this->id.']', $this->attributesToString($attributes), 'value', 'text', $value);  
  }
}
