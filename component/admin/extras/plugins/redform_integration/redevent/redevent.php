<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redFORM
 * @copyright redFORM (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

// Import library dependencies
jimport('joomla.plugin.plugin');

JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
JLoader::registerPrefix('Redform', JPATH_LIBRARIES . '/redform');

class plgRedform_integrationRedevent extends JPlugin {

	private $_db = null;

	private $rfcore;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();

		$this->_db = Jfactory::getDBO();
	}

	/**
	 * returns a title for the object reference in redform
	 *
	 * @param   string  $object_key            should be 'redevent' for this plugin to do something
	 * @param   string  $submit_key            submit ley
	 * @param   object  &$paymentDetailFields  object to return
	 *
	 * @return bool true on success
	 *
	 * @throws Exception
	 */
	public function getRFSubmissionPaymentDetailFields($object_key, $submit_key, &$paymentDetailFields)
	{
		if (!$object_key === 'redevent')
		{
			return false;
		}

		$paymentDetailFields = new stdclass;

		$query = ' SELECT e.title, x.dates, x.enddates, x.times, x.endtimes, e.course_code, r.id AS attendee_id'
		       . ' , v.venue, x.id AS xref '
		       . ' FROM #__redevent_event_venue_xref AS x '
		       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
		       . ' LEFT JOIN #__redevent_venues AS v ON v.id = x.venueid '
		       . ' INNER JOIN #__redevent_register AS r ON r.xref = x.id '
		       . ' WHERE r.submit_key = ' . $this->_db->Quote($submit_key);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		if (!$res)
		{
			throw new Exception('Registration not found for specified key');
		}

		if ($res->dates && strtotime($res->dates))
		{
			if ($res->times && $res->times != '00:00:00')
			{
				$date = strftime('%c', strtotime($res->dates . ' ' . $res->times));
			}
			else
			{
				$date = strftime('%x', strtotime($res->dates));
			}
		}
		else
		{
			$date = JText::_('PLG_REDFORM_INTEGRATION_REDFORM_OPEN_DATE');
		}

		$paymentDetailFields->title = JText::sprintf('PLG_REDFORM_INTEGRATION_REDFORM_TITLE',
			$res->title,
			$res->venue,
			$date
		);

		$fullname = $this->getFullname($submit_key);

		$paymentDetailFields->adminDesc = JText::sprintf('PLG_REDFORM_INTEGRATION_REDFORM_ADMIN_DESC',
			$fullname,
			$res->title,
			$res->venue,
			$date
		);

		$paymentDetailFields->uniqueid = RedeventHelper::getRegistrationUniqueId($res);

		return true;
	}

	/**
	 * Return fullname(s) associated to sumbission
	 *
	 * @param   string  $submit_key  submit_key
	 *
	 * @return string
	 */
	private function getFullname($submit_key)
	{
		$sids = $this->getSids($submit_key);

		$sidsAnswers = $this->getRedformCore()->getSidsFieldsAnswers($sids);

		$fullnames = array();

		foreach ($sidsAnswers as $answers)
		{
			if ($fullname = $this->getAnswerFullname($answers))
			{
				$fullnames[] = $fullname;
			}
		}

		return implode(', ', $fullnames);
	}

	/**
	 * Return fullname for answers
	 *
	 * @param   array  $answers  answers from redform
	 *
	 * @return bool
	 */
	private function getAnswerFullname($answers)
	{
		foreach ($answers as $field)
		{
			if ($field->fieldtype == 'fullname')
			{
				return $field->answer;
			}
		}

		return false;
	}

	/**
	 * Sids for submit_key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return mixed
	 */
	private function getSids($submit_key)
	{
		$rfcore = $this->getRedformCore();
		$sids = $rfcore->getSids($submit_key);

		return $sids;
	}

	/**
	 * return redformcore object
	 *
	 * @return RedformCore
	 */
	private function getRedformCore()
	{
		if (!$this->rfcore)
		{
			$this->rfcore = new RedformCore;
		}

		return $this->rfcore;
	}
}
