<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<h2><?php echo JText::_('COM_REDEVENT_TAGS_TITLE'); ?></h2>

<p><?php echo JText::_('COM_REDEVENT_TAGS_LIST_DESCRIPTION'); ?></p>

<?php $active = true; ?>
<ul class="nav nav-tabs" id="tagsTab">
	<?php foreach ($this->items as $section => $tags): ?>
	<li<?php echo ($active ? ' class="active"' : ''); ?>>
		<a href="#tags<?php echo $section; ?>" data-toggle="tab">
			<strong><?php echo JText::_($section); ?></strong>
		</a>
	</li>
		<?php $active = false; ?>
	<?php endforeach; ?>
</ul>

<?php $active = true; ?>
<div class="tab-content">
	<?php foreach ($this->items as $section => $tags): ?>
		<div class="tab-pane <?php echo ($active ? ' active' : ''); ?>" id="tags<?php echo $section; ?>">
			<div class="row-fluid">
				<table class="tagstable adminlist">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_REDEVENT_TAGS_NAME')?></th>
							<th><?php echo JText::_('COM_REDEVENT_TAGS_DESCRIPTION')?></th>
						</tr>
					</thead>
					<tbody>
						<?php $k = 0; ?>
						<?php foreach ($tags as $tag): ?>
						<tr class="<?php echo ($k ? 'row1' : 'row0'); ?>">
							<td>[<?php echo addslashes($this->escape($tag->name)); ?>]</td>
							<td><?php echo $tag->description; ?></td>
						</tr>
						<?php $k = 1 - $k; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endforeach; ?>
</div>
