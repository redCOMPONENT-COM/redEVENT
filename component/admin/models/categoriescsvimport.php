<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent categories csv import Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelCategoriescsvimport extends RModel
{
	/**
	 * @var string
	 */
	private $duplicateMethod;

	/**
	 * @var int
	 */
	private $updated = 0;

	/**
	 * @var int
	 */
	private $ignored = 0;

	/**
	 * @var int
	 */
	private $added = 0;

	/**
	 * insert events/sessions in database
	 *
	 * @param   array   $records           array of records to import
	 * @param   string  $duplicate_method  method for handling duplicate record (ignore, create_new, update)
	 *
	 * @return boolean true on success
	 */
	public function import($records, $duplicate_method = 'ignore')
	{
		$this->duplicateMethod = $duplicate_method;

		foreach ($records as $r)
		{
			$this->storeCategory($r);
		}

		$count = array('added' => $this->added, 'updated' => $this->updated, 'ignored' => $this->ignored);

		return $count;
	}

	/**
	 * Store category
	 *
	 * @param   array  $data  category data from import
	 *
	 * @return integer|bool
	 */
	private function storeCategory($data)
	{
		$app = JFactory::getApplication();

		if (!empty($data['name']))
		{
			$new = RTable::getAdminInstance('Category');

			if ($this->duplicateMethod !== 'create_new' && isset($data['id']) && $data['id'])
			{
				// Load existing data
				$found = $new->load($data['id']);

				// Discard if set to ignore duplicate
				if ($found && $this->duplicateMethod == 'ignore')
				{
					$this->ignored++;

					return true;
				}
			}
			else
			{
				$found = false;
			}

			// Bind submitted data
			$new->bind($data);

			if ($this->duplicateMethod == 'update' && $found)
			{
				$updating = 1;
			}
			else
			{
				// Create new
				$new->id = null;
				$updating = 0;
			}

			// Store !
			if (!$new->check())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $new->getError(), 'error');

				return false;
			}

			if (!$new->store())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $new->getError(), 'error');

				return false;
			}

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterCategorySaved', array($new->id));

			($updating ? $this->updated++ : $this->added++);

			return $new->id;
		}

		return false;
	}
}
