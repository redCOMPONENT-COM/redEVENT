<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent eventtemplates csv import Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelEventtemplatescsvimport extends RModel
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
	 * @var array
	 */
	private $forms;

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
			$this->storeEventtemplate($r);
		}

		$count = array('added' => $this->added, 'updated' => $this->updated, 'ignored' => $this->ignored);

		return $count;
	}

	/**
	 * Store eventtemplate
	 *
	 * @param   array  $data  eventtemplate data from import
	 *
	 * @return integer|bool
	 */
	private function storeEventtemplate($data)
	{
		$app = JFactory::getApplication();

		if (!empty($data['name']))
		{
			$v = RTable::getAdminInstance('Eventtemplate');

			if ($this->duplicateMethod !== 'create_new' && !empty($data['id']))
			{
				// Load existing data
				$found = $v->load($data['id']);

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

			$data['redform_id'] = $this->getFormId($data['formname']);

			// Bind submitted data
			$v->bind($data);

			if ($this->duplicateMethod == 'update' && $found)
			{
				$updating = 1;
			}
			else
			{
				// Create new
				$v->id = null;
				$updating = 0;
			}

			// Store !
			if (!$v->check())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError(), 'error');

				return false;
			}

			if (!$v->store())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError(), 'error');

				return false;
			}

			($updating ? $this->updated++ : $this->added++);

			return $v->id;
		}

		return false;
	}

	/**
	 * Return form id matching name, creating if needed
	 *
	 * @param   string  $name  form name
	 *
	 * @return integer id
	 */
	private function getFormId($name)
	{
		if (empty($name))
		{
			throw new InvalidArgumentException('formname is required');
		}

		if ($id = array_search($name, $this->getForms()))
		{
			return $id;
		}

		if (!$id)
		{
			// Doesn't exist, create it
			RModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redform/models');
			RTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redform/tables');

			$formModel = RModelAdmin::getInstance('Form', 'RedformModel');
			$data = ['formname' => $name];

			if (!$formModel->save($data))
			{
				throw new RuntimeException($formModel->getError());
			}

			$id = $formModel->getState('form.id');
			$this->forms[$id] = $name;
		}

		return $id;
	}

	/**
	 * returns array of current forms indexed by ids
	 *
	 * @return array
	 */
	private function getForms()
	{
		if (is_null($this->forms))
		{
			$this->forms = array();

			$query = $this->_db->getQuery(true);

			$query->select('id, formname')
				->from('#__rwf_forms');

			$this->_db->setQuery($query);

			$res = array();

			if (!$rows = $this->_db->loadObjectList())
			{
				$this->forms = $res;

				return $this->forms;
			}

			foreach ($rows as $row)
			{
				$res[$row->id] = $row->formname;
			}

			$this->forms = $res;
		}

		return $this->forms;
	}
}
