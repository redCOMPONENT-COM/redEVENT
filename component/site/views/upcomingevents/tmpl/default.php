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
<div id="upcomingevents" class="<?= $this->params->get('pageclass_sfx') ?>">

<?php if ($this->params->def( 'show_page_heading', 1 )) : ?>
	<h1 class="componentheading"><?php echo $this->escape($this->params->get( 'page_title')); ?></h1>
<?php endif; ?>

<?php if ($this->params->get('showintrotext')) : ?>
  <div class="description no_space floattext">
    <?php echo $this->params->get('introtext'); ?>
  </div>
<?php endif; ?>

<?php
if (count($this->upcomingevents) == 0) {
	echo JText::_('COM_REDEVENT_NO_UPCOMING_EVENTS');
}
else {
	echo $this->loadTemplate('courseinfo');
} ?>
</div>
