<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_redevent/models/eventhelper.php';

/**
 * Class RedeventTags, provides tags replacement
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventTags
{
	private $xref;

	private $eventid;

	private $venueid;

	private $submitkey;

	private $published;

	protected $eventlinks = null;

	private $libraryTags = null;

	private $customfields = null;

	private $xrefcustomfields = null;

	private $answers = null;

	private $options = null;

	private $canregister = null;

	/**
	 * event model
	 * @var object
	 */
	private $event = null;

	/**
	 * instance of rfcore
	 * @var object
	 */
	private $rfcore = null;

	/**
	 * @var JDatabaseDriver
	 */
	private $db;

	/**
	 * constuctor
	 *
	 * @param   array  $options  options
	 */
	public function __construct($options = null)
	{
		$this->options = array();

		if (is_array($options))
		{
			$this->addOptions($options);
		}

		if ($this->getOption('db'))
		{
			$this->db = $this->getOption('db');
		}
		else
		{
			$this->db = JFactory::getDbo();
		}
	}

	/**
	 * Set event id
	 *
	 * @param   int  $id  event id
	 *
	 * @return void
	 */
	public function setEventId($id)
	{
		$this->eventid = intval($id);
	}

	/**
	 * Set event object
	 *
	 * @param   object  $object  event
	 *
	 * @return void
	 */
	public function setEventObject($object)
	{
		$this->event = $object;
	}

	/**
	 * Set session id
	 *
	 * @param   int  $xref  session id
	 *
	 * @return void
	 */
	public function setXref($xref)
	{
		if (($this->xref !== $xref) && intval($xref))
		{
			$this->xref = intval($xref);
			$this->customfields = null;
			$this->xrefcustomfields = null;
		}
	}

	/**
	 * Get session id
	 *
	 * @return int
	 */
	public function getXref()
	{
		if (!$this->xref)
		{
			$this->initXref();
		}

		return $this->xref;
	}

	/**
	 * set submit key
	 *
	 * @param   string  $string  submit key
	 *
	 * @return void
	 */
	public function setSubmitkey($string)
	{
		$this->submitkey = $string;
	}

	/**
	 * add options (key, value) to object
	 *
	 * @param   array  $options  options
	 *
	 * @return void
	 */
	public function addOptions($options)
	{
		if (is_array($options))
		{
			if (!empty($this->options))
			{
				$this->options = array_merge($this->options, $options);
			}
			else
			{
				$this->options = $options;
			}
		}
	}

	/**
	 * Get an option value
	 *
	 * @param   string  $name     option name
	 * @param   mixed   $default  default value
	 *
	 * @return mixed
	 */
	public function getOption($name, $default = null)
	{
		return JArrayHelper::getValue($this->options, $name, $default);
	}

	/**
	 * Set an option value
	 *
	 * @param   string  $name   option name
	 * @param   mixed   $value  value
	 *
	 * @return RedeventTags
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * Substitute tags with the correct info
	 *
	 * @param   string  $text     text to replace
	 * @param   array   $options  options
	 *
	 * @return string
	 */
	public function replaceTags($text, $options = null)
	{
		if ($options)
		{
			$this->addOptions($options);
		}

		$this->submitkey = $this->submitkey ? $this->submitkey : JFactory::getApplication()->input->get('submit_key');

		$text = $this->replace($text);

		/* Include redFORM */
		if (strstr($text, '[redform]') && $this->getEvent()->getData()->redform_id > 0)
		{
			$status = RedeventHelper::canRegister($this->xref);

			if ($status->canregister)
			{
				$redform = $this->getForm($this->getEvent()->getData()->redform_id);
			}
			else
			{
				$redform = '<span class="registration_error">' . $status->status . '</span>';
			}

			/* second replacement, add the form */
			/* if done in first one, username in the form javascript is replaced too... */
			$text = str_replace('[redform]', $redform, $text);
		}

		return $text;
	}

	/**
	 * tries to pull a xref from the eventid
	 *
	 * @return object
	 */
	private function initXref()
	{
		$eventid = $this->eventid;

		if ($eventid)
		{
			$query = $this->db->getQuery(true);

			$query->select('x.id')
				->from($this->db->qn('#__redevent_event_venue_xref', 'x'))
				->join('INNER', '#__redevent_events AS e ON e.id = x.eventid')
				->where('x.published = 1')
				->where('x.eventid = ' . $this->db->Quote($eventid))
				->order('x.dates ASC');

			$this->db->setQuery($query);
			$res = $this->db->loadResult();

			if ($res)
			{
				$this->setXref($res);
			}
		}

		return $this;
	}

	/**
	 * recursively replace tags
	 *
	 * @param   string  $text  text to handle
	 *
	 * @return string
	 */
	protected function replace($text)
	{
		$recurse = false;

		// First, let's do the library tags replacement
		$text = $this->replaceLibraryTags($text);

		// Then extra
		$recurse |= $this->replaceExtra($text);

		// Check for plugins
		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedeventTagsReplace', array($this, &$text, &$recurse));

		// Now get the list of all remaining tags
		if (preg_match_all('/\[([^\]\s]+)(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{
			$search = array();
			$replace = array();

			foreach ($alltags as $tag)
			{
				$tag_obj = new RedeventTagsParsed($tag[0]);

				// Check for conditions tags
				if ($tag_obj->getParam('condition_hasplacesleft') == "0"
					&& $this->getEvent()->getPlacesLeft())
				{
					$search[] = $tag_obj->getFullMatch();
					$replace[] = '';
					continue;
				}

				if ($tag_obj->getParam('condition_hasplacesleft') == "1"
					&& $this->getEvent()->getData()->maxattendees > 0
					&& !$this->getEvent()->getPlacesLeft())
				{
					$search[] = $tag_obj->getFullMatch();
					$replace[] = '';
					continue;
				}

				// Attending or waiting list prefix
				if ($this->submitkey && strpos($tag_obj->getName(), 'attending_') === 0)
				{
					// Replace with rest of tag if attending
					$search[] = $tag_obj->getFullMatch();

					if ($this->hasAttending())
					{
						$replace[] = '[' . substr($tag_obj->getName(), 10) . ']';
					}
					else
					{
						$replace[] = '';
					}

					continue;
				}
				elseif ($this->submitkey && strpos($tag_obj->getName(), 'waiting_') === 0)
				{
					// Replace with rest of tag if not attending
					$search[] = $tag_obj->getFullMatch();

					if ($this->hasAttending())
					{
						$replace[] = '';
					}
					else
					{
						$replace[] = '[' . substr($tag_obj->getName(), 8) . ']';
					}

					continue;
				}

				if ($this->replaceLibraryTag($tag_obj->getName()) !== false)
				{
					$search[] = $tag_obj->getFullMatch();
					$replace[] = $this->replaceLibraryTag($tag_obj->getName());

					continue;
				}

				$func = 'getTag_' . strtolower($tag_obj->getName());

				if (method_exists($this, $func))
				{
					$search[] = $tag_obj->getFullMatch();
					$replace[] = $this->$func($tag_obj);
				}
			}

			// Do the replace
			$text = str_replace($search, $replace, $text, $count);

			// Check if tags where replaced, in which case we should run it again
			$recurse |= $count > 0;
		}

		// Then the custom fields tags
		$search = array();
		$replace = array();

		/* Load custom fields */
		$customfields = $this->getCustomFields();

		foreach ($customfields as $tag => $data)
		{
			$search[] = '[' . $data->text_name . ']';
			$replace[] = $data->text_field;
		}

		/* redFORM fields values replacements */
		if ($alltags)
		{
			$redformfields = $this->getFieldsTags();

			if ($redformfields && count($redformfields))
			{
				foreach ($alltags as $tag)
				{
					if (stripos($tag[1], 'answer_') === 0)
					{
						$search[] = '[' . $tag[1] . ']';
						$replace[] = $this->getFormFieldAnswer(substr($tag[1], 7));
					}
					elseif (stripos($tag[1], 'field_') === 0)
					{
						$search[] = '[' . $tag[1] . ']';
						$replace[] = $this->getFieldAnswer(substr($tag[1], 6));
					}
				}
			}
		}

		if (count($search))
		{
			$text = str_ireplace($search, $replace, $text, $count);
			$recurse |= $count > 0;
		}

		// Recurse if we did replacement(s), possibly expanding new tags
		if ($recurse)
		{
			$text = $this->replace($text);
		}

		return $text;
	}

	/**
	 * Do extra replacements from options
	 *
	 * @param   string  &$text  haystack
	 *
	 * @return bool  true if a replacement was done
	 */
	private function replaceExtra(&$text)
	{
		if ($this->getOption('extra') && is_array($this->getOption('extra')))
		{
			$search = array_keys($this->getOption('extra'));
			$replace = $this->getOption('extra');
			$text = str_replace($search, $replace, $text, $count);

			return $count > 0;
		}

		return false;
	}

	/**
	 * return event helper model object
	 *
	 * @return object
	 */
	private function getEvent()
	{
		if (empty($this->event))
		{
			$this->event = RModel::getFrontInstance('Eventhelper', array('ignore_request' => true), 'com_redevent');
			$this->event->setId($this->eventid);
			$this->event->setXref($this->xref);
		}

		return $this->event;
	}

	/**
	 * Load the HTML table with signup links
	 *
	 * @return string
	 */
	private function SignUpLinks()
	{
		if (!$this->xref)
		{
			return false;
		}

		if (!$this->eventid)
		{
			$session = $this->getEvent()->getData();
			$this->eventid = $session->eventid;
		}

		$app = JFactory::getApplication();
		$this->getEventLinks();
		$template_path = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/com_redevent';

		$lists['order_Dir'] = JFactory::getApplication()->input->getWord('filter_order_Dir', 'ASC');
		$lists['order'] = JFactory::getApplication()->input->getCmd('filter_order', 'x.dates');
		$this->lists = $lists;

		$uri = JFactory::getURI('index.php?option=com_redevent');
		$this->action = JRoute::_(RedeventHelperRoute::getDetailsRoute($this->eventid, $this->xref));

		$this->customs = $this->getXrefCustomFields();

		ob_start();

		if (JFactory::getApplication()->input->get('format') == 'pdf')
		{
			if (file_exists($template_path . '/details/courseinfo_pdf.php'))
			{
				include $template_path . '/details/courseinfo_pdf.php';
			}
			else
			{
				include JPATH_COMPONENT . '/views/details/tmpl/courseinfo_pdf.php';
			}
		}
		else
		{
			if (file_exists($template_path . '/details/courseinfo.php'))
			{
				include $template_path . '/details/courseinfo.php';
			}
			else
			{
				include JPATH_COMPONENT . '/views/details/tmpl/courseinfo.php';
			}
		}

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Load the HTML for attachements
	 *
	 * @return string
	 */
	private function attachmentsHTML()
	{
		$app = JFactory::getApplication();
		$template_path = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/com_redevent';

		$this->row = $this->getEvent()->getData();

		ob_start();

		if (!JFactory::getApplication()->input->get('format') == 'pdf')
		{
			if (file_exists($template_path . '/details/default_attachments.php'))
			{
				include $template_path . '/details/default_attachments.php';
			}
			else
			{
				include JPATH_COMPONENT . '/views/details/tmpl/default_attachments.php';
			}
		}

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Load all venues and their signup links
	 *
	 * @return string
	 */
	private function getEventLinks()
	{
		if (empty($this->eventlinks))
		{
			$model = RModel::getFrontInstance('simplelist', array('ignore_request' => true), 'com_redevent');
			$model->setState('filter_event', $this->eventid);
			$model->setState('limit', 1000);
			$model->setState('filter_order', 'x.dates');
			$model->setState('filter_order_Dir', 'ASC');
			$this->eventlinks = $model->getData();
			$this->eventlinks = $this->getUserRegistrations($this->eventlinks);
		}

		return $this->eventlinks;
	}

	/**
	 * adds property userregistered to rows: the number of time this user is already registered for each xref
	 *
	 * @param   array  $rows  session rows
	 *
	 * @return array
	 */
	private function getUserRegistrations($rows)
	{
		$db = $this->db;
		$user = JFactory::getUser();

		foreach ($rows as $k => $r)
		{
			if ($user->get('id'))
			{
				$query = $db->getQuery(true);

				$query->select('COUNT(r.id) AS total')
					->from('#__redevent_register AS r')
					->where('r.xref = ' . $db->Quote($r->xref))
					->where('r.confirmed = 1')
					->where('r.cancelled = 0')
					->where('r.uid = ' . $db->Quote($user->get('id')));

				$db->setQuery($query);
				$rows[$k]->userregistered = $db->loadResult();
			}
			else
			{
				$rows[$k]->userregistered = 0;
			}
		}

		return $rows;
	}

	/**
	 * recursively replaces all the library tags from the text
	 *
	 * @param   string  $text  text to replace
	 *
	 * @return string
	 */
	public function replaceLibraryTags($text)
	{
		$tags = $this->getLibraryTags();

		$search = array();
		$replace = array();

		foreach ($tags as $tag => $data)
		{
			$search[] = '[' . $data->text_name . ']';
			$replace[] = $data->text_field;
		}

		// First replacement
		$text = str_ireplace($search, $replace, $text, $count);

		// Now, the problem that there could have been library tags embedded into one another, so we keep replacing if $count is > 0
		if ($count)
		{
			$text = $this->replaceLibraryTags($text);
		}

		return $text;
	}

	/**
	 * recursively replaces all the library tags from the text
	 *
	 * @param   string  $tag  tag to replace
	 *
	 * @return string
	 */
	private function replaceLibraryTag($tag)
	{
		$tags = $this->getLibraryTags();

		if (isset($tags[$tag]))
		{
			return $tags[$tag]->text_field;
		}

		return false;
	}

	/**
	 * gets list of tags belonging to the text library
	 *
	 * @return array (objects: text_name, text_field)
	 */
	private function getLibraryTags()
	{
		if (empty($this->libraryTags))
		{
			$db = $this->db;
			$query = $db->getQuery(true);

			$query->select('text_name, text_field')
				->from('#__redevent_textlibrary')
				->where('CHAR_LENGTH(text_name) > 0');

			$db->setQuery($query);
			$this->libraryTags = $db->loadObjectList('text_name');
		}

		return $this->libraryTags;
	}

	/**
	 * Returns the content of comments
	 *
	 * @param   object  $event  event
	 *
	 * @return string
	 */
	private function getComments($event)
	{
		$app = JFactory::getApplication();
		$template_path = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/com_redevent';
		$contents = '';
		$this->row = $event;
		$this->row->did = $event->id;
		$this->elsettings = RedeventHelper::config();

		if (JFactory::getApplication()->input->get('format') != 'raw')
		{
			ob_start();

			if (file_exists($template_path . '/details/default_comments.php'))
			{
				include $template_path . '/details/default_comments.php';
			}
			else
			{
				include JPATH_COMPONENT . '/views/details/tmpl/default_comments.php';
			}

			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}

	/**
	 * text for formal offer
	 *
	 * @param   object  $event  event
	 *
	 * @return string
	 */
	private function getFormalOffer($event)
	{
		ob_start();
		?>
		<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
			<?php echo $this->replaceTags($event->submission_type_formal_offer); ?>
			<input type="hidden" name="task" value="signup"/>
			<input type="hidden" name="option" value="com_redevent"/>
			<input type="hidden" name="view" value="signup"/>
			<input type="hidden" name="subtype" value="formaloffer"/>
			<input type="hidden" name="sendmail" value="1"/>
			<input type="hidden" name="xref" value="<?php echo $event->xref; ?>"/>
			<input type="hidden" name="id" value="<?php echo $event->id; ?>"/>
		</form>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * text for email submission
	 *
	 * @param   object  $event  event
	 *
	 * @return string
	 */
	private function getEmailSubmission($event)
	{
		ob_start();
		?>
		<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
			<?php echo $this->replaceTags($event->submission_type_email); ?>
			<input type="hidden" name="task" value="signup"/>
			<input type="hidden" name="option" value="com_redevent"/>
			<input type="hidden" name="view" value="signup"/>
			<input type="hidden" name="subtype" value="email"/>
			<input type="hidden" name="sendmail" value="1"/>
			<input type="hidden" name="xref" value="<?php echo $event->xref; ?>"/>
			<input type="hidden" name="id" value="<?php echo $event->id; ?>"/>
		</form>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * get custom fields and their value
	 *
	 * @return array tag => field
	 */
	private function getCustomfields()
	{
		if (empty($this->customfields))
		{
			$details = $this->getEvent()->getData();

			$db = $this->db;
			$query = $db->getQuery(true);

			$query->select('f.*')
				->from('#__redevent_fields AS f')
				->where('f.published = 1')
				->where('CHAR_LENGTH(f.tag) > 0');

			$db->setQuery($query);
			$fields = $db->loadObjectList();

			$replace = array();

			foreach ((array) $fields as $field)
			{
				$prop = 'custom' . $field->id;

				if (isset($details->$prop))
				{
					$field->value = $details->$prop;
				}
				else
				{
					$field->value = null;
				}

				$fieldInstance = RedeventFactoryCustomfield::getField($field->type);
				$fieldInstance->bind($field);

				$obj = new stdclass;
				$obj->text_name = $fieldInstance->tag;
				$obj->text_field = $fieldInstance->renderValue();
				$replace[$field->tag] = $obj;
			}

			$this->customfields = $replace;
		}

		return $this->customfields;
	}

	/**
	 * returns all custom fields for xrefs
	 *
	 * @return array
	 */
	private function getXrefCustomFields()
	{
		if (empty($this->xrefcustomfields))
		{
			$db = $this->db;
			$query = $db->getQuery(true);

			$query->select('f.id, f.name, f.in_lists, f.searchable, f.ordering')
				->from('#__redevent_fields AS f')
				->where('f.published = 1')
				->where('f.object_key = ' . $db->Quote('redevent.xref'))
				->order('f.ordering ASC');

			$db->setQuery($query);
			$this->xrefcustomfields = $db->loadObjectList();
		}

		return $this->xrefcustomfields;
	}

	/**
	 * Get submission user
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return bool|JUser
	 */
	protected function getSubmissionUser($submit_key)
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		$query->select('uid');
		$query->from('#__redevent_register');
		$query->where('submit_key = ' . $db->quote($submit_key));

		$db->setQuery($query);

		if ($id = $db->loadResult())
		{
			$user = JUser::getInstance($id);

			return $user;
		}

		return false;
	}

	/**
	 * Get unique id for attendee
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return string
	 */
	private function getAttendeeUniqueId($submit_key)
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		$query->select('e.title, e.alias, e.course_code, r.xref, r.id')
			->from('#__redevent_register AS r')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref')
			->join('INNER', '#__redevent_events AS e ON e.id = x.eventid')
			->where('r.submit_key = ' . $db->Quote($submit_key));

		$db->setQuery($query, 0, 1);
		$obj = $db->loadObject();

		if ($obj)
		{
			$code = $obj->course_code . '-' . $obj->xref . '-' . $obj->id;
		}
		else
		{
			$code = '';
		}

		return $code;
	}

	/**
	 * return answers as html text
	 *
	 * @return string html
	 */
	private function answersToHtml()
	{
		$answers = $this->getAnswers();

		if (!$answers)
		{
			return '';
		}

		$res = '';

		foreach ($answers->getSingleSubmissions() as $a)
		{
			$res .= '<table class="formanswers">';

			foreach ($a->getFields() as $field)
			{
				$res .= '<tr>' . "\n";
				$res .= '<th align="left">' . $field->field . '</th>' . "\n";
				$res .= '<td>' . str_replace('~~~', '<br/>', $field->getDatabaseValue()) . '</td>' . "\n";
				$res .= '</tr>' . "\n";
			}

			$res .= '</table>';
		}

		return $res;
	}

	/**
	 * returns answers as array of row arrays
	 *
	 * @return RdfCoreFormSubmission
	 */
	private function getAnswers()
	{
		if (!$this->getEvent()->getData())
		{
			JError::raiseWarning(0, JText::_('COM_REDEVENT_Error_missing_data'));

			return false;
		}

		if (!$sids = $this->getOption('sids'))
		{
			if (!$this->submitkey)
			{
				return false;
			}

			$db = $this->db;
			$query = $db->getQuery(true);

			$query->select('r.sid')
				->from('#__redevent_register AS r')
				->where('r.submit_key = ' . $db->quote($this->submitkey));

			$db->setQuery($query);
			$sids = $db->loadColumn();
		}

		$rfcore = $this->getRFCore();

		return $rfcore->getAnswers($sids);
	}

	/**
	 * Get submission price
	 *
	 * @return bool|mixed
	 */
	private function getSubmissionTotalPrice()
	{
		if (!$this->submitkey)
		{
			return false;
		}

		$db = $this->db;
		$query = $db->getQuery(true);

		$query->select('SUM(s.price + s.vat)')
			->from('#__rwf_submitters AS s')
			->where('s.submit_key = ' . $db->quote($this->submitkey))
			->group('s.submit_key');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * Get form fields tags
	 *
	 * @return array|bool
	 */
	private function getFieldsTags()
	{
		if (!$this->getEvent()->getData())
		{
			JError::raiseWarning(0, JText::_('COM_REDEVENT_Error_missing_data'));

			return false;
		}

		$rfcore = $this->getRFCore();

		$fields = $rfcore->getFields($this->getEvent()->getData()->redform_id);

		$tags = array();

		if ($fields && count($fields))
		{
			foreach ((array) $fields as $f)
			{
				$tags[$f->field_id] = 'field_' . $f->field_id;
			}
		}

		return $tags;
	}

	/**
	 * Get field answer
	 *
	 * @param   int  $id  redform field id (not the same as 'form field' id...)
	 *
	 * @return array|bool
	 */
	private function getFieldAnswer($id)
	{
		$submission = $this->getAnswers();

		if (!$submission)
		{
			return '';
		}

		$fields = $submission->getFirstSubmission()->getFields();

		foreach ($fields as $f)
		{
			if ($f->field_id == $id)
			{
				return $f->value;
			}
		}

		return '';
	}

	/**
	 * Get form field answer
	 *
	 * @param   int  $id  redform form field id
	 *
	 * @return array|bool
	 */
	private function getFormFieldAnswer($id)
	{
		$submission = $this->getAnswers();

		if (!$submission)
		{
			return '';
		}

		$fields = $submission->getFirstSubmission()->getFields();

		foreach ($fields as $f)
		{
			if ($f->id == $id)
			{
				return $f->value;
			}
		}

		return '';
	}

	/**
	 * Get RdfCore object
	 *
	 * @return RdfCore
	 */
	private function getRFCore()
	{
		if (empty($this->rfcore))
		{
			$this->rfcore = RdfCore::getInstance();
		}

		return $this->rfcore;
	}

	/**
	 * returns form
	 *
	 * @return string
	 */
	private function getForm()
	{
		$tag = new RedeventTagsFormForm($this->getEvent());

		return $tag->getHtml($this->getOption('hasreview'));
	}

	/**
	 * Transform relative into absolute url
	 *
	 * @param   string  $url    url
	 * @param   bool    $xhtml  escape
	 * @param   bool    $ssl    ssl prefix
	 *
	 * @return mixed|null|string
	 */
	private function absoluteUrls($url, $xhtml = true, $ssl = null)
	{
		// Get the router
		$app = JFactory::getApplication();
		$router = $app->getRouter();

		// Make sure that we have our router
		if (!$router)
		{
			return null;
		}

		if ((strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		// Build route
		$uri = $router->build($url);
		$url = $uri->toString(array('path', 'query', 'fragment'));

		// Replace spaces
		$url = preg_replace('/\s/u', '%20', $url);

		/*
		 * Get the secure/unsecure URLs.
		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		$ssl = (int) $ssl;

		if ($ssl || 1)
		{
			$uri = JURI::getInstance();

			// Get additional parts
			static $prefix;

			if (!$prefix)
			{
				$prefix = $uri->toString(array('host', 'port'));
			}

			// Determine which scheme we want
			$scheme = ($ssl === 1) ? 'https' : 'http';

			// Make sure our url path begins with a slash
			if (!preg_match('#^/#', $url))
			{
				$url = '/' . $url;
			}

			// Build the URL
			$url = $scheme . '://' . $prefix . $url;
		}

		if ($xhtml)
		{
			$url = str_replace('&', '&amp;', $url);
		}

		return $url;
	}

	/**
	 * format prices
	 *
	 * @param   array  $prices  prices
	 *
	 * @return string|void
	 */
	private function formatPrices($prices)
	{
		if (!is_array($prices))
		{
			return;
		}

		if (count($prices) == 1)
		{
			return RedeventHelperOutput::formatprice($prices[0]->price);
		}

		$res = array();

		foreach ($prices as $p)
		{
			$res[] = RedeventHelperOutput::formatprice($p->price) . ' (' . $p->name . ')';
		}

		return implode(' / ', $res);
	}

	/**
	 * returns true if at least one attendee associated to current submit_key is attending
	 *
	 * @return boolean
	 */
	private function hasAttending()
	{
		// Get how many registrations are associated to submit key, and how many on waiting list
		$db = $this->db;
		$query = $db->getQuery(true);

		$query->select('COUNT(*) as total, SUM(r.waitinglist) as waiting')
			->from('#__redevent_register AS r')
			->where('r.submit_key = ' . $db->Quote($this->submitkey))
			->group('r.submit_key');

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res || !$res->total)
		{
			// No attendee at all for submit key... no display...
			return false;
		}

		if ($res->total != $res->waiting)
		{
			// Not all registrations are on wl
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Is user allowed to register
	 *
	 * @return null|object
	 */
	private function canRegister()
	{
		if ($this->canregister === null)
		{
			$this->canregister = RedeventHelper::canRegister($this->getXref());
		}

		return $this->canregister;
	}

	/*************************************************************************
	 * tags functions
	 *
	 * name must be getTag_xxxxx_yyy
	 *
	 */

	/************ event tags **************************/

	/**
	 * Parses event_description tag
	 *
	 * @return string
	 */
	private function getTag_event_description()
	{
		/* Fix the tags of the event description */
		$findcourse = array('[venues]', '[price]', '[credits]', '[code]');
		$venues_html = $this->SignUpLinks();

		$replacecourse = array($venues_html,
			$this->formatPrices($this->getEvent()->getPrices()),
			$this->getEvent()->getData()->course_credit,
			$this->getEvent()->getData()->course_code);
		$res = str_replace($findcourse, $replacecourse, $this->getEvent()->getData()->datdescription);

		return $res;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_event_info_text()
	{
		return $this->getTag_event_description();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_event_title()
	{
		return $this->getEvent()->getData()->title;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_event_full_title()
	{
		return RedeventHelper::getSessionFullTitle($this->getEvent()->getData());
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_session_code()
	{
		return $this->getEvent()->getData()->session_code;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_price()
	{
		return $this->formatPrices($this->getEvent()->getPrices());
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_credits()
	{
		return $this->getEvent()->getData()->course_credit;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_code()
	{
		return $this->getEvent()->getData()->course_code;
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_event_created(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;

		return RedeventHelperDate::formatdatetime($this->getEvent()->getData()->event_created, $format);
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_event_modified(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;

		return RedeventHelperDate::formatdatetime($this->getEvent()->getData()->event_modified, $format);
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_session_created(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;

		return RedeventHelperDate::formatdatetime($this->getEvent()->getData()->session_created, $format);
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_session_modified(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;

		return RedeventHelperDate::formatdatetime($this->getEvent()->getData()->session_modified, $format);
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_date(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;

		return RedeventHelperDate::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times, $format);
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_enddate(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;

		return RedeventHelperDate::formatdate($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes, $format);
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_time(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;
		$tmp = "";

		if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times))
		{
			$tmp = RedeventHelperDate::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times, $format);

			if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes))
			{
				$tmp .= ' - ' . RedeventHelperDate::formattime($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes, $format);
			}
		}

		return $tmp;
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_starttime(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;
		$tmp = "";

		if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times))
		{
			$tmp = RedeventHelperDate::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times, $format);
		}

		return $tmp;
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_endtime(RedeventTagsParsed $tag)
	{
		$format = $tag->getParam('format') ?: null;
		$tmp = "";

		if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes))
		{
			$tmp = RedeventHelperDate::formattime($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes, $format);
		}

		return $tmp;
	}

	/**
	 * Parses a tag
	 *
	 * @param   RedeventTagsParsed  $tag  tag
	 *
	 * @return string
	 */
	private function getTag_startenddatetime(RedeventTagsParsed $tag)
	{
		$formatDate = $tag->getParam('formatDate') ?: null;
		$formatTime = $tag->getParam('formatTime') ?: null;
		$tmp = RedeventHelperDate::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times, $formatDate);

		if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times))
		{
			$tmp .= ' ' . RedeventHelperDate::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times, $formatTime);
		}

		if (!empty($this->getEvent()->getData()->enddates) && $this->getEvent()->getData()->enddates != $this->getEvent()->getData()->dates)
		{
			$tmp .= ' - ' . RedeventHelperDate::formatdate($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes, $formatDate);
		}

		if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes))
		{
			$tmp .= ' ' . RedeventHelperDate::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->endtimes, $formatTime);
		}

		return $tmp;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_duration()
	{
		return RedeventHelperDate::getEventDuration($this->getEvent()->getData());
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_event_image()
	{
		$eventimage = '';

		if ($this->getEvent()->getData()->datimage)
		{
			$eventimage = JHTML::image(
				JURI::root() . $this->getEvent()->getData()->datimage, $this->getEvent()->getData()->title,
				array('title' => $this->getEvent()->getData()->title)
			);
		}

		return $eventimage;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_eventimage()
	{
		return $this->getTag_event_image();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_event_thumb()
	{
		$eventimage = RedeventImage::modalimage($this->getEvent()->getData()->datimage, $this->getEvent()->getData()->title);

		return $eventimage;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_category_image()
	{
		$cats_images = array();

		foreach ($this->getEvent()->getData()->categories as $c)
		{
			$cats_images[] = RedeventImage::getCategoryImage($c, false);
		}

		$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'
			. implode('</span><span class="details-categories-image">', $cats_images) . '</span></span>';

		return $categoryimage;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_categoryimage()
	{
		return $this->getTag_category_image;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_category_thumb()
	{
		$cats_images = array();

		foreach ($this->getEvent()->getData()->categories as $c)
		{
			$cats_images[] = RedeventImage::getCategoryImage($c);
		}

		$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'
			. implode('</span><span class="details-categories-image">', $cats_images) . '</span></span>';

		return $categoryimage;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_info()
	{
		// Check that there is no loop with the tag inclusion
		if (strpos($this->getEvent()->getData()->details, '[info]') === false)
		{
			$info = $this->replaceTags($this->getEvent()->getData()->details);
		}
		else
		{
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XREF_DETAILS'));
			$info = '';
		}

		return $info;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_category()
	{
		// Categories
		$cats = array();

		foreach ($this->getEvent()->getData()->categories as $c)
		{
			$cats[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getCategoryEventsRoute($c->slug)), $c->name);
		}

		return '<span class="details-categories">' . implode(', ', $cats) . '</span>';
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_eventcomments()
	{
		return $this->getComments($this->getEvent()->getData());
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_permanentlink()
	{
		$link = JHTML::link(
			$this->absoluteUrls(
				RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug),
				false
			),
			JText::_('COM_REDEVENT_Permanent_link'), 'class="permalink"'
		);

		return $link;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_datelink()
	{
		$link = JHTML::link(
			$this->absoluteUrls(
				RedeventHelperRoute::getDetailsRoute(
					$this->getEvent()->getData()->slug,
					$this->xref
				), false
			),
			JText::_('COM_REDEVENT_Event_details'), 'class="datelink"'
		);

		return $link;
	}

	/**
	 * Parses tag ical_url
	 * returns link to session ical export
	 *
	 * @return string
	 */
	private function getTag_ical()
	{
		$ttext = JText::_('COM_REDEVENT_EXPORT_ICS');
		$res = JHTML::link(
			$this->getTag_ical_url(),
			$ttext, array('class' => 'event-ics')
		);

		return $res;
	}

	/**
	 * Parses tag ical_url
	 * returns url to session ical export
	 *
	 * @return string
	 */
	private function getTag_ical_url()
	{
		$res = $this->absoluteUrls(
			RedeventHelperRoute::getDetailsRoute(
				$this->getEvent()->getData()->slug,
				$this->getEvent()->getData()->xslug
			) . '&format=raw&layout=ics',
			false
		);

		return $res;
	}

	/**
	 * Parses tag summary
	 * returns event summary
	 *
	 * @return string
	 */
	private function getTag_summary()
	{
		return $this->getEvent()->getData()->summary;
	}

	/**
	 * Parses tag moreinfo
	 * returns list of attachments
	 *
	 * @return string
	 */
	private function getTag_attachments()
	{
		return $this->attachmentsHTML();
	}

	/**
	 * Parses tag moreinfo
	 * generates a modal link to a more info form for the session
	 *
	 * @return string
	 */
	private function getTag_moreinfo()
	{
		JHTML::_('behavior.modal', 'a.moreinfo');
		$link = JRoute::_(
			RedeventHelperRoute::getMoreInfoRoute(
				$this->getEvent()->getData()->xslug,
				array('tmpl' => 'component')
			)
		);
		$text = '<a class="moreinfo" title="' . JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL')
			. '" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 400, y: 500}}">'
			. JText::_('COM_REDEVENT_DETAILS_MOREINFO_BUTTON_LABEL')
			. ' </a>';

		return $text;
	}

	/**
	 * returns event creator name
	 *
	 * @return string
	 */
	private function getTag_author_name()
	{
		return $this->getEvent()->getData()->creator_name;
	}

	/**
	 * returns event creator email
	 *
	 * @return string
	 */
	private function getTag_author_email()
	{
		return $this->getEvent()->getData()->creator_email;
	}

	/**************  venue tags ******************/

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue()
	{
		return $this->getEvent()->getData()->venue;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_title()
	{
		return $this->getTag_venue();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_code()
	{
		return $this->getEvent()->getData()->venue_code;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_company()
	{
		return $this->getEvent()->getData()->venue_company;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_city()
	{
		return $this->getEvent()->getData()->location;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_city()
	{
		return $this->getTag_city();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venues()
	{
		return $this->SignUpLinks();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_street()
	{
		return $this->getEvent()->getData()->street;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_zip()
	{
		return $this->getEvent()->getData()->plz;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_state()
	{
		return $this->getEvent()->getData()->state;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_link()
	{
		$link = JHTML::link(
			$this->absoluteUrls(
				RedeventHelperRoute::getVenueEventsRoute($this->getEvent()->getData()->venueslug)
			),
			$this->getEvent()->getData()->venue
		);

		return $link;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_website()
	{
		$res = '';

		if (!empty($this->getEvent()->getData()->venueurl))
		{
			$res = JHTML::link(
				$this->absoluteUrls(($this->getEvent()->getData()->venueurl)),
				JText::_('COM_REDEVENT_Venue_website')
			);
		}

		return $res;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venueimage()
	{
		if (!$this->getEvent()->getData()->locimage)
		{
			return '';
		}

		$venueimage = JHTML::image(
			JURI::root() . $this->getEvent()->getData()->locimage,
			$this->getEvent()->getData()->venue,
			array('title' => $this->getEvent()->getData()->venue)
		);
		$venuelink = JHTML::link(
			$this->absoluteUrls(
				RedeventHelperRoute::getVenueEventsRoute($this->getEvent()->getData()->venueslug)
			),
			$venueimage
		);

		return $venuelink;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_image()
	{
		return $this->getTag_venueimage();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_thumb()
	{
		$venueimage = RedeventImage::modalimage($this->getEvent()->getData()->locimage, $this->getEvent()->getData()->venue);

		return $venueimage;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_description()
	{
		return $this->getEvent()->getData()->venue_description;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_country()
	{
		return RedeventHelperCountries::getCountryName($this->getEvent()->getData()->country);
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_countryflag()
	{
		return RedeventHelperCountries::getCountryFlag($this->getEvent()->getData()->country);
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_mapicon()
	{
		return RedeventHelperOutput::mapicon($this->getEvent()->getData(), array('class' => 'event-map'));
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_venue_map()
	{
		return RedeventHelperOutput::map($this->getEvent()->getData(), array('class' => 'event-full-map'));
	}

	/**************  registration tags ******************/

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_redform_title()
	{
		return $this->getEvent()->getData()->formname;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_inputname()
	{
		$text = '<div id="divsubemailname">'
			. '<div class="divsubemailnametext">' . JText::_('COM_REDEVENT_NAME') . '</div>'
			. '<div class="divsubemailnameinput"><input type="text" name="subemailname" /></div>'
			. '</div>';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_inputemail()
	{
		$text = '<div id="divsubemailaddress">'
			. '<div class="divsubemailaddresstext">' . JText::_('COM_REDEVENT_EMAIL') . '</div>'
			. '<div class="divsubemailaddressinput"><input type="text" name="subemailaddress" /></div>'
			. '</div>';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_submit()
	{
		$text = '<div id="disubemailsubmit"><input type="submit" value="' . JText::_('COM_REDEVENT_SUBMIT') . '" /></div>';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_registrationend()
	{
		$res = '';

		if (RedeventHelperDate::isValidDate($this->getEvent()->getData()->registrationend))
		{
			$elsettings = RedeventHelper::config();
			$timezone = new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset')));
			$date = new JDate($this->getEvent()->getData()->registrationend, new DateTimeZone('UTC'));
			$date->setTimezone($timezone);

			return $date->format($elsettings->get('formatdate', 'd.m.Y') . ' ' . $elsettings->get('formattime', 'H:i'), true);
		}

		return $res;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_username()
	{
		if ($user = $this->getSubmissionUser($this->submitkey))
		{
			return $user->get('username');
		}

		$res = '';
		$email = $this->getRFCore()->getSubmissionContactEmail($this->submitkey, false);

		if ($email)
		{
			$res = isset($email['username']) ? $email['username'] : '';
		}

		return $res;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_useremail()
	{
		if ($user = $this->getSubmissionUser($this->submitkey))
		{
			return $user->get('email');
		}

		$res = '';
		$email = $this->getRFCore()->getSubmissionContactEmail($this->submitkey, true);

		if ($email)
		{
			$res = isset($email['email']) ? $email['email'] : '';
		}

		return $res;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_userfullname()
	{
		if ($user = $this->getSubmissionUser($this->submitkey))
		{
			return $user->get('name');
		}

		$res = '';
		$email = $this->getRFCore()->getSubmissionContactEmail($this->submitkey, true);

		if ($email)
		{
			$res = isset($email['fullname']) ? $email['fullname'] : '';
		}

		return $res;
	}

	/**
	 * Parses tag answers
	 * returns attendee answers to registration form
	 *
	 * @return string
	 */
	private function getTag_answers()
	{
		return $this->answersToHtml();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_eventplaces()
	{
		return $this->getEvent()->getData()->maxattendees;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_waitinglistplaces()
	{
		return $this->getEvent()->getData()->maxwaitinglist;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_eventplacesleft()
	{
		return $this->getEvent()->getPlacesLeft();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_waitinglistplacesleft()
	{
		return $this->getEvent()->getWaitingPlacesLeft();
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_webformsignup()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		$mainframe = JFactory::getApplication();
		$base_url = JURI::root();
		$iconspath = $base_url . 'media/com_redevent/images/';
		$elsettings = RedeventHelper::config();
		$text = '<span class="vlink webform">'
			. JHTML::_(
				'link',
				$this->absoluteUrls(
					RedeventHelperRoute::getSignupRoute('webform', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)
				),
				JHTML::_(
					'image', $iconspath . $elsettings->get('signup_webform_img', 'form_icon.gif'),
					JText::_($elsettings->get('signup_webform_text'))
				)
			)
			. '</span> ';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_emailsignup()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		$mainframe = JFactory::getApplication();
		$base_url = JURI::root();
		$iconspath = $base_url . 'media/com_redevent/images/';
		$elsettings = RedeventHelper::config();
		$text = '<span class="vlink email">'
			. JHTML::_(
				'link',
				$this->absoluteUrls(
					RedeventHelperRoute::getSignupRoute('email', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)
				),
				JHTML::_(
					'image', $iconspath . $elsettings->get('signup_email_img', 'email_icon.gif'),
					JText::_($elsettings->get('signup_email_text')),
					'width="24px" height="24px"'
				)
			)
			. '</span> ';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_formalsignup()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		$mainframe = JFactory::getApplication();
		$base_url = JURI::root();
		$iconspath = $base_url . 'media/com_redevent/images/';
		$elsettings = RedeventHelper::config();
		$text = '<span class="vlink formaloffer">'
			. JHTML::_(
				'link',
				$this->absoluteUrls(
					RedeventHelperRoute::getSignupRoute('formaloffer', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)
				),
				JHTML::_(
					'image', $iconspath . $elsettings->get('signup_formal_offer_img', 'formal_icon.gif'),
					JText::_($elsettings->get('signup_formal_offer_text')),
					'width="24px" height="24px"'
				)
			)
			. '</span> ';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_externalsignup()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		$mainframe = JFactory::getApplication();
		$base_url = JURI::root();
		$iconspath = $base_url . 'media/com_redevent/images/';
		$elsettings = RedeventHelper::config();

		if (!empty($this->getEvent()->getData()->external_registration_url))
		{
			$link = $this->getEvent()->getData()->external_registration_url;
		}
		else
		{
			$link = $this->getEvent()->getData()->submission_type_external;
		}

		$text = '<span class="vlink external">'
			. JHTML::_(
				'link',
				$link,
				JHTML::_(
					'image', $iconspath . $elsettings->get('signup_external_img', 'external_icon.gif'),
					$elsettings->get('signup_external_text')
				),
				'target="_blank"'
			)
			. '</span> ';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_phonesignup()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		$mainframe = JFactory::getApplication();
		$base_url = JURI::root();
		$iconspath = $base_url . 'media/com_redevent/images/';
		$elsettings = RedeventHelper::config();
		$text = '<span class="vlink phone">'
			. JHTML::_(
				'link',
				$this->absoluteUrls(
					RedeventHelperRoute::getSignupRoute('phone', $this->getEvent()->getData()->slug, $this->getEvent()->getData()->xslug)
				),
				JHTML::_(
					'image', $iconspath . $elsettings->get('signup_phone_img', 'phone_icon.gif'),
					JText::_($elsettings->get('signup_phone_text')),
					'width="24px" height="24px"'
				)
			)
			. '</span> ';

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_webformsignuppage()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		// Check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_webform) == 0)
		{
			$text = $this->replaceTags($this->getEvent()->getData()->submission_type_webform);
		}
		else
		{
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_formalsignuppage()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		// Check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_formal_offer) == 0)
		{
			$text = $this->getFormalOffer($this->getEvent()->getData());
		}
		else
		{
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_phonesignuppage()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		// Check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_phone) == 0)
		{
			$text = $this->replaceTags($this->getEvent()->getData()->submission_type_phone);
		}
		else
		{
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_emailsignuppage()
	{
		$registration_status = $this->canregister();

		if (!$registration_status->canregister)
		{
			$img = JHTML::_(
				'image', JURI::root() . 'media/com_redevent/images/agt_action_fail.png',
				$registration_status->status,
				array('class' => 'hasTooltip', 'title' => $registration_status->status)
			);

			return $img;
		}

		// Check that there is no loop with the tag inclusion
		if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_email) == 0)
		{
			$text = $this->getEmailSubmission($this->getEvent()->getData());
		}
		else
		{
			JError::raiseNotice(0, JText::_('COM_REDEVENT_ERROR_TAG_LOOP_XXXXSIGNUPPAGE'));
			$text = '';
		}

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_paymentrequest()
	{
		$text = '';
		$link = $this->getTag_paymentrequestlink();

		if (!empty($link))
		{
			$text = JHTML::link($link, JText::_('COM_REDEVENT_Checkout'), '');
		}

		return $text;
	}

	/**
	 * Parses a tag
	 *
	 * @return string
	 */
	private function getTag_paymentrequestlink()
	{
		$app = JFactory::getApplication();
		$lang = $app->input->get('lang');
		$link = '';

		if (!empty($this->submitkey))
		{
			$title = urlencode(
				$this->getEvent()->getData()->title
				. ' '
				. RedeventHelperDate::formatdate(
					$this->getEvent()->getData()->dates,
					$this->getEvent()->getData()->times
				)
			);
			$link = 'index.php?option=com_redform&task=payment.select&source=redevent&key='
				. $this->submitkey . '&paymenttitle=' . $title;

			if ($lang)
			{
				$link .= '&lang=' . $lang;
			}

			$link = $this->absoluteUrls($link, false);
		}

		return $link;
	}

	/**
	 * Parses registrationid tag
	 * returns unique registration id
	 *
	 * @return string
	 */
	private function getTag_registrationid()
	{
		$text = '';

		if (!empty($this->submitkey))
		{
			$text = $this->getAttendeeUniqueId($this->submitkey);
		}

		return $text;
	}

	/**
	 * Parses total_price tag
	 * total price for registration, including redform fields
	 *
	 * @return string
	 */
	private function getTag_total_price()
	{
		return $this->getSubmissionTotalPrice();
	}

	/**
	 * returns gps position of the venue
	 *
	 * @return string
	 */
	private function getTag_latlong()
	{
		$session = $this->getEvent()->getData();

		if ($session->latitude || $session->longitude)
		{
			return $session->latitude . ',' . $session->longitude;
		}

		return '';
	}
}
