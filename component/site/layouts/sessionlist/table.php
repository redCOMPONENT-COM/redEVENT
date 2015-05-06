<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$print = JArrayHelper::getValue($displayData, 'print', 0);
?>
<table class="eventtable" summary="eventlist">
	<?php if ($print): ?>
		<?php echo $this->sublayout('head', $displayData); ?>
		<?php echo $this->sublayout('bodyPrint', $displayData); ?>
	<?php else: ?>
		<?php echo $this->sublayout('head', $displayData); ?>
		<?php echo $this->sublayout('body', $displayData); ?>
	<?php endif; ?>
</table>
