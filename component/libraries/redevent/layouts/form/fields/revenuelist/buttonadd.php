<?php
/**
 * @package     RedEVENT
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$fieldId = $displayData['fieldId'];

$modalTitle = JText::_('COM_REDEVENT_FIELD_VENUELIST_MODAL_ADDVENUE_TITLE');
$modalId = "$fieldId-modal";
$link = JRoute::_('index.php?option=com_redevent&task=editvenue.add&modal=1&tmpl=component&fieldId=' . $fieldId);

// Create the modal object
$modal = RModal::getInstance(
	array(
		'attribs' => array(
			'id'    => $modalId,
			'class' => 'modal hide',
			'style' => 'width: 800px; height: 500px;'
		),
		'params' => array(
			'showHeader'      => true,
			'showFooter'      => false,
			'showHeaderClose' => true,
			'title' => $modalTitle,
			'link' => $link,
			'height' => '400px' // As the content is obtained through ajax, it's not calculated right
		)
	),
	$modalId
);

RHelperAsset::load('modalAddVenue.js', 'com_redevent');

echo RedeventLayoutHelper::render('modal.addvenue', $modal);
?>
<a class="btn btn-primary modalAjax" data-toggle="modal" title="<?php echo $modalTitle; ?>" href="#<?php echo $modalId; ?>"
   rel="{handler: 'iframe', size: {x: 800, y: 500}}">
	<i class="icon-plus"></i>
</a>
