<?php
/**
 * @version 1.0 $Id: default.php 30 2009-05-08 10:22:21Z roland $
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

defined('_JEXEC') or die('Restricted access');

use Aesir\App;

$activeMember = App::getMember();
$document = &JFactory::getDocument();
$renderer   = $document->loadRenderer('modules');
$position_breadcrumbs   = 'breadcrumbs';
$position_menu   = 'management-menu';
$position_user_menu   = 'user-management-menu';
$position_logout   = 'logout';
$this->returnAppend = '&return=' . base64_encode(RedeventHelperRoute::getMyeventsRoute());
?>

<div class="container">
	<?php echo $renderer->render($position_breadcrumbs, $options, null); ?>
	<div class="aesir-container user-management-cotaniner">
		<div class="row">
			<div class="col-xs-6 col-sm-11">
				<?php if ($activeMember->isLoaded() && $activeMember->getId() != 0): ?>
					<?php echo $renderer->render($position_menu, $options, null); ?>
				<?php else : ?>
					<?php echo $renderer->render($position_user_menu, $options, null); ?>
				<?php endif ?>
			</div>
			<div class="col-xs-6 col-sm-1">
				<?php echo $renderer->render($position_logout, $options, null); ?>
			</div>
		</div>
		<div class="aesir-item-header reditem-content-header text-center">
			<h1 class="header-title"><?php echo $this->escape($this->pagetitle); ?></h1>
		</div>
		<div class="row">
			<div id="redevent" class="my-course-events el_eventlist<?= $this->params->get('pageclass_sfx') ?> col-xs-12 col-lg-10 col-lg-push-1">
				
				<div id="result_adttending">
					<?php echo $this->loadTemplate('attending'); ?>
				</div>
				<div id="result_attended">
					<?php echo $this->loadTemplate('attended'); ?>
				</div>
				<!-- <h2><?php echo JText::_('COM_REDEVENT_MYEVENTS_MANAGED_SESSIONS'); ?></h2> -->
				<div id="result_sessions">
				<?php echo $this->loadTemplate('sessions'); ?>
				</div>

				<!-- <h2><?php echo JText::_('COM_REDEVENT_MYEVENTS_MANAGED_EVENTS'); ?></h2> -->
				<div id="result_events">
					<?php echo $this->loadTemplate('events'); ?>
				</div>

				<!-- <h2><?php echo JText::_('COM_REDEVENT_MYEVENTS_MANAGED_VENUES'); ?></h2> -->
				<div id="result_venue">
					<?php echo $this->loadTemplate('venues'); ?>
				</div>
			</div>
		</div>
	</div>
</div>
