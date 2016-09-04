<?php
/**
 * @package     RedEVENT
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);
?>
<form action="<?php echo JRoute::_('index.php'); ?>" class="redform-validate" method="post" name="redform" enctype="multipart/form-data">
	<?php echo $redformHtml; ?>

	<?php if (!$submitKey && $session->getEvent()->hasReview()): ?>
		<input type="hidden" name="hasreview" value="1"/>
	<?php endif; ?>

	<div id="submit_button" style="display: block;" class="submitform<?php echo $form->classname; ?>">

		<?php if (empty($submitKey)): ?>
			<input type="submit" id="regularsubmit" name="submit" value="<?php echo JText::_('COM_REDEVENT_Submit'); ?>" />
		<?php else: ?>
			<input type="submit" id="redformsubmit" name="submit" value="<?php echo JText::_('COM_REDEVENT_Confirm'); ?>" />
			<input type="submit" id="redformcancel" name="cancel" value="<?php echo JText::_('COM_REDEVENT_Cancel'); ?>" />
		<?php endif; ?>

	</div>

	<input type="hidden" name="xref" value="<?php echo $session->id; ?>"/>
	<input type="hidden" name="option" value="com_redevent"/>
	<input type="hidden" name="task" value="registration.register"/>
</form>
