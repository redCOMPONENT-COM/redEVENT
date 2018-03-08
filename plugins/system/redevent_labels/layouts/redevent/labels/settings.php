<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$form = $displayData['form'];
$xref = $displayData['xref'];
?>
<form action="index.php?option=com_redevent" method="post">
	<?= $form->renderFieldset('basic') ?>
	<input type="hidden" name="xref" value="<?= $xref ?>" />
	<input type="hidden" name="task" value="attendees.labels" />
	<button type="submit" class="btn btn-primary"><?= JText::_('JSUBMIT') ?></button>
</form>
