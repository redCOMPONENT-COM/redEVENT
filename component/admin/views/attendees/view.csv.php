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
 * View class for the EventList attendees screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewAttendees extends JView {

	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		jimport('joomla.filesystem.file');

		$model = $this->getModel();
		$model->setState('getAllFormFields', true);
		$model->setState('unlimited', true);
		$event     = $this->get('Event');
		$fields    = $model->getFields();
		$registers = $model->getData();

		$text = "";

		foreach ($fields AS $f)
		{
			$cols[] = $f->field_header;
		}

		$stdcols = array( JText::_('COM_REDEVENT_REGDATE'),
		               JText::_('COM_REDEVENT_IP_ADDRESS'),
		               JText::_('COM_REDEVENT_UNIQUE_ID'),
		               JText::_('COM_REDEVENT_USERNAME'),
		               JText::_('COM_REDEVENT_ACTIVATED'),
		               JText::_('COM_REDEVENT_CANCELLED'),
		               JText::_('COM_REDEVENT_WAITINGLIST'),
		               JText::_('COM_REDEVENT_PRICE'),
		               JText::_('COM_REDEVENT_PRICEGROUP'),
		               JText::_('COM_REDEVENT_PAYMENT'),
		               );
		$cols = array_merge($cols, $stdcols);
		$text .= $this->writecsvrow($cols);

		if (count($registers))
		{
			foreach((array) $registers as $r)
			{
				$data = array();

				foreach ($fields AS $f)
				{
					$cleanfield = 'field_'.$f->id;

					if (isset($r->$cleanfield))
					{
						$val = $r->$cleanfield;

						if (stristr($val, '~~~'))
						{
							$val = str_replace('~~~', '\n', $val);
						}

						$data[] = $val;
					}
					else
					{
						$data[] = '';
					}
				}

				$svals = array( $r->uregdate,
				               $r->uip,
				               $event->course_code .'-'. $event->xref .'-'. $r->id,
				               $r->name,
				               $r->confirmed,
				               $r->cancelled,
				               $r->waitinglist,
				               $r->price,
				               $r->pricegroup,
				               ($r->paid ? JText::_('COM_REDEVENT_REGISTRATION_PAID').' / '.$r->status : JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID').' / '.$r->status),
				             );
				$data = array_merge($data, $svals);
				$text .= $this->writecsvrow($data);
			}
		}

		$event->dates = redEVENTHelper::isValidDate($event->dates) ? $event->dates : JText::_('COM_REDEVENT_OPEN_DATE');
		$title = JFile::makeSafe($event->title .'_'. $event->dates .'_'. $event->venue .'.csv');

		$doc = JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		header('Content-Disposition: attachment; filename="'.$title.'"');

		echo $text;

		$app->close();
	}

	public function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
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
