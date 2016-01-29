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

<p><?php echo Jtext::_('COM_REDEVENT_CUSTOMFIELDS_IMPORT_INTRO'); ?></p>
<form action="index.php" method="post" name="importform" id="importform"  enctype="multipart/form-data" >

<table class="adminlist">
	<tbody>
	<tr>
		<td class="label" width="150px"><?php echo JText::_('COM_REDEVENT_CUSTOMFIELDS_CSV_IMPORT_FILE'); ?></td>
		<td><input type="file" name="import" /><button type="submit"><?php echo JText::_('COM_REDEVENT_IMPORT')?></button></td>
	</tr>
	</tbody>
</table>

<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="customfields" />
<input type="hidden" name="task" value="doimport" />
</form>