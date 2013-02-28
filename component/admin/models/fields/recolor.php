<?php
/**
 * @version 2.5
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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.form.formfield');
 
/**
 * Session form field class
 */
class JFormFieldRecolor extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'recolor';
	
	/**
	 * display reset button
	 * @var boolean
	 */
	protected $reset;
	
	/**
	* Method to get the field input markup
	*/
	protected function getInput()
	{
		// js color picker
		FOFTemplateUtils::addCSS('media://com_redevent/css/colorpicker.css');
		FOFTemplateUtils::addJS('media://com_redevent/js/colorpicker.js');
		
		$html = '
		<input class="inputbox" type="text" style="background: '.( $this->value == '' ? "transparent" :$this->value).'"
                     name="color" id="color" size="10" maxlength="20" value="'.$this->value.'" />                   
        <input type="button" class="button" value="'.JText::_('COM_REDEVENT_PICK' ).'" onclick="openPicker(\'color\', -200, 20);" />
		';
			
		return $html;
	}
}