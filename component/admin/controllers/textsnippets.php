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

 /**
 * redEVENT Text Library Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventControllerTextsnippets extends FOFController {
  
	/**
	 * start export screens
	 * 
	 */
	function import()
	{
		JRequest::setVar( 'view', 'textsnippets' );
		JRequest::setVar( 'layout', 'import' );
		parent::display();
	}

	function export()
	{
		$app			=& JFactory::getApplication();
				
		$model = $this->getModel('textsnippets');
		$rows = $model->export();

		header('Content-Type: text/x-csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=textlibrary.csv');
		header('Pragma: no-cache');

		$k = 0;
		$export = '';
		$col = array();
				
		if (count($rows))
		{		
			$header = current($rows);
			$export .= redEVENTHelper::writecsvrow(array_keys($header));

			$current = 0; // current event
			foreach($rows as $data)
			{			
				$export .= redEVENTHelper::writecsvrow($data);
			}
	
			echo $export;
		}

		$app->close();
	}
	
	function doimport()
	{
		$replace = JRequest::getVar('replace', 0, 'post', 'int');

		$msg = '';
		if ( $file = JRequest::getVar( 'import', null, 'files', 'array' ) )
		{
			$handle = fopen($file['tmp_name'],'r');
			if(!$handle)
			{
				$msg = JText::_('COM_REDEVENT_Cannot_open_uploaded_file.');
				$this->setRedirect( 'index.php?option=com_redevent&view=textsnippets&task=import', $msg, 'error' );
				return;
			}
			 
			// get fields, on first row of the file
			$fields = array();
			if ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE )
			{
				$numfields = count($data);
				for ($c=0; $c < $numfields; $c++)
				{
					$fields[$c]=$data[$c];
				}
			}
			// If there is no validated fields, there is a problem...
			if ( !count($fields) ) {
				$msg .= "<p>Error parsing column names. Are you sure this is a proper csv export ?<br />try to export first to get an example of formatting</p>\n";
				$this->setRedirect( 'index.php?option=com_redevent&view=textsnippets&task=import', $msg, 'error' );
				return;
			}
			else {
				$msg .= "<p>".$numfields." fields found in first row</p>\n";
				$msg .= "<p>".count($fields)." fields were kept</p>\n";
			}
			// Now get the records, meaning the rest of the rows.
			$records = array();
			$row = 1;
			while ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE )
			{
				$num = count($data);
				if ($numfields != $num) {
					$msg .= "<p>Wrong number of fields ($num) record $row<br /></p>\n";
				}
				else {
					$r = new stdclass();
					// only extract columns with validated header, from previous step.
					foreach ($fields as $k => $v) {
						$r->$v = $data[$k];
					}
					$records[] = $r;
				}
				$row++;
			}
			fclose($handle);
			$msg .= "<p>total records found: ".count($records)."<br /></p>\n";
			 
			// database update
			if (count($records))
			{
				$model = $this->getModel('textlibrary');
				$result = $model->import($records, $replace);
				$msg .= "<p>total added records: ".$result['added']."<br /></p>\n";
				$msg .= "<p>total updated records: ".$result['updated']."<br /></p>\n";
			}
			$this->setRedirect( 'index.php?option=com_redevent&view=textsnippets&task=import', $msg );
		}
		else {
			parent::display();
		}
	}
}
