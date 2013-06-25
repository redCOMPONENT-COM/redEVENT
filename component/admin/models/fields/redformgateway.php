<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * country field
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class JFormFieldRedformgateway extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'redformgateway';

	/**
	 * (non-PHPdoc)
	 * @see JFormFieldList::getOptions()
	 */
	protected function getOptions()
	{
		JPluginHelper::importPlugin( 'redform_payment' );
		$dispatcher = JDispatcher::getInstance();
		$gateways = array();
		$results = $dispatcher->trigger('onGetGateway', array(&$gateways));

		$options = array();
		if (count($gateways))
		{
			foreach ($gateways as $g)
			{
				$options[] = JHtml::_('select.option', isset($g['label']) ? $g['label'] : $g['name'], $g['name']);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	protected function getInput()
	{
		$text = parent::getInput();

		// We add an hidden field just in case nothing is selected, as in this case the field is not posted !
		$sav = $this->multiple;
		$this->multiple = false;
		$text .= '<input type="hidden" name="' . $this->getName($this->fieldname . '_present') . '" value="1" />';
		$this->multiple = $sav;

		return $text;
	}
}
