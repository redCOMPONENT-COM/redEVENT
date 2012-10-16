<?php
/**
 * @version 1.0 $Id$
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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="redevent" class="el_eventlist">
<p class="buttons">
	<?php
		echo REOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

<h1 class="componentheading">
	<?php echo $this->daydate; ?>
</h1>

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<?php if ($this->params->get('filter_text',1) || $this->params->get('display_limit_select')) : ?>
<div id="el_filter" class="floattext">
		<?php if ($this->params->get('filter_text',1)) : ?>
		<div class="el_fleft">
			<?php
			echo '<label for="filter_type">'.JText::_('COM_REDEVENT_Filter').'</label>&nbsp;';
			echo $this->lists['filter_types'].'&nbsp;';
			?>
			<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="text_area" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('COM_REDEVENT_EVENTS_FILTER_HINT'); ?>"/>
			<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
			<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('display_limit_select')) : ?>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('COM_REDEVENT_Display_Num').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>
<!--table-->

<?php echo $this->loadTemplate('table'); ?>

<p>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

<!--footer-->

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