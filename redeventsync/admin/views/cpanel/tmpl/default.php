<?php
// Protect from unauthorized access
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

$option = 'com_redeventsync';

FOFTemplateUtils::addCSS('media://com_redeventsync/css/backend.css');
?>

<div id="cpanel" class="span12">
	<div class="icon">
		<?php
		$href = JRoute::_('index.php?option=com_redeventsync&view=logs');
		$img = JHtml::image(JURI::root() . 'media/com_redeventsync/images/icon-48-log.png', JText::_('COM_REDEVENTSYNC_NAME_CPANEL_LOGS_ALT'));
		$txt = $img . '<span>' . JText::_('COM_REDEVENTSYNC_NAME_CPANEL_LOGS') . '</span>';
		echo JHtml::link($href, $txt);
		?>
	</div>
</div>