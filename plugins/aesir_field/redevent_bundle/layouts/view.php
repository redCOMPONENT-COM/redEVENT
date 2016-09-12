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

$selectedText = $value ? RedeventEntityBundle::load($value)->name : '';
?>
<?php if (!empty($value)) : ?>
<span class="reditem_redevent_bundle reditem_redevent_bundle_<?php echo $field->id; ?>">
	<?php echo $selectedText; ?>
</span>
<?php endif; ?>
