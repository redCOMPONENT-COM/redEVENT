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
 * CSV Details View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewDetails extends JView
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
		$model = $this->getModel();
		
		$event     = $this->get('Details');		
		$registers = $model->getRegisters(true);
		
		$text = "";
		if (count($registers))
		{
			$fields = array();
			foreach ($registers[0]->fields AS $f) {
				$fields[] = $f;
			}
			$text .= $this->writecsvrow($fields);
		}
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
		$title = JFile::makeSafe($event->title .'_'. $event->dates .'_'. $event->venue .'.csv');
		header('Content-type: application/excel');
		header('Content-Disposition: attachment; filename="'.$title.'"');
		echo $text;
	}
	
	function writecsvrow($dataArray, $delimiter = ';',$enclosure = '"')
	{
		$fields = array();
		$writeDelimiter = FALSE;
		foreach($dataArray as $dataElement) 
		{
			$dataElement=str_replace("\"", "\"\"", $dataElement);
			$fields[] = $enclosure . $dataElement . $enclosure;
		}
		return implode($delimiter, $fields) ."\n";
	}
}
?>