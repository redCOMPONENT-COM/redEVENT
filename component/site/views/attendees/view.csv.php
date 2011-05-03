<?php
/**
 * @version 1.0 $Id: view.html.php 1625 2009-11-18 16:54:27Z julien $
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
 * CSV Attendees View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewAttendees extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 2.0
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() == 'exportattendees') {
			return $this->_displayAttendees($tpl);
		}
		echo 'layout not found';
	}
	
	/**
	 * Creates the attendees output for the details view
	 *
 	 * @since 2.0
	 */
	function _displayAttendees($tpl = null)
	{
		jimport('joomla.filesystem.file');
		
		$model = $this->getModel();
		
		if (!$this->get('ViewAttendees')) {
			JError::raiseError(403, 'Not authorized');
		}
		
		$event     = $this->get('Session');		
		$registers = $model->getRegisters(true, true);
		
		$text = "";
		if (count($registers))
		{
			$fields = array();
			foreach ($registers[0]->fields AS $f) {
				$fields[] = $f;
			}
			$text .= $this->writecsvrow($fields);

			foreach((array) $registers as $r) 
			{
				$data = array();
				foreach ($r->answers as $val)
				{
					if (stristr($val, '~~~')) {
						$val = str_replace('~~~', '\n', $val);
					}
					$data[] = $val;
				}
				$text .= $this->writecsvrow($data);
			}
		}
		else {
			//$text = "no attendees";
		}
		if (!redEVENTHelper::isValidDate($event->dates)) {
			$event->dates = JText::_('OPEN DATE');
		}
		$title = JFile::makeSafe($event->full_title .'_'. $event->dates .'_'. $event->venue .'.csv');
				
		$doc =& JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="'.$title.'"');
		header('Pragma: no-cache');
		echo $text;
	}
		
	function writecsvrow($fields, $delimiter = ',', $enclosure = '"') 
	{
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
        ) : $field;
    }

    return join($delimiter, $output) . "\n";
	} 
}
?>