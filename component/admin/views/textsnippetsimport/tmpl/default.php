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

JHtml::_('behavior.formvalidation');
?>
<div class="well"><?php echo Jtext::_('COM_REDEVENT_TEXTLIBRARY_IMPORT_INTRO'); ?></div>
<form action="index.php?option=com_redevent&view=textsnippetsimport" class="form-validate form-horizontal" id="adminForm" method="post"
      name="adminForm" enctype="multipart/form-data">

	<div class="row-fluid">
		<div class="control-group">
			<div class="control-label">
				<label for="file"><?php echo JText::_('COM_REDEVENT_TEXTLIBRARY_CSV_IMPORT_FILE'); ?></label>
			</div>
			<div class="controls">
				<input type="file" name="import" class="required"/></button>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label for="replace"><?php echo JText::_('COM_REDEVENT_TEXTLIBRARY_CSV_IMPORT_FILE_REPLACE'); ?></label>
			</div>
			<div class="controls">
				<input type="checkbox" name="replace" value="1" />
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
