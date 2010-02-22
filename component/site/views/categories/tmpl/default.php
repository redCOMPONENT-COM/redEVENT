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
		echo ELOutput::submitbutton( $this->dellink, $this->params );
		echo ELOutput::archivebutton( $this->params, $this->task );
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

	<div class="catimg">
	  	<?php
		if ($row->image != '') {
			echo JHTML::_('link', JRoute::_($row->linktarget), $row->image);
		?>
		<p>
		<?php }
			echo JText::_( 'EVENTS' ).': ';
			echo JHTML::_('link', JRoute::_($row->linktarget), $row->assignedevents);
			
			if ($row->image != '') {?> </p> <?php }?>
	</div>

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
<p class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</p>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>

<!--copyright-->

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>
</div>