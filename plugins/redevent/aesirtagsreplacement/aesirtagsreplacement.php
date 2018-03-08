<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  aesirtagsreplacement
 *
 * @copyright   Copyright (C) 2008-2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Specific parameters for redEVENT.
 *
 * @since  3.2.4
 */
class PlgRedeventAesirtagsreplacement extends JPlugin implements \Redevent\Plugin\TagReplace
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.2.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Callback to add supported tags
	 *
	 * @param   array  $tags  supported tags
	 *
	 * @return mixed
	 *
	 * @since  3.2.4
	 */
	public function onRedeventGetAvailableTags(&$tags)
	{
		$tags[] = new RedeventTagsTag(
			'aesir_session_item_link', JText::_('PLG_REDEVENT_AESIRTAGSREPLACEMENT_TAG_AESIR_SESSION_ITEM_LINK'), 'aesir'
		);
		$tags[] = new RedeventTagsTag(
			'aesir_session_item_field', JText::_('PLG_REDEVENT_AESIRTAGSREPLACEMENT_TAG_AESIR_SESSION_ITEM_FIELD'), 'aesir'
		);
	}

	/**
	 * Callback for tags replacement
	 *
	 * @param   RedeventTags  $replacer  replacer
	 * @param   string        $text      text to replace
	 * @param   boolean       $recurse   set to true if replacements should be run again (e.g some tags were found and expanded)
	 *
	 * @return mixed
	 *
	 * @since  3.2.4
	 */
	public function onRedeventTagsReplace(RedeventTags $replacer, &$text, &$recurse)
	{
		if (!$session = $replacer->getSession())
		{
			return true;
		}

		$tags = $replacer->extractTags($text);

		$search = array();
		$replace = array();

		foreach ($tags as $tag)
		{
			if ($tag->getName() == 'aesir_session_item_link')
			{
				$item = $this->getAesirSessionItem($session->id);

				$search[] = $tag->getFullMatch();
				$replace[] = JRoute::_($item->getLink());
			}
			elseif (strstr($tag->getName(), 'aesir_session_item_'))
			{
				$item = $this->getAesirSessionItem($session->id);
				$field = substr($tag->getName(), strlen(aesir_session_item_));

				if (isset($item->{$field}))
				{
					$search[] = $tag->getFullMatch();
					$replace[] = $item->{$field};
				}
			}
		}

		$text = str_replace($search, $replace, $text);

		return true;
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $sessionId  redEVENT session id
	 *
	 * @return ReditemEntityItem
	 */
	private function getAesirSessionItem($sessionId)
	{
		if (!$this->sessionItem)
		{
			$db = JFactory::getDbo();

			$sessionTableName   = $db->qn('#__reditem_types_' . $this->getSessionType()->table_name, 's');
			$sessionSelectField = $db->qn('s.' . $this->getSessionSelectField()->fieldcode);

			$query = $db->getQuery(true)
				->select('s.*')
				->from($sessionTableName)
				->join('INNER', '#__reditem_items AS i ON i.id = s.id')
				->where($sessionSelectField . ' = ' . $sessionId);

			$db->setQuery($query);

			if ($res = $db->loadObject())
			{
				$this->sessionItem = ReditemEntityItem::getInstance($res->id)->bind($res);
			}
			else
			{
				$this->sessionItem = ReditemEntityItem::getInstance();
			}
		}

		return $this->sessionItem;
	}

	/**
	 * Get session type entity
	 *
	 * @return ReditemEntityType
	 *
	 * @since 3.2.3
	 */
	private function getSessionType()
	{
		if (is_null($this->sessionType))
		{
			$typeId = RedeventHelperConfig::get('aesir_session_type_id');
			$type = ReditemEntityType::load($typeId);

			if (!$type->isValid())
			{
				throw new LogicException('Session type is not selected');
			}

			$this->sessionType = $type;
		}

		return $this->sessionType;
	}

	/**
	 * Get session type entity
	 *
	 * @return ReditemEntityField
	 *
	 * @since 3.2.3
	 */
	private function getSessionSelectField()
	{
		if (is_null($this->sessionSelectField))
		{
			$id = RedeventHelperConfig::get('aesir_session_select_field');
			$field = ReditemEntityField::load($id);

			if (!$field->isValid())
			{
				throw new LogicException('Session select field is not selected');
			}

			$this->sessionSelectField = $field;
		}

		return $this->sessionSelectField;
	}
}
