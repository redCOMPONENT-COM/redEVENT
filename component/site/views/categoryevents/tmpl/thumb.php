<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access' );
?>
<style type="text/css">
.rf_img {min-height:<?php echo $this->config->get('imageheight', 100);?>px;}
</style>
<div id="redevent" class="el_categoryevents<?= $this->params->get('pageclass_sfx') ?>">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			echo RedeventHelperOutput::listbutton( $this->list_link, $this->params );
			echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
		endif;
		echo RedeventHelperOutput::mailbutton( $this->category->slug, 'categoryevents', $this->params );
		echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

<?php if ($this->params->def( 'show_page_heading', 1 )) : ?>

    <h1 class='componentheading'>
		<?php echo $this->task == 'archive' ? $this->escape($this->category->name.' - '.JText::_('COM_REDEVENT_ARCHIVE')) : $this->escape($this->category->name); ?>
	</h1>

<?php endif; ?>

<div class="floattext">
<div class="catimg">
	<?php if ($this->category->image): ?>
	<?php echo RedeventImage::modalimage($this->category->image, $this->category->name); ?>
	<?php else: ?>
	<?php echo JHTML::image('media/com_redevent/images/noimage.png', $this->category->name); ?>
	<?php endif; ?>
</div>

<div class="catdescription">
	<?php echo $this->description; ?>
</div>
</div>



<!-- use form for filters and pagination -->
<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">


	<!-- filters  -->
	<?php echo RedeventLayoutHelper::render(
		'sessionlist.filters',
		$this
	); ?>
	<!-- end filters -->

<?php echo $this->loadTemplate('items'); ?>

<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
<input type="hidden" name="view" value="categoryevents" />
<input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo (isset($this->item->id) ? $this->item->id:""); ?>" />
</form>

<!--pagination-->
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
