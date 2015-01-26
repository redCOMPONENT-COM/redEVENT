<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the EditeventView
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
*/
class RedeventViewEditevent extends RViewSite
{
	/**
	 * Creates the output for event submissions
	 *
	 * @param   string  $tpl  tpl
	 *
	 * @return void
	 *
	 * @since 0.4
	 */
	public function display($tpl=null)
	{
		$mainframe = & JFactory::getApplication();
		$user      = & JFactory::getUser();

		if (!$user->get('id'))
		{
			echo JText::_('COM_REDEVENT_LOGIN_TO_SUBMIT_EVENT');

			return;
		}

		$useracl = RedeventUserAcl::getInstance();

		if (!$useracl->canAddEvent())
		{
			echo JText::_('COM_REDEVENT_EDIT_EVENT_NOT_ALLOWED');

			return;
		}

		// Initialize variables
		$editor 	= JFactory::getEditor();
		$document 	= JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$params     = $mainframe->getParams();

		// Get Data from the model
		$row      = $this->get('Event');
		$customs  = $this->get('Customfields');
		$xcustoms = $this->get('XrefCustomfields');
		$roles    = $this->get('SessionRoles');
		$prices   = $this->get('SessionPrices');

		// Get requests
		$id					= JRequest::getInt('id');

		// Clean output
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, 'datdescription');

		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.modal', 'a.vmodal');

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
			$document->addStyleSheet($this->baseurl . '/components/com_redevent/assets/css/editevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$document->addScript('components/com_redevent/assets/js/attachments.js');
		$document->addScriptDeclaration('var removemsg = "' . JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG') . '";');

		$document->addScript('components/com_redevent/assets/js/xref_roles.js');
		$document->addScriptDeclaration('var txt_remove = "' . JText::_('COM_REDEVENT_REMOVE') . '";');
		$document->addScript('components/com_redevent/assets/js/xref_prices.js');

		// Set page title
		$id ? $title = $row->title . ' - ' . JText::_('COM_REDEVENT_EDIT_EVENT') : $title = JText::_('COM_REDEVENT_ADD_EVENT');
		$document->setTitle($title);

		// Get the menu object of the active menu item
		$menu   = JSite::getMenu();
		$item   = $menu->getActive();
		$params = $mainframe->getParams('com_redevent');

		// Pathway
		$pathway = $mainframe->getPathWay();
		$pathway->addItem($title, '');

		// Has the user access to the editor and the add venue screen
		$editoruser = $params->get('edit_description_allow_editor', 1);

		$canpublish = $useracl->canPublishEvent($id);

		// Transform <br /> and <br> back to \r\n for non editorusers
		if (!$editoruser)
		{
			$row->datdescription = RedeventHelper::br2break($row->datdescription);
		}

		// Get image information
		$dimage = $row ? RedeventImage::flyercreator($row->datimage) : null;

		// Set the info image
		$infoimage = JHTML::_('image', 'components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES'));

		// Create the stuff required for the venueselect functionality
		$url	= JURI::root();

		$js = "
			function reSelectVenue(id, venue) {
			document.getElementById('a_id').value = id;
			document.getElementById('a_name').value = venue;
			SqueezeBox.close();
		}";

		$document->addScriptDeclaration($js);

		// Categories selector
		$selected = array();

		if ($row)
		{
			foreach ((array) $row->categories as $cat)
			{
				$selected[] = $cat->id;
			}
		}

		$catoptions = $this->get('CategoryOptions');

		if (!$catoptions)
		{
			echo JText::_('COM_REDEVENT_EDITEVENT_FORBIDDEN_NO_CATEGORY_AVAILABLE');

			return;
		}

		$lists['categories'] = JHTML::_('select.genericlist', $catoptions, 'categories[]', 'class="inputbox required validate-categories" multiple="multiple" size="10"', 'value', 'text', $selected);

		if ($params->get('edit_recurrence', 0))
		{
			$document->addScript('components/com_redevent/assets/js/xref_recurrence.js');

			// Recurrence selector
			$recur_type = array( JHTML::_('select.option', 'NONE', JText::_('COM_REDEVENT_NO_REPEAT')),
					JHTML::_('select.option', 'DAILY', JText::_('COM_REDEVENT_DAILY')),
					JHTML::_('select.option', 'WEEKLY', JText::_('COM_REDEVENT_WEEKLY')),
					JHTML::_('select.option', 'MONTHLY', JText::_('COM_REDEVENT_MONTHLY')),
					JHTML::_('select.option', 'YEARLY', JText::_('COM_REDEVENT_YEARLY'))
			);
			$lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', ($row->rrules->type ? $row->rrules->type : 'NONE'));
		}

		// Published state selector
		$published = array( JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
				JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
		);
		$lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $row->published);

		$rolesoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_role')));
		$rolesoptions = array_merge($rolesoptions, $this->get('RolesOptions'));

		$pricegroupsoptions = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_PRICEGROUPS_SELECT_PRICEGROUP')));
		$pricegroupsoptions = array_merge($pricegroupsoptions, $this->get('PricegroupsOptions'));

		$this->assignRef('row',        $row);
		$this->assignRef('customs',    $customs);
		$this->assignRef('xcustoms',   $xcustoms);
		$this->assignRef('categories', $categories);
		$this->assignRef('editor',     $editor);
		$this->assignRef('dimage',     $dimage);
		$this->assignRef('infoimage',  $infoimage);
		$this->assignRef('editoruser', $editoruser);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item',       $item);
		$this->assignRef('params',     $params);
		$this->assignRef('lists',      $lists);
		$this->assignRef('canpublish', $canpublish);
		$this->assignRef('referer',    JRequest::getWord('referer'));
		$this->assign('title',         $title);
		$this->assignRef('access', RedeventHelper::getAccesslevelOptions());
		$this->assignRef('roles', $roles);
		$this->assignRef('rolesoptions', $rolesoptions);
		$this->assignRef('prices', $prices);
		$this->assignRef('pricegroupsoptions', $pricegroupsoptions);

		parent::display($tpl);
	}
}
