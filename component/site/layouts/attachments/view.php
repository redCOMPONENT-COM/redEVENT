<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_REDCORE') or die;

/**
 * @var   array  $attachments  attachements
 */
extract($displayData);
JHtml::_('bootstrap.tooltip');
RHelperAsset::load('attachements.css', 'com_redevent');
?>
<ul class="re-files">
	<?php foreach ($attachments as $file): ?>
		<li>
			<?php
				$name = $file->name ?: $file->file;
				$tip = JHtml::tooltipText(JText::_('COM_REDEVENT_Download') . ' ' . $name);
			?>
			<a href="<?= JRoute::_('index.php?option=com_redevent&task=getfile&format=raw&file=' . $file->id) ?>"
			   title="<?= $tip ?>">
				<i class="icon icon-download"></i> <span class="re-file-name"><?= $name ?></span>
			</a>
			<?php if ($file->description): ?>: <?= $file->description ?> <?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>