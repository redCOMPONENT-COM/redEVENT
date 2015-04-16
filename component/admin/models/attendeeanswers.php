<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Attendee answers Model
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedEventModelAttendeeanswers extends RModel
{
	/**
	 * submitter id
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$sid = JFactory::getApplication()->input->getInt('submitter_id',  0);
		$this->setId($sid);
	}

	/**
	 * Method to set the identifier
	 *
	 * @param   int  $id  event identifier
	 *
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get data
	 *
	 * @return RdfAnswers
	 */
	public function getData()
	{
		$redform = new RdfCore;

		return $redform->getSidAnswers($this->id);
	}
}
