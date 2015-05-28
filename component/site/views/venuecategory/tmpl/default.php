<?php
/**
 * @version 1.0 $Id: default.php 360 2009-06-30 07:49:16Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="redevent" class="el_categoryevents">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
		endif;
		echo RedeventHelperOutput::mailbutton( $this->category->slug, 'venuecategory', $this->params );
		echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

<?php if ($this->params->def( 'show_page_title', 1 )) : ?>

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
<!--table-->

<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">

	<!-- filters  -->
	<?php echo RLayoutHelper::render(
		'sessionlist.filters',
		$this
	); ?>
	<!-- end filters -->

<?php echo $this->loadTemplate('table'); ?>
<p>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="filter_order" value="<?php echo $this->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir; ?>" />
<input type="hidden" name="view" value="venuecategory" />
<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo (isset($this->item->id) ? $this->item->id:""); ?>" />
</p>
</form>

<!--pagination-->

<?php if (!$this->params->get( 'popup' ) ) : ?><!--pagination-->
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
<?php endif; ?>
</div>
