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
$my = JRoute::_('index.php?option=com_redmember&view=userdetail&layout=alterdetail&tmpl=component');
// $contact = 'index.php?option=com_content&view=article&id=354&catid=73&tmpl=component';
// $support = 'index.php?option=com_content&view=article&id=353&catid=73&tmpl=component';
$support = 'http://www.maersktraining.com/b2b-support?tmpl=component';
$contact = 'http://www.maersktraining.com/b2b-contact?tmpl=component';
?>
<div id="logo" class="span4">
	<a class="image" href="">			
		<img title="" alt="" src="<?php echo JURI::root()?>templates/redweb/images/redadmin-logo.jpg">
	</a>
</div>
<ul class="inline">
	<li><?php echo JHtml::link($my, JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_HELLO_USER_S', JFactory::getUser()->get('name')), array('class' => 'modal')); 
			  echo " <a>-</a> ";
			  echo JHTML::link('index.php?option=com_users&task=user.logout', JText::_('COM_REDEVENT_FRONTEND_ADMIN_LOGOUT')); 
	?></li>	
	<li><?php echo JHTML::link($my, JText::_('COM_REDEVENT_FRONTEND_ADMIN_MY_ACCOUNT'), array('class' => 'btn modal')); ?></li>
	<li><?php echo JHTML::link(JRoute::_('index.php?option=com_redmember&view=filemanager'), JText::_('COM_REDEVENT_FRONTEND_ADMIN_FILE_ARCHIVE'), array('class' => 'btn')); ?></li>
	<li><?php echo JHTML::link($support, JText::_('COM_REDEVENT_FRONTEND_ADMIN_SUPPORT'), array('class' => 'btn modal', 'rel' => "{size: {x: 780, y:600}}")); ?></li>
	<li><?php echo JHTML::link($contact, JText::_('COM_REDEVENT_FRONTEND_ADMIN_CONTACT'), array('class' => 'btn modal', 'rel' => "{size: {x: 780, y:600}}")); ?></li>
</ul>
<div class="clear"></div>