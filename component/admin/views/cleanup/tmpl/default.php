<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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
?>
<table cellspacing="0" cellpadding="4" border="0" width="100%">
	<tr>
		<td width="10%">
			<div class="linkicon">
				<a href="index.php?option=com_redevent&amp;controller=cleanup&amp;task=cleaneventimg">
					<?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_( 'CLEANUP EVENT IMG' ) ); ?>
					<span><?php echo JText::_( 'CLEANUP EVENT IMG' ); ?></span>
				</a>
			</div>
		</td>
		<td width="40%" valign="middle">
			<?php echo JText::_( 'CLEANUP EVENT IMG DESC' ); ?>
		</td>
		<td width="10%">
			<div class="linkicon">
				<a href="index.php?option=com_redevent&amp;controller=cleanup&amp;task=cleanvenueimg">
					<?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleanvenueimg.png',  JText::_( 'CLEANUP VENUE IMG' ) ); ?>
					<span><?php echo JText::_( 'CLEANUP VENUE IMG' ); ?></span>
				</a>
			</div>
		</td>
		<td width="40%" valign="middle">
			<?php echo JText::_( 'CLEANUP VENUE IMG DESC' ); ?>
		</td>
</table>