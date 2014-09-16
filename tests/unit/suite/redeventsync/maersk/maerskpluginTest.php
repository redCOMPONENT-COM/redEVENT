<?php
/**
 * maersk sync plugin test
 *
 * @package    Redeventsync.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

/**
 * Test class for maersk sync plugin.
 *
 * @package  Redeventsync.UnitTest
 */
class maerskpluginTest extends JoomlaTestCase
{
	protected $plugin;

	/**
	 * Setup tests
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->plugin = $this->getPlugin();
	}

	/**
	 * test load plugin
	 *
	 * @return void
	 */
	public function testLoadplugin()
	{
		$this->assertInstanceOf('JPlugin', $this->plugin);
	}

	/**
	 * Test unsupported schema
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function testUnsupportedSchema()
	{
		try
		{
			$this->plugin->onHandle('maersk', '<RERERE></RERERE>');
		}
		catch (Exception $e)
		{
			if (!strstr($e->getMessage(), 'Parsing error: Unsupported schema'))
			{
				throw new Exception('bad exception:' . $e->getMessage());
			}
		}
	}

	/**
	 * Content provider
	 *
	 * @return array
	 */
	public function getTestSupportedInputSchemaData()
	{
		return array(
			array('<AttendeesRQ />'),
			array('<AttendeesRS />'),
			array('<CustomersCRMRQ />'),
			array('<CustomersRQ />'),
			array('<CustomersRS />'),
			array('<GetSessionAttendeesRS />'),
			array('<GetSessionsRS />'),
			array('<SessionsRQ />'),
			array('<SessionsRS />'),
		);
	}

	/**
	 * Test supported schema. Just test the top element
	 *
	 * @param   string  $schema  xml data
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @dataProvider getTestSupportedInputSchemaData
	 */
	public function testSupportedInputSchema($schema)
	{
		try
		{
			$this->plugin->onHandle('maersk', $schema);
		}
		catch (Exception $e)
		{
			if (!strstr($e->getMessage(), 'Parsing error: Invalid xml data'))
			{
				throw new Exception('bad exception:' . $e->getMessage());
			}
		}
	}

	/**
	 * Content provider
	 *
	 * @return array
	 */
	public function getTestNotXmlDataData()
	{
		return array(
			array(null),
			array(''),
			array('adasdasd'),
			array(23)
		);
	}

	/**
	 * Test unsupported data
	 *
	 * @param   mixed  $data  data
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @dataProvider getTestNotXmlDataData
	 */
	public function testNotXmlData($data)
	{
		try
		{
			$this->plugin->onHandle('maersk', $data);
		}
		catch (Exception $e)
		{
			if (!strstr($e->getMessage(), 'Parsing error') && !strstr($e->getMessage(), 'DOMDocument::loadXML()'))
			{
				throw new Exception('bad exception:' . $e->getMessage());
			}

			return;
		}

		$this->fail('An expected exception has not been raised.');
	}

	public function testAttendeesRSMissingResponse()
	{
		try
		{
			$this->plugin->onHandle('maersk', '<AttendeesRS xmlns="http://www.redcomponent.com/redevent"><AttendeeRS><TransactionId>998</TransactionId></AttendeeRS></AttendeesRS>');
		}
		catch (Exception $e)
		{
			if (!strstr($e->getMessage(), 'Parsing error: Invalid xml data'))
			{
				throw new Exception('bad exception:' . $e->getMessage());
			}
		}

	}

	/**
	 * Return plugin
	 *
	 * @param   array  $params  parameters
	 *
	 * @return plgRedeventsyncclientMaersk
	 */
	private function getPlugin($params = null)
	{
		jimport('joomla.event.dispatcher');
		$dispatcher = JDispatcher::getInstance();

		require_once JPATH_SITE . '/plugins/redeventsyncclient/maersk/maersk.php';
		$plugin = new plgRedeventsyncclientMaersk($dispatcher, $params);

		require_once 'stubs/helpermessagelog.php';
		$logger = new ResyncHelperMessagelogStub;
		$plugin->setDbLogger($logger);

		$client = $this->getMock('RedeventsyncClientMaersk', array('send'), array('http://mock.redweb.dk'));
		$plugin->setClient($client);

		return $plugin;
	}
}
