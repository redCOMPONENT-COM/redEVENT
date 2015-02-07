<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<table class="table-striped">
	<tbody>
	<?php foreach ($this->data->getFields() as $field): ?>
		<tr>
			<th class="key"><?php echo $field->name; ?></th>
			<td valign="top">
				<?php if (is_array($field->value)): ?>
					<?php echo implode('<br>', $field->value); ?>
				<?php else: ?>
					<?php echo $field->getValueAsString(); ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
