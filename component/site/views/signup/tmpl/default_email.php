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
<?php if (isset($this->result)) { ?>
	<?php if ($this->result) { ?><div id="result"><?php echo JText::_('COM_REDEVENT_SIGNUP_RESULT_OK'); ?></div><?php } ?>
	<?php if (!$this->result) { ?><div id="result"><?php echo JText::_('COM_REDEVENT_SIGNUP_RESULT_NOK'); ?></div><?php } ?>
<?php } ?>
<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
	<?php echo $this->tags->ReplaceTags($this->page); ?>
	<input type="hidden" name="task" value="signup" />
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="view" value="signup" />
	<input type="hidden" name="subtype" value="email" />
	<input type="hidden" name="sendmail" value="1" />
	<input type="hidden" name="xref" value="<?php echo JRequest::getVar('xref'); ?>" />
	<input type="hidden" name="id" value="<?php echo JRequest::getVar('id'); ?>" />
</form>