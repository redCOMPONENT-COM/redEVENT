<?php
/**
 * @package     RedITEM2
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

extract($displayData);

$selectedText = $value ? RedeventEntityEvent::load($value)->title : '';
?>
<?php if (!empty($value)) : ?>
<span class="reditem_redevent_event reditem_redevent_event_<?php echo $field->id; ?>">
	<?php echo $selectedText; ?>
</span>
<?php endif; ?>
