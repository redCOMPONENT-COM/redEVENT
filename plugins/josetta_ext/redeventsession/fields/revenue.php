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

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
*/
class JFormFieldREVenue extends JFormField {


	public $type = 'revenue';

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

	public function setValue($value)
	{
		$this->value = $value;
	}

	protected function getInput()
	{
		$ref_id = $this->form->getField('ref_id')->value;
		$lang   = $this->form->getValue('language');

		// find associated event
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('ja.id, v.venue');
		$query->from('#__josetta_associations AS ja');
		$query->join('INNER', '#__josetta_associations AS jorg ON jorg.key = ja.key');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON jorg.id = x.venueid');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = ja.id');
		$query->where('x.id = ' . $ref_id);
		$query->where('jorg.context = ' . $db->Quote('com_redevent_venue'));
		$query->where('ja.language = ' . $db->Quote($lang));

		$db->setQuery($query);
		$resu = $db->loadObject();

		$html = array();

		if (!$resu)
		{
			$html[] = '<div class="error">' . Jtext::_('COM_REDEVENT_JOSETTA_TRANSLATE_VENUE_FIRST') . '</div>';
		}
		else
		{
			$html[] = $resu->venue;
		}

		$html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . ($resu ? $resu->id : 0) . '"/>';


		return implode("\n", $html);
	}
}
