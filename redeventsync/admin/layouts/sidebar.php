<?php
/**
 * @package     Redeventsync.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$active = null;

if (isset($data['active']))
{
	$active = $data['active'];
}

$logsClass = ($active === 'logs') ? 'active' : '';
$queueClass = ($active === 'queuedmessages') ? 'active' : '';
$syncClass = ($active === 'syncs') ? 'active' : '';
$optionsClass = ($active === 'config') ? 'active' : '';

$user = JFactory::getUser();

$uri = JUri::getInstance();
$return = base64_encode('index.php' . $uri->toString(array('query')));
?>

<ul class="nav nav-tabs nav-stacked">
	<li>
		<a class="<?php echo $logsClass; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redeventsync&view=logs') ?>">
			<i class="icon-list"></i>
			<?php echo JText::_('COM_REDEVENTSYNC_TITLE_LOGS') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $queueClass; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redeventsync&view=queuedmessages') ?>">
			<i class="icon-time"></i>
			<?php echo JText::_('COM_REDEVENTSYNC_TITLE_QUEUEDMESSAGES') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $syncClass; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redeventsync&view=sync') ?>">
			<i class="icon-exchange"></i>
			<?php echo JText::_('COM_REDEVENTSYNC_MENU_SYNC') ?>
		</a>
	</li>
	<?php if ($user->authorise('core.admin', 'com_redform')): ?>
	<li>
		<a class="<?php echo $optionsClass; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redcore&view=config&layout=edit&component=com_redeventsync&return=' . $return); ?>">
			<i class="icon-cogs"></i>
			<?php echo JText::_('JToolbar_Options') ?>
		</a>
	</li>
	<?php endif; ?>
</ul>
