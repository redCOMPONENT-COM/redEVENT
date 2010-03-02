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
* @author Julien Vonthron <jlv@jlv-solutions.com>
* @package Tracks
* @since 0.5
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
  function render($attributes = '')
  {    
  	if ($this->required) {
  		$attribs = 'class="required"';
  	}
  	else {
  		$attribs = null;
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
    return JHTML::_('select.genericlist', $option_list, 'custom'.$this->id.'[]', 'multiple="multiple" size="'.min(10,count($options)).'" '.$attribs, 'value', 'text', explode("\n",$this->value));
  }

  function renderFilter($attributes = '') 
  {
    $app = & JFactory::getApplication();
    
    // the filtered value should be stored in session
    $customs = $app->getUserState('com_redevent.venuesmap.customs');
    if (is_array($customs) && isset($customs[$this->id])) {
      $value = $customs[$this->id];
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
    return JHTML::_('select.genericlist', $option_list, 'filtercustom['.$this->id.']', $attributes, 'value', 'text', $value);  
  }
}
?>