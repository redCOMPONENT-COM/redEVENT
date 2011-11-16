<?php
/**
 * @version 1.0 $Id: image.class.php 298 2009-06-24 07:42:35Z julien $
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

defined('_JEXEC') or die('Restricted access');

class redEVENTcustomHelper {
	
	/**
	 * returns a custom field object according to type
	 *
	 * @param string $type
	 * @return object
	 */
  function getCustomField($type)
  {
		require_once (JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'customfield'.DS.'includes.php');
    switch ($type)
    {
      case 'select':
        return new TCustomfieldSelect();
        break;
        
      case 'select_multiple':
        return new TCustomfieldSelectmultiple();
        break;
        
      case 'date':
        return new TCustomfieldDate();
        break;
        
      case 'radio':
        return new TCustomfieldRadio();
        break;
        
      case 'checkbox':
        return new TCustomfieldCheckbox();
        break;
        
      case 'textarea':
        return new TCustomfieldTextarea();
        break;
        
      case 'textbox':
      default:
        return new TCustomfieldTextbox();
        break;
    }
  }
}
?>