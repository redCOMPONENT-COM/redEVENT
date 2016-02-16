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
			'id'    => $modalId
		),
		'params' => array(
			'showHeader'      => true,
			'showFooter'      => false,
			'showHeaderClose' => true,
			'title' => $modalTitle,
			'link' => $link
		)
	),
	$modalId
);

RHelperAsset::load('modalAddVenue.js', 'com_redevent');

echo RedeventLayoutHelper::render('modal.addvenue', $modal);
?>
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#<?php echo $modalId; ?>">
	<i class="icon-plus"></i>
</button>
