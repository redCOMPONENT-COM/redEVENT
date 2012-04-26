<?php
/**
 * @version 1.0 $Id: event.php 230 2009-06-10 09:17:42Z julien $
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
defined('_JEXEC') or die();

/**
 * Renders an Event element
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */

class JElementXref extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'xref';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$doc 		=& JFactory::getDocument();
		JHTML::_('behavior.mootools');
		$fieldName	= $control_name.'['.$name.']';

		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redevent'.DS.'tables');

		$event =& JTable::getInstance('redevent_events', '');
		if ($value) {
			$event->xload($value);
		} else {
			$event->title = JText::_('COM_REDEVENT_SELECTEVENT');
		}

		$js = "
		function selectXref(xref, title, id) {
			$('a_id').value = xref;
			$('a_name').value = title;
			$('sbox-window').close();
			var els = $$('.event-id');
			if (els) {
				els[0].value = id;
			}
		}";

		$link = 'index.php?option=com_redevent&amp;view=xrefelement&amp;tmpl=component';
		$doc->addScriptDeclaration($js);

		JHTML::_('behavior.modal', 'a.modal');

		$html = "\n<div style=\"float: left;\"><input style=\"background: #ffffff;\" type=\"text\" id=\"a_name\" value=\"$event->title\" disabled=\"disabled\" /></div>";
		$html .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".JText::_('COM_REDEVENT_Select')."\"  href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".JText::_('COM_REDEVENT_Select')."</a></div></div>\n";
		$html .= "\n<input type=\"hidden\" id=\"a_id\" name=\"$fieldName\" value=\"$value\" />";

		return $html;
	}
}
