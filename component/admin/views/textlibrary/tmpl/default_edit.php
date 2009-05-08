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

defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tbody>
	<tr>
		<td><?php echo JText::_('TEXT_TAG'); ?></td>
		<td><input type="text" name="text_name" size="150" value="<?php echo $this->row->text_name; ?>" /></td>
	</tr>
	<tr>
		<td><?php echo JText::_('TEXT_DESCRIPTION'); ?></td>
		<td><input type="text" name="text_description" size="150" value="<?php echo $this->row->text_description; ?>" /></td>
	</tr>
	<tr>
		<td colspan="2"><?php echo JText::_('TEXT_FIELD'); ?></td>
	</tr>
	<tr>
		<td colspan="2"><?php echo $this->editor->display( 'text_field',  $this->row->text_field, '100%;', '550', '75', '20', array('pagebreak', 'readmore') ) ;?></td>
	</tr>
	</tbody>
	</table>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="controller" value="textlibrary" />
<input type="hidden" name="view" value="textlibrary" />
<input type="hidden" name="task" value="" />
</form>

<p class="copyright">
	<?php echo ELAdmin::footer( ); ?>
</p>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>