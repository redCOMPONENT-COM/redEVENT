<?php
/**
 * recurrenceTest
 *
 * @package    Redevent.UnitTest
 * @copyright  Copyright (C) 2014 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

// Register library prefix
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

/**
 * Test class for recurrence.
 *
 * @package  Redevent.UnitTest
 */
class recurrenceTest extends TestCaseDatabase
{
	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestGetNextData()
	{
		$data = array();

		// Daily
		$rrule = 'RRULE:FREQ=DAILY;INTERVAL=3;UNTIL=20200901T000000;WKST=MO;';
		$xref = new stdclass;
		$xref->dates = '2012-12-04';
		$xref->enddates = '2012-12-04';
		$xref->times = '14:00';
		$xref->endtimes = '16:00';
		$xref->registrationend = null;
		$xref->count = 12;

		$expect = clone $xref;
		$expect->dates = '2012-12-07';
		$expect->enddates = '2012-12-07';

		$data['Daily'] = array($rrule, $xref, $expect, 'should be 3 days from initial');

		// Monthly
		$rrule = 'RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=20200901T000000;WKST=MO;';
		$xref = new stdclass;
		$xref->dates = '2012-12-04';
		$xref->enddates = '2012-12-04';
		$xref->times = '14:00';
		$xref->endtimes = '16:00';
		$xref->registrationend = null;
		$xref->count = 12;

		$expect = clone $xref;
		$expect->dates = '2013-01-04';
		$expect->enddates = '2013-01-04';

		$data['Monthly 1'] = array($rrule, $xref, $expect, 'should be one month from initial');

		// Monthly 2
		$expect2 = clone $expect;
		$expect2->dates = '2013-02-04';
		$expect2->enddates = '2013-02-04';

		$data['Monthly 2'] = array($rrule, $expect, $expect2, 'should be one month from initial');

		// Weekly
		$rrule = 'RRULE:FREQ=WEEKLY;INTERVAL=1;COUNT=999;WKST=MO;';
		$xref_3 = new stdclass;
		$xref_3->dates = '2012-10-14';
		$xref_3->enddates = '2012-10-14';
		$xref_3->times = '14:00';
		$xref_3->endtimes = '16:00';
		$xref_3->registrationend = null;
		$xref_3->count = 3;

		$expect_3 = clone $xref_3;
		$expect_3->dates = '2012-10-21';
		$expect_3->enddates = '2012-10-21';

		$data['weekly'] = array($rrule, $xref_3, $expect_3, 'should be one week from initial');

		return $data;
	}

	/**
	 * test get next occurence name function
	 *
	 * @param   string  $rrule        rule
	 * @param   object  $xref_data    session data
	 * @param   object  $expect_next  expected result to compare with
	 * @param   string  $message      error message
	 *
	 * @return void
	 *
	 * @dataProvider getTestGetNextData
	 */
	public function testGetNext($rrule, $xref_data, $expect_next, $message)
	{
		$params = new JRegistry;
		$params->set('week_start', 'MO');

		$recurrenceHelper = new RedeventRecurrenceHelper;
		$rule = $recurrenceHelper->getRule($rrule);
		$nextHelper = new RedeventRecurrenceNext($rule, array('params' => $params));
		$next = $nextHelper->getNext($xref_data);

		if ($expect_next === false)
		{
			$this->assertFalse($next);
		}
		else
		{
			$this->assertEquals(
				$next->dates,
				$expect_next->dates,
				$message
			);

			$this->assertEquals($next->count, $xref_data->count + 1);
		}
	}
}
