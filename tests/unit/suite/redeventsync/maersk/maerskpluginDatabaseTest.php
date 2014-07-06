<?php
/**
 * maersk sync plugin test
 *
 * @package    Redeventsync.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

require_once 'stubs/redmemberlib.php';

/**
 * Test class for maersk sync plugin.
 *
 * @package  Redeventsync.UnitTest
 */
class maerskpluginDatabaseTest extends JoomlaDatabaseTestCase
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

	public function testCustomersCRMRQCreate()
	{
		$xml = file_get_contents(__DIR__ . '/xml/CustomersCRMRQ_1.xml');
		$this->plugin->onHandle('maersk', $xml);
	}

	public function testCustomersCRMRQModify()
	{
		$xml = file_get_contents(__DIR__ . '/xml/CustomersCRMRQ_2.xml');
		$this->plugin->onHandle('maersk', $xml);
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
		require_once JPATH_SITE . '/plugins/redeventsyncclient/maersk/maersk.php';

		$dispatcher = JDispatcher::getInstance();
		$plugin = new plgRedeventsyncclientMaersk($dispatcher, $params);

		require_once 'stubs/helpermessagelog.php';
		$logger = new ResyncHelperMessagelogStub;

		$plugin->setDbLogger($logger);

		$client = $this->getMock('RedeventsyncClientMaersk', array('send'), array('http://mock.redweb.dk'));
		$plugin->setClient($client);

		return $plugin;
	}
}
