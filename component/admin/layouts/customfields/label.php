<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$customfield = $displayData;

JHtml::_('rbootstrap.tooltip');
?>
<label for="<?php  echo $customfield->id ?>" id="<?php  echo $customfield->id ?>-lbl" class="hasTooltip" title="<?php echo $customfield->name ?>">
	<?php echo  $customfield->name ?>
	<?php  if ($customfield->required): ?>
		<span class="star">&nbsp;*</span>
	<?php endif ?>
</label>
