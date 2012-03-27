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
			echo REOutput::submitbutton( $this->dellink, $this->params );
			echo REOutput::archivebutton( $this->params, $this->task, $this->category->slug );
		endif;
		echo REOutput::mailbutton( $this->category->slug, 'venuecategory', $this->params );
		echo REOutput::printbutton( $this->print_link, $this->params );
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
	<?php echo redEVENTImage::modalimage($this->category->image, $this->category->name); ?>
	<?php else: ?>
	<?php echo JHTML::image('components/com_redevent/assets/images/noimage.png', $this->category->name); ?>
	<?php endif; ?>
</div>

<div class="catdescription">
	<?php echo $this->description; ?>
</div>
</div>
<!--table-->

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<?php if ($this->params->get('filter_text',1) || $this->params->get('display_limit_select')) : ?>
<div id="el_filter" class="floattext">
		<?php if ($this->params->get('filter_text',1)) : ?>
		<div class="el_fleft">
			<?php
			echo '<label for="filter_type">'.JText::_('COM_REDEVENT_FILTER').'</label>&nbsp;';
			echo $this->lists['filter_type'].'&nbsp;';
			?>
			<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="text_area" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
			<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_GO' ); ?></button>
			<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_RESET' ); ?></button>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('display_limit_select')) : ?>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>

<?php echo $this->loadTemplate('table'); ?>
<p>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
<input type="hidden" name="view" value="venuecategory" />
<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo (isset($this->item->id) ? $this->item->id:""); ?>" />
</p>
</form>

<!--pagination-->

<?php if (!$this->params->get( 'popup' ) ) : ?>
<div class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>
<?php endif; ?>

<!--copyright-->

<p class="copyright">
	<?php echo REOutput::footer( ); ?>
</p>
</div>