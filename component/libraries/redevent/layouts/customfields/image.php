<?php
/**
 * @package     RedITEM
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

extract($displayData);

$fieldcode = $id;

$uploadMaxFilesize       = (int) $config->get('upload_max_filesize', 2);
$uploadMaxFilesizeInByte = $uploadMaxFilesize * 1024 * 1024;
$allowedFileExtension    = $config->get('allowed_file_extension', 'jpg,jpeg,gif,png');
$allowedMime             = $config->get('allowed_file_mimetype', 'image/jpg,image/jpeg,image/gif,image/png');
$fieldName               = 'jform[custom' + $id + ']';
$fieldId                 = 'cform_' . $fieldcode;

$isCroppingEnable = $config->get('enable_cropping_image', '1');
$cropKeepRatio    = (boolean) $config->get('crop_keep_ratio', 0);
$previewWidth     = $config->get('preview_image_width', '300');
$previewHeight    = $config->get('preview_image_height', '300');
$cropWidth        = $config->get('crop_width', '');
$cropHeight       = $config->get('crop_height', '');

// Load string for javascripts
JText::script('COM_REDEVENT_UPLOAD_1_FILE_ONLY');
JText::script('COM_REDEVENT_ITEM_DRAG_AN_IMAGE');
JText::script('COM_REDEVENT_ITEM_DRAG_IMAGES');
JText::script('COM_REDEVENT_ITEM_DRAG_FEATURE_NOT_SUPPORT');
JText::script('COM_REDEVENT_UPLOAD_FILE_INVALID');
JText::script('COM_REDEVENT_UPLOAD_FILE_TOO_BIG');
JText::script('COM_REDEVENT_UPLOAD_ABORT');
JText::script('COM_REDEVENT_FEATURE_CROP_BTN_LBL');
JText::script('COM_REDEVENT_FEATURE_CROPIMAGE_FAIL');
JText::script('COM_REDEVENT_DRAG_AND_DROP_BROWSE');
JText::script('COM_REDEVENT_DRAG_AN_IMAGE');
JText::script('COM_REDEVENT_UPLOAD_DELETE_FILE');

// Load dragndrop scripts
RHelperAsset::load('lib/jquery-ui/jquery-ui.min.js', 'redcore');
RHelperAsset::load('lib/jquery-ui/jquery-ui.custom.min.css', 'redcore');
RHelperAsset::load('jquery/jquery.ajaxfileupload.min.js', 'com_redevent');
RHelperAsset::load('dragndrop.js', 'com_redevent');
RHelperAsset::load('dragndrop.min.css', 'com_redevent');
RHelperAsset::load('reditem.cropimage.min.js', 'com_redevent');
?>

<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#imgfield_<?php echo $fieldcode; ?>_<?php echo $id ?>').dragndrop({
			url: "index.php?option=com_redevent&task=customfield.ajaxUpload",
			text: "<?php echo JText::_('COM_REDEVENT_ITEM_DRAG_A_FILE') ?>",
			img_preview: "div_<?php echo $fieldId; ?>",
			img_preview_path: "<?php echo JURI::root() . 'images/com_redevent/customfields/image/' ?>",
			config: {
				size: "<?php echo $uploadMaxFilesizeInByte ?>",
				ext: "<?php echo $allowedFileExtension?>",
				mime: "<?php echo $allowedMime ?>",
				includeBrowse: 1
			},
		});
	});
</script>

<div>
	<div class="media" id="div_<?php echo $fieldId; ?>">
		<?php if (!empty($imagePreview)) : ?>
		<div class="pull-left">
			<div class="img-preview-container" style="max-width: <?php echo $previewWidth ?>px; max-height: <?php echo $previewHeight ?>px; position:relative;  margin-right: 20px;" >
				<img id="img_preview_<?php echo $fieldcode?>" src="<?php echo $imagePreview; ?>" class="img-polaroid" style="max-width: 100%; max-height: 100%;" />
			</div>
		</div>
		<?php endif; ?>
		<div class="media-body">
			<p>
				<input type="file" name="<?php echo $fieldName; ?>" id="<?php echo $fieldId; ?>"/>
				<div class="clearfix"></div>
			</p>
			<div id="imgfield_<?php echo $fieldcode; ?>_<?php echo $id ?>" class="dragndrop" upload-type="image"
				input-target="<?php echo $fieldId; ?>" target="<?php echo $fieldcode ?>"></div>
			<div class="clearfix"></div>
			<input type="hidden" id="<?php echo $fieldId; ?>_value" name="cform[image][<?php echo $fieldcode; ?>]" value="<?php echo htmlspecialchars($value); ?>" />
		</div>
	</div>
	<div class="clearfix"></div>
</div>
