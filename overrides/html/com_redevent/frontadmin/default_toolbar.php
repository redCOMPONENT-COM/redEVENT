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
$support = 'https://www.maersktraining.com/b2b-support?tmpl=component';
$contact = 'https://www.maersktraining.com/b2b-contact?tmpl=component';
$filemanager =  'https://www.maersktraining.com/filemanager/file-are?tmpl=component';

$script = <<<JS
	window.addEvent('domready', function(){

		document.id('logo').addEvent('click', function(){
			document.id('search-course-reset').fireEvent('click');
			document.id('reset_person').fireEvent('click');
		});

	});
JS;

JFactory::getDocument()->addScriptDeclaration($script);
?>
<div id="logo" class="span4">
	<img title="" alt="" src="<?php echo JURI::root()?>templates/redweb/images/redadmin-logo.jpg">
</div>
<ul class="inline">
	<li>
		<form name="org-form" id="org-form" method="post">
			<?php if (count($this->organizations_options) > 2): ?>
                <div class="styled-select-admin">
                    <?php echo JHtml::_('select.genericlist', $this->organizations_options, 'filter_organization', '', 'value', 'text', $this->state->get('filter_organization')); ?>
                </div>
			<?php else: ?>
				<input type="hidden" name="filter_organization" id="filter_organization" value="<?php echo $this->organizations_options[1]->value; ?>" />
				<?php echo $this->organizations_options[1]->text; ?>
			<?php endif; ?>

			<input type="hidden" id="bookings_order" name="bookings_order" value="<?php echo $this->bookings_order; ?>"/>
			<input type="hidden" id="bookings_order_dir" name="bookings_order_dir" value="<?php echo $this->bookings_order_dir; ?>"/>
			<input type="hidden" id="bookings_limitstart" name="bookings_limitstart" value="<?php echo $this->bookings_limitstart; ?>"/>

			<input type="hidden" id="members_order" name="members_order" value="<?php echo $this->members_order; ?>"/>
			<input type="hidden" id="members_order_dir" name="members_order_dir" value="<?php echo $this->members_order_dir; ?>"/>
			<input type="hidden" id="members_limitstart" name="members_limitstart" value="<?php echo $this->members_limitstart; ?>"/>
		</form>
	</li>
	<li><?php echo JHTML::link('#', JText::_('COM_REDEVENT_FRONTEND_ADMIN_MY_ACCOUNT'), array('class' => 'btn myaccount', 'uid' => JFactory::getUser()->get('id'))); ?></li>
	<li><?php echo JHTML::link($support, JText::_('COM_REDEVENT_FRONTEND_ADMIN_SUPPORT'), array('class' => 'btn modal', 'rel' => "{size: {x: 780, y:600}}")); ?></li>
	<li><?php echo JHTML::link($contact, JText::_('COM_REDEVENT_FRONTEND_ADMIN_CONTACT'), array('class' => 'btn modal', 'rel' => "{size: {x: 780, y:600}}")); ?></li>	<li>
		<?php
		echo JHTML::link('index.php?option=com_users&task=user.logout&' . JSession::getFormToken() . '=1',
			JText::_('COM_REDEVENT_FRONTEND_ADMIN_LOGOUT'), array('class' => 'btn'));
		?>
	</li>
</ul>
<div class="clear"></div>
