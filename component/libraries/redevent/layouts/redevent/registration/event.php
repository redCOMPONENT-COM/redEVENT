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

	<div id="submit_button" style="display: block;" class="submitform<?php echo $form->classname; ?>">
		<input type="submit" id="regularsubmit" name="submit" value="<?php echo JText::_('COM_REDEVENT_Submit'); ?>" />
	</div>

	<input type="hidden" name="option" value="com_redevent"/>
	<input type="hidden" name="task" value="registration.register"/>
</form>
