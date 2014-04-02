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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="redevent" class="el_categoriesview">
<p class="buttons">
	<?php
		echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
	?>
</p>

<?php if ($this->params->def( 'show_pagepage_title', 1 )) : ?>
	<h1 class="componentheading">
		<?php echo $this->escape($this->pagetitle); ?>
	</h1>
<?php endif; ?>

<?php foreach ($this->rows as $row) : ?>

<div class="floattext">
	<h2 class="eventlist cat<?php echo $row->id; ?>">
		<?php echo $this->escape($row->catname); ?>
	</h2>

	<?php if (!empty($row->image) || $this->params->get('use_default_picture', 1)):?>
	<div class="catimg">
	  	<?php	if (!empty($row->image)): ?>
	  	<span>
	  	<?php $img = JHTML::image(redEVENTImage::getThumbUrl($row->image), $row->catname);
				echo JHTML::_('link', JRoute::_($row->linktarget), $img); ?>
			</span>
			<?php endif; ?>
		<?php
			echo JText::_('COM_REDEVENT_EVENTS' ).': ';
			echo JHTML::_('link', JRoute::_($row->linktarget), $row->assignedevents);
		?>
	</div>
	<?php endif; ?>

	<div class="catdescription cat<?php echo $row->id; ?>"><?php echo $row->catdescription ; ?>
	<p>
		<?php
			echo JHTML::_('link', JRoute::_($row->linktarget), $row->linktext);
		?>
	</p>
	</div>

</div>
<?php endforeach; ?>

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
