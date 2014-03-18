<?php
/**
 * @package     Redevent
 * @subpackage  mod_redevent_globase
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

$rfcore = new RedFormCore;
?>
<div class="modRedeventGlobase">
<form action="<?php echo $action; ?>"
      method="post" name="redform" enctype="multipart/form-data" onsubmit="return CheckSubmit(this);">

	<?php echo $rfcore->getFormFields($params->get('formId')); ?>

	<div class="globase-categories">
		<div class="globase-categories-label">
			<?php echo JText::_('MOD_REDEVENT_GLOBASE_SELECT_NEWSLETTER_CATEGORIES_LABEL'); ?>
		</div>
		<div class="globase-categories-boxes">
			<?php foreach ($nyhedsbrev as $opt): ?>
				<input name="nyhedsbrev[]" type="checkbox" value="<?php echo $opt->value; ?>" />
				<label><?php echo $opt->value; ?></label>
			<?php endforeach; ?>
		</div>
	</div>

	<input name="pureGlobase" type="hidden" value="1" />
	<div id="globasesubmit">
		<button type="button" class="globasesubmit-btn"><?php echo JText::_('MOD_REDEVENT_GLOBASE_BUTTON_BOOK_LABEL'); ?></button>
	</div>
</form>
</div>
