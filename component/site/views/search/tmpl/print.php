<?php
/**
 * @version 1.0 $Id: simplelist.php 1403 2009-11-03 12:41:02Z julien $
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
<div id="redevent" class="el_eventlist<?= $this->params->get('pageclass_sfx') ?> print">
<p class="buttons">
	<?php	echo RedeventHelperOutput::printbutton( $this->print_link, $this->params ); ?>
</p>

  <h1 class="componentheading">
    <?php echo JText::_('COM_REDEVENT_Search_events'); ?>
  </h1>


    <?php if ($this->params->get('showintrotext')) : ?>
      <div class="description no_space floattext">
        <?php echo $this->params->get('introtext'); ?>
      </div>
    <?php endif; ?>

<form action="<?php echo JRoute::_($this->action); ?>" method="post" id="adminForm">

<div id="el_filter" class="floattext">

  <?php if ($this->params->get('display_limit_select')) : ?>
	<div class="el_fright">
		<?php	echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
		echo $this->pageNav->getLimitBox();
		?>
	</div>
  <?php endif; ?>

</div>


<?php echo $this->loadTemplate('table'); ?>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
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
