<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

extract($displayData);
?>
<!-- Button to trigger modal -->
<a href="#tagsModal<?= $field->id ?>" role="button" class="btn" data-toggle="modal"><?= JText::_('COM_REDEVENT_TAGS_TITLE') ?></a>

<!-- Modal -->
<div id="tagsModal<?= $field->id ?>" class="modal hide fade tagInsertModal" tabindex="-1" role="dialog" aria-labelledby="tagsModalLabel<?= $field->id ?>" aria-hidden="true" field="<?= $field->id ?>">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="tagsModalLabel<?= $field->id ?>"><?php echo JText::_('COM_REDEVENT_TAGS_TITLE'); ?></h3>
		<div class="tags-howto"><?= JText::_('COM_REDEVENT_TAGS_LIST_DESCRIPTION') ?></div>
	</div>
	<iframe src="" style="border: 0px none transparent; padding: 0px; overflow: hidden;" frameborder="0" width="95%" class="modal-body"></iframe>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_REDEVENT_CLOSE'); ?></button>
	</div>
</div>
