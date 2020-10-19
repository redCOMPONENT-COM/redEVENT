<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die('Restricted access');
?>

<ul class="redeventalleventsmod<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php foreach ($list as $item) :  ?>
		<li><?php echo JHTML::link($item->link, $item->text); ?></li>
	<?php endforeach; ?>
</ul>
