<?php
/**
 * @package     Redshopb.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;

$formName = $data['formName'];
?>
<h2>
	<?php echo JText::_('COM_REDEVENT_SESSIONS'); ?>
</h2>
<div class="row-fluid">
	<div class="btn-toolbar">
		<div class="btn-group">
			<button class="btn btn-success" onclick="Joomla.submitform('session.add',
				document.getElementById('<?php echo $formName; ?>'))" href="#">
				<i class="icon-file-text-alt"></i>
				<?php echo JText::_('JTOOLBAR_NEW') ?>
			</button>

			<button class="btn"
			        onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('Please first make a selection from the list');}
				        else{ Joomla.submitform('session.edit', document.getElementById('<?php echo $formName; ?>'))}"
			        href="#">
				<i class="icon-edit"></i>
				<?php echo JText::_('JTOOLBAR_EDIT') ?>
			</button>
			<button class="btn"
			        onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('Please first make a selection from the list');}
				        else{ Joomla.submitform('sessions.copy', document.getElementById('<?php echo $formName; ?>'))}"
			        href="#">
				<i class="icon-copy"></i>
				<?php echo JText::_('COM_REDEVENT_COPY') ?>
			</button>
			<button class="btn"
			        onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('Please first make a selection from the list');}
				        else{ Joomla.submitform('sessions.publish', document.getElementById('<?php echo $formName; ?>'))}"
			        href="#">
				<i class="icon-plus-sign"></i>
				<?php echo JText::_('JTOOLBAR_PUBLISH') ?>
			</button>
			<button class="btn"
			        onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('Please first make a selection from the list');}
				        else{ Joomla.submitform('sessions.unpublish', document.getElementById('<?php echo $formName; ?>'))}"
			        href="#">
				<i class="icon-minus-sign"></i>
				<?php echo JText::_('JTOOLBAR_UNPUBLISH') ?>
			</button>
			<button class="btn btn-danger"
			        onclick="if (document.<?php echo $formName; ?>.boxchecked.value==0){alert('Please first make a selection from the list');}
				        else{ Joomla.submitform('sessions.delete', document.getElementById('<?php echo $formName; ?>'))}"
			        href="#">
				<i class="icon-remove-sign"></i>
				<?php echo JText::_('JTOOLBAR_DELETE') ?>
			</button>
		</div>
	</div>
</div>
