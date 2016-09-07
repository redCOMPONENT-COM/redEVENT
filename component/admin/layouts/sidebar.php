<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_REDCORE') or die;

$user = JFactory::getUser();
$active = null;
$data = $displayData;
$params = RedeventHelper::config();

if (isset($data['active']))
{
	$active = $data['active'];
}

$icons = RedeventHelperAdmin::getAdminMenuItems();
?>
<script type="text/javascript">
	(function($){
		$(function(){
			$('.redevent-sidebar .redevent-sidebar-item.active').parent().parent().addClass('in');
		});
	})(jQuery);
</script>

<?php if (!empty($icons)): ?>
	<div class="accordion redevent-sidebar" id="redeventSideBarAccordion">
		<?php $index = 0; ?>
		<?php foreach ($icons as $group): ?>
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#redeventSideBarAccordion" href="#collapse<?php echo $index ?>">
						<i class="<?php echo $group['icon'];?>"></i>
						<?php echo $group['text'];?>
					</a>
				</div>
				<div id="collapse<?php echo $index ?>" class="accordion-body collapse">
					<?php if (!empty($group['items'])): ?>
						<ul class="nav nav-tabs nav-stacked">
							<?php foreach ($group['items'] as $icon): ?>
								<?php
								$class = '';
								$stat = (isset($icon['count'])) ? $icon['count'] : 0;
								?>
								<?php if ($active === $icon['view']): ?>
									<?php $class = 'active'; ?>
								<?php endif; ?>
								<li class="redevent-sidebar-item <?php echo $class ?>">
									<a href="<?php echo $icon['link'] ?>">
										<i class="<?php echo $icon['icon'] ?>"></i>
										<?php echo $icon['text'] ?>
										<?php if ($stat): ?>
											<span class="badge pull-right"><?php echo $stat; ?></span>
										<?php endif;?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
			<?php $index++; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
