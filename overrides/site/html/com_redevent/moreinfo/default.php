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
<div id="moreinfoform">
<form action="<?php echo JRoute::_($this->action); ?>" method="post" class="moreinfo">

<h1><?php echo JText::_('COM_REDEVENT_MOREINFO_TITLE'); ?></h1>
<p><?php echo JText::_('COM_REDEVENT_MOREINFO_INTRO'); ?></p>

<label for="name"><?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_NAME'); ?>
</label>
<input type="text" name="name" value="<?php echo $this->user->get('name'); ?>" placeholder="<?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_NAME'); ?>" />

<label for="email"><?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_EMAIL'); ?>
</label>
<input type="text" name="email" value="<?php echo $this->user->get('email'); ?>" placeholder="<?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_EMAIL'); ?>" />

<label for="company"><?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_COMPANY'); ?>
</label>
<input type="text" name="company" placeholder="<?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_COMPANY'); ?>"/>

<label for="country"><?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_COUNTRY'); ?>
</label>
<input type="text" name="contry" placeholder="<?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_COUNTRY'); ?>"/>

<label for="phonenumber"><?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_PHONENUMBER'); ?>
</label>
<input type="text" name="phonenumber" placeholder="<?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_PHONENUMBER'); ?>"/>

<label for="comments"><?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_COMMENTS'); ?>
</label>
<textarea name="comments" cols="20" rows="10" placeholder="<?php echo JText::_('COM_REDEVENT_MOREINFO_LABEL_COMMENTS'); ?>"></textarea>

<button type="submit"><?php echo Jtext::_('COM_REDEVENT_MOREINFO_SUBMIT'); ?></button>
<div class="moreinfospacer"></div>

<input type="hidden" name="xref" value="<?php echo $this->xref?>"/>
<input type="hidden" name="task" value="moreinfo.submitinfo"/>
<input type="hidden" name="uid" value="<?php echo $this->user->get('id'); ?>"/>
</form>
</div>
