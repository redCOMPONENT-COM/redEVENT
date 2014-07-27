<?php
/**
 * cli dequeue message class test
 *
 * @package    Redeventsync.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

require_once 'PHPUnit/Autoload.php';
require_once JPATH_SITE . '/cli/redeventsyncdequeue/message.php';

/**
 * cli dequeue message class test
 *
 * @package  Redeventsync.UnitTest
 */
class messageTest extends JoomlaTestCase
{
	/**
	 * test load plugin
	 *
	 * @return void
	 */
	public function testGetType()
	{
		$string = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<atype>
 <b>
  <c>text</c>
  <c>stuff</c>
 </b>
 <d>
  <c>code</c>
 </d>
</atype>
XML;

		$message = new RedeventsyncdequeueMessage($string);
		$type = $message->getType();

		$this->assertEquals('atype', $type);
	}

	/**
	 * test load plugin
	 *
	 * @return void
	 */
	public function testGetTransactionId()
	{
		$string = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<atype xmlns="http://www.redcomponent.com/redevent">
 <b>
  <c>text</c>
  <c>stuff</c>
  <dd>
  	<TransactionId>123456</TransactionId>
  </dd>
 </b>
 <d>
  <c>code</c>
 </d>
</atype>
XML;

		$message = new RedeventsyncdequeueMessage($string);
		$tid = $message->getTransactionId();

		$this->assertEquals(123456, $tid);
	}

	/**
	 * test load plugin
	 *
	 * @return void
	 */
	public function testGetNoTransactionId()
	{
		$string = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<atype xmlns="http://www.redcomponent.com/redevent">
 <b>
  <c>text</c>
  <c>stuff</c>
 </b>
 <d>
  <c>code</c>
 </d>
</atype>
XML;

		$message = new RedeventsyncdequeueMessage($string);
		$tid = $message->getTransactionId();

		$this->assertEquals(0, $tid);
	}
}
