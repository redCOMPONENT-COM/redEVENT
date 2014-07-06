<?php
/**
 * Redevent lib helper test
 *
 * @package    Redevent.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

// Register library prefix
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

/**
 * Test class for Redevent lib helper class
 *
 * @package  Redevent.UnitTest
 */
class helperTest extends JoomlaTestCase
{
	public function getTestGetRegistrationUniqueIdExceptionData()
	{
		$data = array(
			array(null),
			array('test'),
			array(12),
			array(array('1')),
			array((object) array('attendee_id' => 12, 'course_code' => '')),
			array((object) array('attendee_id' => 12, 'xref' => 5)),
			array((object) array('course_code' => 12, 'xref' => 5)),
		);


		return $data;
	}

	/**
	 * test getRegistrationUniqueId Exceptions
	 *
	 * @param   object  $data  data
	 *
	 * @expectedException InvalidArgumentException
	 *
	 * @dataProvider getTestGetRegistrationUniqueIdExceptionData
	 *
	 * @return void
	 */
	public function testGetRegistrationUniqueIdException($data)
	{
		RedeventHelper::getRegistrationUniqueId($data);
	}

	public function getTestGetRegistrationUniqueIdData()
	{
		$data = array(
			array((object) array('course_code' => 'TEST1', 'xref' => 3, 'attendee_id' => 12), 'TEST1-3-12'),
			array((object) array('course_code' => '', 'xref' => 3, 'attendee_id' => 12), '-3-12'),
		);


		return $data;
	}

	/**
	 * test getRegistrationUniqueId Exceptions
	 *
	 * @param   object  $data  data
	 *
	 * @dataProvider getTestGetRegistrationUniqueIdData
	 *
	 * @return void
	 */
	public function testGetRegistrationUniqueId($data, $expect)
	{
		$res = RedeventHelper::getRegistrationUniqueId($data);

		$this->assertEquals($res, $expect);
	}
}
