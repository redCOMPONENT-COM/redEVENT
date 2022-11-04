<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );
$class = $this->params->get('pageclass_sfx');
?>
<style type="text/css">
.rf_img {min-height:<?php echo $this->config->get('imageheight', 100);?>px;}
</style>
<div id="redevent" class="rf_thumb<?= $class ?>"">
	<p class="buttons">
		<?php
			if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
				echo RedeventHelperOutput::listbutton( $this->list_link, $this->params );
				echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
			endif;

			echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
		?>
	</p>

	<?php if ($this->params->def( 'show_page_heading', 1 )) : ?>

	    <h1 class="componentheading">
			<?php echo $this->escape($this->pagetitle); ?>
		</h1>

	<?php endif; ?>


	<?php if ($this->params->get('showintrotext')) : ?>
		<div class="description no_space floattext">
			<?php echo $this->params->get('introtext'); ?>
		</div>
	<?php endif; ?>

	<!-- filter -->
	<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">


		<!-- filters  -->
		<?php echo RedeventLayoutHelper::render(
			'sessionlist.filters',
			$this
		); ?>
		<!-- end filters -->

	<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
	<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	</form>
	<!-- filter end -->

	<?php echo $this->loadTemplate('items'); ?>

	<!--footer--><!--pagination-->
	<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pageNav->get('pages.total') > 1)) : ?>
	<div class="pagination">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
					<?php echo $this->pageNav->getPagesCounter(); ?>
			</p>

			<?php endif; ?>
		<?php echo $this->pageNav->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>
	<!-- pagination end -->

</div>
