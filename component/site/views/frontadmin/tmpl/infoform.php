<?php
/**
 * @package    Redevent
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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
?>
<div id="b2b-session-info-form" class="akeeba-bootstrap">

	<form action="<?php echo $this->action; ?>" method="post" class="form-horizontal" role="form">
		<h1><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_TITLE'); ?></h1>
		<p><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_INTRO'); ?></p>

		<div class="form-group">
			<label for="question"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_QUESTION_LABEL'); ?></label>
			<textarea name="question" cols="50" rows="10" class="form-control"></textarea>
		</div>

		<button type="submit" class="btn btn-default"><?php echo Jtext::_('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_SUBMIT'); ?></button>

		<input type="hidden" name="xref" value="<?php echo $this->xref?>"/>
		<input type="hidden" name="controller" value="frontadmin"/>
		<input type="hidden" name="task" value="submitinfoform"/>
	</form>

</div>
