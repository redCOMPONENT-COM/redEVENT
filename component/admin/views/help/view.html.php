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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList Help screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewHelp extends JView {

	function display($tpl = null) {

		//Load filesystem folder and pane behavior
		jimport('joomla.html.pane');
		jimport( 'joomla.filesystem.folder' );

		//initialise variables
		$document		= & JFactory::getDocument();
		$lang 			= & JFactory::getLanguage();
		$pane 			= & JPane::getInstance('sliders');
		$user			= & JFactory::getUser();

		//get vars
		$helpsearch 	= JRequest::getString( 'search' );

		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Create Submenu
    ELAdmin::setMenu();

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_HELP' ), 'help' );

		// Check for files in the actual language
		$langTag = $lang->getTag();

		if ( !JFolder::exists( JPATH_SITE . DS.'administrator'.DS.'components'.DS.'com_redevent/help'.DS .$langTag ) ) {
			$langTag = 'en-GB';		// use english as fallback
		}

		//search the keyword in the files
		$toc 		= RedEventViewHelp::getHelpToc( $helpsearch );

		//assign data to template
		$this->assignRef('pane'			, $pane);
		$this->assignRef('langTag'		, $langTag);
		$this->assignRef('helpsearch'	, $helpsearch);
		$this->assignRef('toc'			, $toc);

		parent::display($tpl);
	}

	/**
 	* Compiles the help table of contents
 	* Based on the Joomla admin component
 	*
 	* @param string A specific keyword on which to filter the resulting list
 	*/
	function getHelpTOC( $helpsearch )
	{
		$lang =& JFactory::getLanguage();
		jimport( 'joomla.filesystem.folder' );

		// Check for files in the actual language
		$langTag = $lang->getTag();

		if( !JFolder::exists( JPATH_SITE . DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'help'.DS .$langTag ) ) {
			$langTag = 'en-GB';		// use english as fallback
		}
		$files = JFolder::files( JPATH_SITE . DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'help'.DS.$langTag, '\.xml$|\.html$' );

		$toc = array();
		foreach ($files as $file) {
			$buffer = file_get_contents( JPATH_SITE . DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'help'.DS.$langTag.DS.$file );
			if (preg_match( '#<title>(.*?)</title>#', $buffer, $m )) {
				$title = trim( $m[1] );
				if ($title) {
					if ($helpsearch) {
						if (JString::strpos( strip_tags( $buffer ), $helpsearch ) !== false) {
							$toc[$file] = $title;
						}
					} else {
						$toc[$file] = $title;
					}
				}
			}
		}
		asort( $toc );
		return $toc;
	}
}
