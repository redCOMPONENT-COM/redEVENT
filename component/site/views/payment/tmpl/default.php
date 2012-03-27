<?php
/**
 * @version 1.0 $Id: default.php 1192 2009-10-14 19:38:01Z julien $
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
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="redevent" class="event_id<?php echo $this->row->eventid; ?> el_payment">
	<p class="buttons">
			<?php echo REOutput::printbutton( $this->print_link, $this->params ); ?>
	</p>
<div class="payment-result">
<?php echo $this->text; ?>
</div>
<?php echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=details&id='.$this->row->eventid.'&xref='.$this->row->xref), JText::_('COM_REDEVENT_RETURN_EVENT_DETAILS')); ?>
</div>