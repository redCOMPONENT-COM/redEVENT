<?php
/**
 * @package     RedITEM
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

extract($displayData);

$isNew = JFactory::getApplication()->input->getInt('id', 0) == 0;

if (!empty($default) && $isNew)
{
	$value = $default;
}

$selectedText = $value ? RedeventEntitySession::load($value)->getEvent()->title : '';

RHelperAsset::load('lib/jquery-ui/jquery-ui.min.js', 'redcore');
RHelperAsset::load('lib/jquery-ui/jquery-ui.custom.min.css', 'redcore');
?>

<script type="text/javascript">

	(function($){
		$(document).ready(function(){
			$('#reset-redevent-session').click(function(){
				$(this).parents('.session-body').find('input').val('');
			});
		});
	})(jQuery);

	function RedeventSessionInsertFieldValue(value, text, fieldId) {
		(function($){
			$("#" + fieldId + "_value").val(value);
			$("#" + fieldId + "_text").val(text);
		})(jQuery);
		jModalClose();
	}

	function jModalClose() {
		(function($){
			jQuery('.modal').modal('hide');
		})(jQuery);
	}
</script>

<div>
	<div class="session" id="div_<?php echo $fieldId; ?>" data-fieldId="<?php $field->id; ?>">
		<div class="session-body">
			<p>
				<input type="text" id="<?php echo $fieldId; ?>_text" value="<?php echo $selectedText; ?>" class="large" readonly="readonly"/>
				<button type="button" class="btn btn-default" data-toggle="modal" data-target="#<?php echo $fieldId; ?>_session_modal">
					<?php echo JText::_('JSelect'); ?>
				</button>
				<button type="button" class="btn btn-danger" id="reset-redevent-session">
					<?php echo JText::_('PLG_AESIR_FIELD_REDEVENT_SESSION_BUTTON_RESET'); ?>
				</button>
			</p>
			<div class="clearfix"></div>
			<input type="hidden" id="<?php echo $fieldId; ?>_value" name="cform[session][<?php echo $fieldcode; ?>]" value="<?php echo htmlspecialchars($value); ?>" />
		</div>
	</div>
	<div class="clearfix"></div>
</div>

<div class="modal fade" id="<?php echo $fieldId; ?>_session_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<iframe src="index.php?option=com_redevent&view=sessions&layout=element&tmpl=component&fieldid=<?php echo $fieldId; ?>&function=RedeventSessionInsertFieldValue" frameborder="0" width="100%" height="450px"></iframe>
			</div>
		</div>
	</div>
</div>
