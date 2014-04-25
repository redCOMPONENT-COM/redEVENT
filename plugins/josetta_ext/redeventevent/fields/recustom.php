<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
/**
 * @version     $Id: josetta.php 315 2012-02-21 12:31:10Z josetta2 $
 * @package     Josetta
 * @copyright   Diffubox (c) 2012
 * @copyright   weeblr, llc (c) 2012
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
*/
class JFormFieldRECustom extends JFormField {


	public $type = 'recustom';

	protected $customfield = null;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'element':
				return $this->$name;
				break;
		}

		$value = parent::__get( $name);
		return $value;
	}

	public function setup(&$element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		return true;
	}

	protected function getInput()
	{
		$field = $this->getCustomField();

		$attr = array('onchange' => "Josetta.itemChanged(this);");
		return $field->render($attr);
	}

	protected function getCustomField()
	{
		if (!$this->customfield)
		{
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$customid = substr($this->element['name'], 6);

			$query->select('f.*');
			$query->from('#__redevent_fields AS f');
			$query->where('f.id = ' . $customid);
			$query->order('f.ordering');

			$db->setQuery($query);
			$field = $db->loadObject();

			$customfield = RedeventFactoryCustomfield::getField($field->type);
			$customfield->bind($field);
			$customfield->value = $this->value;
			$customfield->fieldname = $this->name;
			$customfield->fieldid   = $this->id;

			$this->customfield = $customfield;
		}

		return $this->customfield;
	}
}
