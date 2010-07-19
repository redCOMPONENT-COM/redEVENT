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
 	 * @see redFORM::saveform
 	 * User gets redirected here from the redFORM model where the form data is first saved
	 */
	function display($tpl = null)
	{
		global $mainframe;
		
		switch ($this->getLayout())
		{
			case 'confirmed':
				return $this->_displayConfirmed($tpl);
				
			case 'review':
				return $this->_displayReview($tpl);
		}
		
		/* Display page */
		parent::display($tpl);
	}
	
	/**
	 * creates output for confirm page
	 * 
	 * @param $tpl
	 */
	function _displayConfirmed($tpl = null)
	{
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		$tags = new redEVENT_tags();
		$tags->setXref(JRequest::getVar('xref'));
		
		$model_details = $this->getModel('Details', 'RedEventModel');
		$model_details->setXref(JRequest::getVar('xref'));
		$event = $model_details->getDetails();
//		echo '<pre>';print_r($event); echo '</pre>';exit;
		
		/* Check if we have and clean up confirmation message */
		if (strlen($event->confirmation_message) > 0) {
			/* Assign to jview */
			$this->assignRef('message', $event->confirmation_message);
		}		
		
		$this->assignRef('tags',   $tags);
		$this->assignRef('event',  $event);
		$this->assignRef('action', JRequest::getVar('action'));
		
		parent::display($tpl);
	}

	/**
	 * creates output for review page
	 * 
	 * @param $tpl
	 */
	function _displayReview($tpl = null)
	{
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		
		/* Get the action */
		$action = JRequest::getVar('action');
		
		
		/* Collect registration details */
		$registration	= $this->get('Details');
		/* Start the tag replacer */
		$tags = new redEVENT_tags();
		$tags->setXref($registration['event']->xref);
		$this->assignRef('tags', $tags);
			
		JRequest::setVar('answers', $registration['answers']);
		JRequest::setVar('xref', $registration['event']->xref);
		
		/* Assign to jview */
		$this->assignRef('registration', $registration);
		$this->assignRef('action', $action);	
		$this->assignRef('event',  $registration['event']);		
			
		/* Display page */
		parent::display($tpl);
	}
	
	/**
	 * structures the keywords
	 *
 	 * @since 0.9
 	 * @todo: not used ?
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