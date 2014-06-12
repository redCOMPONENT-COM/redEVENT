<?php
/**
 * maersk sync plugin test
 *
 * @package    Redeventsync.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

require_once 'PHPUnit/Autoload.php';

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

		try
		{
			$this->plugin->onHandle('maersk', $xml);
		}
		catch(JDatabaseException $e)
		{
			// We should implement a database stub...
		}
	}

	public function testCustomersCRMRQModify()
	{
		$xml = file_get_contents(__DIR__ . '/xml/CustomersCRMRQ_2.xml');

		try
		{
			$this->plugin->onHandle('maersk', $xml);
		}
		catch(JDatabaseException $e)
		{
			// We should implement a database stub...
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
		require_once JPATH_SITE . '/plugins/redeventsyncclient/maersk/maersk.php';
		jimport('joomla.event.dispatcher');

		$dispatcher = JDispatcher::getInstance();
		$plugin = new plgRedeventsyncclientMaersk($dispatcher, $params);

		$logger = $this->getMock('ResyncHelperMessagelog', array('log'));
		$plugin->setDbLogger($logger);

		$client = $this->getMock('RedeventsyncClientMaersk', array('send'), array('http://mock.redweb.dk'));
		$plugin->setClient($client);

		return $plugin;
	}
}
