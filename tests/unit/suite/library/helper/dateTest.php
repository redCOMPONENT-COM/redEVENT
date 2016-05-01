<?php
/**
 * recurrenceTest
 *
 * @package    Redevent.UnitTest
 * @copyright  Copyright (C) 2014 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Test class for date helper.
 *
 * @package  Redevent.UnitTest
 */
class dateTest extends TestCase
{
	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function setUpBeforeClass()
	{
		// Register library prefix
		JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

		parent::setUpBeforeClass();
	}

	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestIsValidDateData()
	{
		$data = array(
			array('not a date', false),
			array('2015-02-20', true),
			array('2015-20-10', false),
			array('0000-00-00', false),
			array('0000-00-00 00:00', false),
			array('1987-02-19 10:00', false),
			array('1987-02-19 10:00:00', true),
			array("", false),
			array(null, false),
			array(false, false),
			array(0, false),
			array("201-1-26", false),
		);

		return $data;
	}

	/**
	 * test IsValidDate
	 *
	 * @param   string   $date          date string
	 * @param   boolean  $expect_valid  should it be valid
	 *
	 * @return void
	 *
	 * @dataProvider getTestIsValidDateData
	 */
	public function testIsValidDate($date, $expect_valid)
	{
		$this->assertEquals(
			RedeventHelperDate::isValidDate($date),
			$expect_valid
		);
	}

	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestIsValidTimeData()
	{
		$data = array(
			array('not a time', false),
			array('19:20:30', true),
			array('05:20:30', true),
			array('05:20', true),
			array('25:20', false),
			array('12:62', false),
			array('12:20:62', false),
		);

		return $data;
	}

	/**
	 * test IsValidDate
	 *
	 * @param   string   $time          time string
	 * @param   boolean  $expect_valid  should it be valid
	 *
	 * @return void
	 *
	 * @dataProvider getTestIsValidTimeData
	 */
	public function testIsValidTime($time, $expect_valid)
	{
		$this->assertEquals(
			RedeventHelperDate::isValidTime($time),
			$expect_valid
		);
	}
}
