<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

JFormHelper::loadFieldClass('list');

/**
 * custom types field
 *
 * @package  Redevent.library
 * @since    2.5
 */
class JFormFieldRELanguages extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'relanguages';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$config = RedeventHelper::config();

		if ($allowed = $config->get('allowed_session_languages'))
		{
			$allowed = explode(',', $allowed);
			$allowed = array_map(trim, $allowed);
		}
		else
		{
			$allowed = array();
		}

		return RedeventHelperLanguages::getOptions('value', 'text', false, $allowed);
	}
}
