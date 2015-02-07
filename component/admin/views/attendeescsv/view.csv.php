<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Export attendees to csv
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewAttendeescsv extends RViewCsv
{
	protected function getColumns()
	{
		return false;
	}

	public function display($tpl = null)
	{
		$filters = JFactory::getApplication()->input->get('jform', array(), 'array');
		$model = $this->getModel();

		$events = isset($filters['events']) ? $filters['events'] : false;
		JArrayHelper::toInteger($events);

		$fields = $model->getFields($filters['form']);

		$cols = array(
			JText::_('COM_REDEVENT_EVENT'),
			JText::_('COM_REDEVENT_DATE'),
			JText::_('COM_REDEVENT_VENUE'),
		);

		$text = "";

		foreach ($fields AS $f)
		{
			$cols[] = $f->field_header;
		}

		$stdcols = array(
			JText::_('COM_REDEVENT_REGDATE'),
			JText::_('COM_REDEVENT_IP_ADDRESS'),
			JText::_('COM_REDEVENT_UNIQUE_ID'),
			JText::_('COM_REDEVENT_USERNAME'),
			JText::_('COM_REDEVENT_ACTIVATED'),
			JText::_('COM_REDEVENT_WAITINGLIST'),
			JText::_('COM_REDEVENT_PRICE'),
			JText::_('COM_REDEVENT_PRICEGROUP'),
			JText::_('COM_REDEVENT_PAYMENT'),
		);
		$cols = array_merge($cols, $stdcols);
		$text .= $this->writecsvrow($cols);


		$attendees = $model->getRegisters($filters['form'],
			$events,
			$filters['category'],
			$filters['venue'],
			$filters['state'],
			$filters['attending']);

		if ($attendees)
		{
			foreach ((array) $attendees as $r)
			{
				$data = array(
					$r->title,
					RedeventHelper::isValidDate($r->dates) ? $r->dates : JText::_('COM_REDEVENT_OPEN_DATE'),
					$r->venue,
				);

				foreach ($fields AS $f)
				{
					$answer = $r->answers->getFieldAnswer($f->id);
					$data[] = is_array($answer) ? implode("\n", $answer) : $answer;
				}

				$svals = array(
					$r->uregdate,
					$r->uip,
					$r->course_code . '-' . $r->xref . '-' . $r->id,
					$r->name,
					$r->confirmed,
					$r->waitinglist,
					$r->price,
					$r->pricegroup,
					($r->paid ? JText::_('COM_REDEVENT_REGISTRATION_PAID') . ' / ' . $r->status : JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID') . ' / ' . $r->status),
				);
				$data = array_merge($data, $svals);
				$text .= $this->writecsvrow($data);
			}
		}

		$doc = JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		$date = md5(date('Y-m-d-h-i-s'));
		$title = JFile::makeSafe('attendees_' . $date . '.csv');
		header('Content-Disposition: attachment; filename="' . $title . '"');
		echo $text;
	}

	private function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
	{
		$delimiter_esc = preg_quote($delimiter, '/');
		$enclosure_esc = preg_quote($enclosure, '/');

		$output = array();

		foreach ($fields as $field)
		{
			$output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
				$enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
			) : $field;
		}

		return join($delimiter, $output) . "\n";
	}
}
