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

<div id="redevent" class="el_categoriesdetailed">
<p class="buttons">
	<?php
		if ( !$this->params->get( 'popup' ) ) : //don't show in printpopup
			echo RedeventHelperOutput::submitbutton( $this->dellink, $this->params );
		endif;
		echo RedeventHelperOutput::printbutton( $this->print_link, $this->params );
	?>
</p>

<?php if ($this->params->get('show_page_title')) : ?>

<h1 class="componentheading">
<?php echo $this->escape($this->pagetitle); ?>
</h1>

<?php endif;

foreach($this->categories as $category) :
?>
	<h2 class="eventlist cat<?php echo $category->id; ?>">
		<?php echo $this->escape($category->name); ?>
	</h2>

<div class="cat<?php echo $category->id; ?> floattext">

	<?php if (!empty($category->image) || $this->params->get('use_default_picture', 1)):?>
	<div class="catimg">
	  	<?php
	  	if ($category->image) {
	  		$img = JHTML::image(RedeventImage::getThumbUrl($category->image), $category->name);
	  	}
	  	else {
	  		$img = JHTML::image('media/com_redevent/images/noimage.png', $category->name);
	  	}
	  	echo JHTML::_('link', JRoute::_($category->linktarget), $img);
		?>
		<p>
			<?php
				echo JText::_('COM_REDEVENT_EVENTS' ).': ';
				echo JHTML::_('link', JRoute::_($category->linktarget), $category->assignedevents);
			?>
		</p>
	</div>
	<?php endif; ?>

	<div class="catdescription"><?php echo $category->description; ?>
		<p>
			<?php
				echo JHTML::_('link', JRoute::_($category->linktarget), $category->linktext);
			?>
		</p>
	</div>
	<br class="clear" />

</div>

<!--table-->
<?php
$this->rows		= & $category->events;
$this->categoryid = $category->id;

echo $this->loadTemplate('table');

endforeach;
?>

<!--pagination-->

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
