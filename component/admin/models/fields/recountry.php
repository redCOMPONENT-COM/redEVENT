<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2014 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * country field
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class JFormFieldRecountry extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'recountry';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array_merge(parent::getOptions(), RedeventHelperCountries::getCountryOptions());

		return $options;
	}
}
