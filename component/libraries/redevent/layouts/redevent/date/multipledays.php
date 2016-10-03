<?php
/**
 * @package     RedEVENT
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);
?>
<span class="event-date">
	<span class="event-start">
		<span class="event-day"><?= $date_start ?></span>

		<?php if ($time_start): ?>
			<span class="event-time"><?= $time_start ?></span>
		<?php endif; ?>
	</span>

	<span class="event-end"><span class="event-day"><?= $date_end ?></span>
		<?php if ($time_end): ?>
			<span class="event-time"><?= $time_end ?></span>
		<?php endif; ?>
	</span>
</span>
