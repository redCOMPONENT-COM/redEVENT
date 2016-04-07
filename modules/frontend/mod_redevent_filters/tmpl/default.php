<?php
/**
 * @package     Redevent
 * @subpackage  mod_redevent_quickbook
 * @copyright   (C) 2014 redcomponent.com
 * @license     GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');
?>
<div id="modRedeventFilters">
	<?php if ($params->get('text_filter')): ?>
		<div class="text_filter">
			<input type="text" name="filter" value="<?php echo $model->getState('filter'); ?>" placeholder="<?php echo JText::_('MOD_REDEVENT_FILTERS_TEXT_PLACEHOLDER'); ?>"/>
			<button id="mfreset"><?php echo JText::_('MOD_REDEVENT_FILTERS_BUTTON_RESET_LABEL'); ?></button>
		</div>
	<?php endif; ?>

	<?php if ($params->get('category_filter') && count($data->category)): ?>
	<?php $selected = $model->getState('filter_multicategory', array()); ?>
	<div class="category_filter">
		<div class="filter_title"><?php echo JText::_('MOD_REDEVENT_FILTERS_TITLE_CATEGORIES'); ?></div>
		<div class="boxes">
			<?php foreach ($data->category as $o): ?>
				<?php $checked = in_array($o->value, $selected) ? 'checked="checked"' : ''; ?>
				<div class="boxoption">
					<input type="checkbox" name="filter_multicategory[]" value="<?php echo $o->value; ?>" <?php echo $checked; ?>/><label><?php echo $o->text; ?></label>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($params->get('venue_filter') && count($data->venue)): ?>
		<?php $selected = $model->getState('filter_multivenue', array()); ?>
		<div class="venue_filter">
			<div class="filter_title"><?php echo JText::_('MOD_REDEVENT_FILTERS_TITLE_VENUES'); ?></div>
			<div class="boxes">
				<?php foreach ($data->venue as $o): ?>
					<?php $checked = in_array($o->value, $selected) ? 'checked="checked"' : ''; ?>
					<div class="boxoption">
						<input type="checkbox" name="filter_multivenue[]" value="<?php echo $o->value; ?>" <?php echo $checked; ?>/><label><?php echo $o->text; ?></label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($params->get('custom_filter') && count($data->custom)): ?>
		<?php $allSelected = $model->getState('filter_customs', array()); ?>

		<?php foreach ($data->custom as $custom): ?>
			<?php $selected = isset($allSelected[$custom->id]) ? $allSelected[$custom->id] : ''; ?>
			<div class="custom_filter">
				<div class="filter_title"><?php echo $custom->name; ?></div>
				<?php if ($custom->options):
					$options = explode("\n", $custom->options);
					$options = array_map('trim', $options);
					$selected = is_array($selected) ? $selected : array($selected);
				?>
					<div class="boxes">
						<?php foreach ($options as $o): ?>
							<?php $checked = $selected && in_array($o, $selected) ? 'checked="checked"' : 'not'; ?>
							<div class="boxoption">
								<input type="checkbox" name="filtercustom[<?php echo $custom->id; ?>][]" value="<?php echo $o; ?>" <?php echo $checked; ?>/><label><?php echo $o; ?></label>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else: ?>
					<div class="textfilter">
						<input type="text" name="filtercustom[<?php echo $custom->id; ?>]" value="<?php echo $selected; ?>" placeholder="<?php echo JText::_('MOD_REDEVENT_FILTERS_TEXT_PLACEHOLDER'); ?>" />
					</div>
				<?php endif; ?>

			</div>

		<?php endforeach; ?>
	<?php endif; ?>
</div>
