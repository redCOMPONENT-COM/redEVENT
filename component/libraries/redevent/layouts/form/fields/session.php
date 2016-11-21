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

$value = (int) $field->value ?: 0;

if ($field->required)
{
	$class = 'class="required"';
}

RHelperAsset::load('sessionmodal.js', 'com_redevent');
?>
<!-- Button to trigger modal -->
<div class="input-append sessionmodal-buttons" fieldId="<?= $field->id ?>">
	<input type="text" id="<?= $field->id ?>_name" value="<?= $title ?>" disabled="disabled" <?= $title ?>/>
	<a href="#sessionModal<?= $field->id ?>" role="button" class="btn btn-primary" data-toggle="modal"><?= JText::_('LIB_REDEVENT_SELECT_SESSION') ?></a>
	<?php if ($reset): ?>
		<a id="reset<?= $field->id ?>" class="btn reset-session" title="<?= JText::_('LIB_REDEVENT_RESET') ?>"><?= JText::_('LIB_REDEVENT_RESET') ?></a>
	<?php endif; ?>
	<input type="hidden" id="<?= $field->id ?>_id" <?= $class ?> name="<?= $field->name ?>" value="<?= $value ?>" />
</div>

<!-- Modal -->
<div id="sessionModal<?= $field->id ?>" class="modal fade sessionFieldModal" tabindex="-1" role="dialog" aria-labelledby="sessionFieldModalLabel<?= $field->id ?>" field="<?= $field->id ?>">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="sessionFieldModalLabel<?= $field->id ?>"><?php echo JText::_('LIB_REDEVENT_SELECT_SESSION'); ?></h3>
	</div>
	<iframe src="<?= $link ?>" style="border: 0px none transparent; padding: 0px; overflow: hidden; height: 90vh" frameborder="0" width="95%" class="modal-body"></iframe>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JCLOSE'); ?></button>
	</div>
</div>
