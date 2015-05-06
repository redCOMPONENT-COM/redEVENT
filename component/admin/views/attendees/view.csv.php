<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * CSV View class for Attendees screen
 *
 * @TODO: not used at the moment !
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedEventViewAttendees extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
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

		$stdcols = array(JText::_('COM_REDEVENT_REGDATE'),
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
		$text .= RedeventHelper::writecsvrow($cols);

		if (count($registers))
		{
			foreach ((array) $registers as $r)
			{
				$data = array();

				foreach ($fields AS $f)
				{
					$cleanfield = 'field_' . $f->id;

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

				$svals = array($r->uregdate,
					$r->uip,
					$event->course_code . '-' . $event->xref . '-' . $r->id,
					$r->name,
					$r->confirmed,
					$r->cancelled,
					$r->waitinglist,
					$r->price,
					$r->pricegroup,
					($r->paid ? JText::_('COM_REDEVENT_REGISTRATION_PAID') . ' / ' . $r->status
						: JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID') . ' / ' . $r->status
					),
				);
				$data = array_merge($data, $svals);
				$text .= RedeventHelper::writecsvrow($data);
			}
		}

		$event->dates = RedeventHelper::isValidDate($event->dates) ? $event->dates : JText::_('COM_REDEVENT_OPEN_DATE');
		$title = JFile::makeSafe($event->title . '_' . $event->dates . '_' . $event->venue . '.csv');

		$doc = JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		header('Content-Disposition: attachment; filename="' . $title . '"');

		echo $text;

		$app->close();
	}
}
