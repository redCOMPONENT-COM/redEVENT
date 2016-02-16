<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

$params = RedeventHelper::config();

$return = base64_encode('index.php?option=com_redeventb2b');

$icons = array(
	array('link' => 'index.php?option=com_redcore&view=config&layout=edit&component=com_redeventb2b&return=' . $return, 'icon' => 'icon-cog', 'text' => JText::_('COM_REDEVENTB2B_SETTINGS'), 'access' => 'core.manage'),
);
?>
<div class="container" id="redeventb2b-cpanel">
	<?php $iconsRow = array_chunk($icons, 6); ?>
	<?php foreach ($iconsRow as $row) : ?>
	<div class="row-fluid">
		<?php foreach ($row as $icon) : ?>
		<?php if ($this->user->authorise($icon['access'], 'com_redeventb2b')): ?>
			<div class="span2">
				<a class="redeventb2b-cpanel-icon-link" href="<?php echo JRoute::_($icon['link']); ?>">
					<div class="redeventb2b-cpanel-icon-wrapper">
						<div class="redeventb2b-cpanel-icon">
							<i class="<?php echo $icon['icon']; ?> icon-5x"></i>
						</div>
					</div>
					<div class="redeventb2b-cpanel-text">
						<?php echo $icon['text']; ?>
					</div>
				</a>
			</div>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
</div>
