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
			<div class="styled-select-admin">
				<?php echo JHtml::_('select.genericlist', $this->organizations_options, 'filter_organization', '', 'value', 'text', $this->state->get('filter_organization')); ?>
			</div>
			<input type="hidden" name="limit" value="<?php echo $this->state->get('limit'); ?>"/>
		</form>
	</li>
	<li><?php echo JHTML::link('#', JText::_('COM_REDEVENT_FRONTEND_ADMIN_MY_ACCOUNT'), array('class' => 'btn myaccount', 'uid' => JFactory::getUser()->get('id'))); ?></li>
	<li><?php echo JHTML::link($support, JText::_('COM_REDEVENT_FRONTEND_ADMIN_SUPPORT'), array('class' => 'btn modal', 'rel' => "{handler: 'iframe', size: {x: 780, y:600}}")); ?></li>
	<li><?php echo JHTML::link($contact, JText::_('COM_REDEVENT_FRONTEND_ADMIN_CONTACT'), array('class' => 'btn modal', 'rel' => "{handler: 'iframe', size: {x: 780, y:600}}")); ?></li>
	<?php if ($this->useracl->canAddSession()): ?>
	<li>
			<?php echo JHtml::link(RedeventHelperRoute::getAddSessionTaskRoute().'&tmpl=component',
				Jtext::_('COM_REDEVENT_FRONTEND_ADMIN_COURSE_BUTTON_ADD_SESSION'),
				array('class' => 'btn xrefmodal')); ?>
	</li>
	<?php endif; ?>
	<li>
		<?php
		$return = JRoute::_('index.php');
		echo JHTML::link('index.php?option=com_users&task=user.logout&' . JSession::getFormToken() . '=1'
			. '&return=' . base64_encode($return),
			JText::_('COM_REDEVENT_FRONTEND_ADMIN_LOGOUT'), array('class' => 'btn'));
		?>
	</li>
</ul>
<div class="clear"></div>
