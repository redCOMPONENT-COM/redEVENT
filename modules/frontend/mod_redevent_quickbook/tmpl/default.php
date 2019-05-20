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

$rfcore = RdfCore::getInstance();

$prices = json_encode($data->pricegroups);

$script = <<<JS
jQuery(function($) {
	modRedeventQuickbook($('.modRedeventQuickbook form'), $prices);
})
JS;

\Joomla\CMS\Factory::getDocument()->addScriptDeclaration($script);
?>
<div class="modRedeventQuickbook">
	<form action="<?= $action; ?>"
		  method="post" name="redform" enctype="multipart/form-data" class="form-validate">
		<?php echo JHtml::_('select.genericlist', $data->sessionsOptions, 'xref', null, 'value', 'text', JFactory::getApplication()->input->getInt('xref', 0)); ?>
		<?php echo $rfcore->getFormFields($data->form->id); ?>

		<div id="qbsubmit">
			<button type="button" id="qbsubmit-btn"><?php echo JText::_('MOD_REDEVENT_QUICKBOOK_BUTTON_BOOK_LABEL'); ?></button>
		</div>
		<input type="hidden" name="option" value="com_redevent"/>
		<input type="hidden" name="task" value="registration.register"/>
	</form>
</div>
