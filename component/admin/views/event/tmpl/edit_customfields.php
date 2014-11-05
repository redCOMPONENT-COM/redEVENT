<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');
?>
<?php foreach ($this->customfields as $field): ?>

	<div class="control-group">
		<div class="control-label">
			<?php echo $field->getLabel(); ?>
		</div>
		<div class="controls">
			<?php echo $field->render(); ?>
		</div>
	</div>

<?php endforeach;
