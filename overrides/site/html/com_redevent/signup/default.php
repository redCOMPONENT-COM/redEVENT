<?php
/**
 * @version 1.0 $Id$
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
<?php if (isset($this->fullpage)): 
	?>
<div id="redevent" class="el_webformsignup">
<?php echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=details&id='.$this->course->did.'&xref='.$this->course->xref), JText::_('COM_REDEVENT_RETURN_EVENT_DETAILS'));
?>
	<p class="buttons">
	  <?php
	    echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	  ?>
	</p>

	<?php if ($this->params->get('show_page_title', true)) : ?>

	<h1 class="componentheading">
	<?php echo $this->escape($this->pagetitle); ?>
	</h1>

	<?php endif; ?>
<?php endif; ?>
<?php
echo $this->page;
?>
<?php if (isset($this->fullpage)): ?>
</div>
<?php endif; ?>
