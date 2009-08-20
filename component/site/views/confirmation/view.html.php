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

jimport( 'joomla.application.component.view');

/**
 * HTML Details View class of the EventList component
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewConfirmation extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null)
	{
		global $mainframe;
		
		/* Set which page to show */
		$tpl = JRequest::getVar('page', null);
		
		/* Check submission key */
		$key_ok = $this->get('CheckSubmitKey');
		
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		
		/* Get the action */
		$action = JRequest::getVar('action');
		
		/* Start the tag replacer */
		$tags = new redEVENT_tags;
		$this->assignRef('tags', $tags);
		
		if ($key_ok) {
			switch ($tpl) {
				case 'confirmation':
				case 'print':					
					/* Collect registration details */
					$registration	= $this->get('Details');
					
					if (empty($registration['event']->review_message)) 
					{
						// nothing in review message, so skip it !
						$redirect = 'index.php?option=com_redevent&task='.JRequest::getVar('event_task')
						           .'&xref='.JRequest::getInt('xref')
						           .'&submit_key='.JRequest::getVar('submit_key')
						           .'&view=confirmation&page=final'
                       .'&action=confirmreg'
						           ;
						$mainframe->redirect(JRoute::_($redirect, false));
						return;			
					}
					
					JRequest::setVar('answers', $registration['answers']);
					JRequest::setVar('xref', $registration['event']->xref);
					
					/* Assign to jview */
					$this->assignRef('registration', $registration);
					$this->assignRef('action', $action);
					break;
				case 'final':
					if ($action == 'confirmreg') {
						/* Save the confirmation */
						$result = $this->get('MailConfirmation');
						if ($result) {
							$model_details = $this->getModel('Details', 'RedEventModel');
							$model_details->setXref($result->xref);
							$row = $model_details->getDetails();
							/* Check if we have and clean up confirmation message */
							if (strlen($row->confirmation_message) > 0) {
								/* Assign to jview */
								$this->assignRef('message', $row->confirmation_message);
							}
							/* No confirmation message, default back to event page */
							else {
								/* Assign to jview */
								$this->assignRef('message', JText::_('CONFIRM_REGISTRATION'));
							}
						}
					}
					else if ($action == 'cancelreg') {
						$this->get('CancelConfirmation');
						$this->assignRef('message', JText::_('CANCEL_CONFIRMATION'));
					}
					break;
			}
		}
		else {
			$registration = false;
			$this->assignRef('registration', $registration);
			$this->assignRef('message', JText::_('NO_VALID_REGISTRATION'));
		}
		
		/* Display page */
		parent::display($tpl);
	}

	/**
	 * structures the keywords
	 *
 	 * @since 0.9
	 */
	function keyword_switcher($keyword, $row, $formattime, $formatdate) {
		switch ($keyword) {
			case "catsid":
				// TODO: fix for multiple cats
				//$content = $row->catname;
        $content = '';
				break;
			case "a_name":
				// $content = $row->venue;
				$content = '';
				break;
			case "times":
			case "endtimes":
				if ($row->$keyword) {
					$content = strftime( $formattime ,strtotime( $row->$keyword ) );
				} else {
					$content = '';
				}
				break;
			case "dates":
			case "enddates":
				$content = strftime( $formatdate ,strtotime( $row->$keyword ) );
				break;
			default:
				$content = $row->$keyword;
				break;
		}
		return $content;
	}
}
?>