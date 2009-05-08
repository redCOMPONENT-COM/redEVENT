<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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
class RedeventViewSignup extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null) {
		global $mainframe;
		
		$document 	= & JFactory::getDocument();
		$document->setTitle(JText::_('Signup'));
		
		/* Load the event details */
		$course = $this->get('Details');
		$venue = $this->get('Venue');
		
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		$tags = new redEVENT_tags;
		$this->assignRef('tags', $tags);
		
		switch (JRequest::getCmd('subtype', 'webform')) {
			case 'email':
				if (JRequest::getVar('sendmail') == '1') {
					$this->tmp_xref = JRequest::getInt('xref');
					$this->tmp_id = JRequest::getInt('id');
					$model_signup = $this->getModel('Signup');
					/* Send the user the signup email */
					$result = $model_signup->getSendSignupEmail($tags, $course->send_pdf_form);
					$this->assignRef('result', $result);
					JRequest::setVar('xref', $this->tmp_xref);
					JRequest::setVar('id', $this->tmp_id);
				}
				/* Load the view */
				$this->assignRef('page', $course->submission_type_email);
				$tpl = 'email';
				break;
			case 'formaloffer':
				if (JRequest::getVar('sendmail') == '1') {
					$this->tmp_xref = JRequest::getInt('xref');
					$this->tmp_id = JRequest::getInt('id');
					$model_details = $this->getModel('Details');
					$model_signup = $this->getModel('Signup');
					$model_details->getDetails();
					$venues = $model_details->getVenues();
					/* Send the user the formal offer email */
					$result = $model_signup->getSendFormalOfferEmail($tags);
					$this->assignRef('result', $result);
					JRequest::setVar('xref', $this->tmp_xref);
					JRequest::setVar('id', $this->tmp_id);
				}
				/* Load the view */
				$this->assignRef('page', $course->submission_type_formal_offer);
				$tpl = 'formaloffer';
				break;
			case 'phone':
				/* Load the view */
				$this->assignRef('page', $course->submission_type_phone);
				$tpl = 'phone';
				break;
			case 'webform':
			default:
				$this->assignRef('page', $course->submission_type_webform);
				break;
		}
		
		$this->assignRef('course', $course);
		$this->assignRef('venue', $venue);
		
		parent::display($tpl);
	}
}
?>