<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/labels.php';

/**
 * redEVENT labels plugin
 *
 * @since  3.2.4
 */
class PlgSystemRedevent_Labels extends JPlugin
{
	/**
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * Intercepts task
	 *
	 * @return void
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$task = $input->get('task');

		if ($input->get('option') !== 'com_redevent')
		{
			return;
		}

		switch ($task)
		{
			case 'attendees.labels':
				return $this->getPdf();
		}

		return;
	}

	/**
	 * Override toolbar
	 *
	 * @param   RedeventViewAdmin  $view     the view object
	 * @param   RToolbar           $toolbar  the toolbar
	 *
	 * @return void
	 *
	 * @since  3.2.4
	 */
	public function onRedeventViewGetToolbar(RedeventViewAdmin $view, RToolbar &$toolbar)
	{
		if ($view instanceof RedeventViewAttendees)
		{
			$group = new RToolbarButtonGroup;

			$xref = JFactory::getApplication()->input->get('xref');

			// Workaround for redCORE styling the button differently with .modal class...
			JFactory::getDocument()->addStyleDeclaration(<<<CSS
				.redcore .modal.attendees-labels-button {
					width:auto;
					left:0;
				}
CSS
			);

			$button = RToolbarBuilder::createModalButton(
				'label-modal', JText::_('PLG_SYSTEM_REDEVENT_LABELS_BUTTON_GET_LABELS'), 'attendees-labels-button', 'icon-print', false,
				array(
					'url' => 'index.php?option=com_ajax&plugin=getAttendeesLabels&format=html&tmpl=component&xref=' . $xref
				)
			);
			$group->addButton($button);

			$toolbar->addGroup($group);
		}
	}

	/**
	 * Ajax Callback
	 *
	 * @return string
	 *
	 * @since 3.2.4
	 */
	public function onAjaxGetAttendeesLabels()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$xref = $input->getInt('xref');

		RForm::addFormPath(__DIR__);
		$form = RForm::getInstance("layout_settings", "layout_settings");

		$html = RdfLayoutHelper::render(
			'redevent.labels.settings',
			compact('xref', 'form'),
			null,
			array('component' => 'com_redevent', 'defaultLayoutsPath' => __DIR__ . '/layouts')
		);

		return $html;
	}

	/**
	 * Return associated attendees
	 *
	 * @return RedeventEntityAttendee[]
	 */
	private function getAttendees()
	{
		$session = $this->getSession();

		if (!$attendees = $session->getAttendees())
		{
			return false;
		}

		return array_filter(
			$attendees,
			function ($attendee)
			{
				return 0 == $attendee->waiting_list && 0 == $attendee->cancelled && 1 == $attendee->confirmed;
			}
		);
	}

	/**
	 * Get session
	 *
	 * @return RedeventEntitySession
	 *
	 * @since 3.2.4
	 */
	private function getSession()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$xref = $input->getInt('xref');

		return RedeventEntitySession::load($xref);
	}

	/**
	 * Get the pdf
	 *
	 * @return void
	 *
	 * @since 3.2.4
	 */
	private function getPdf()
	{
		$input = JFactory::getApplication()->input;
		$session = $this->getSession();
		$attendees = $this->getAttendees();

		$rawText = $this->getTextFromTextlibrary($input->getString('label_content'));

		$format = $this->getFormat();

		$pdf = new RedeventLabelLabels($format);
		$pdf->SetTitle($session->getFullTitle());

		$pdf->AddPage();

		foreach ($attendees as $attendee)
		{
			$text = $attendee->replaceTags($rawText);
			$pdf->Add_Label($text);
		}

		$pdf->Output(JFile::makeSafe($session->getFullTitle() . '.pdf'), 'I');
		JFactory::getApplication()->close();
	}

	/**
	 * Get base text from text library
	 *
	 * @param   string  $tagName  tag name
	 *
	 * @return object
	 *
	 * @since 3.2.4
	 */
	private function getTextFromTextlibrary($tagName)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('text_field')
			->from('#__redevent_textlibrary')
			->where('text_name = ' . $db->q($tagName));

		$db->setQuery($query);

		 return $db->loadResult();
	}

	/**
	 * Get format
	 *
	 * @return array|mixed
	 *
	 * @since 3.2.4
	 */
	private function getFormat()
	{
		$input = JFactory::getApplication()->input;
		$format = $input->getString('print_layout');

		if ('custom' != $format)
		{
			return $format;
		}

		return array(
			'paper-size' => $input->getString('custom_paper_size'),
			'metric' => 'mm',
			'marginLeft' => $input->getString('custom_marginLeft'),
			'marginTop' => $input->getString('custom_marginTop'),
			'NX' => $input->getInt('custom_nx'),
			'NY' => $input->getInt('custom_ny'),
			'SpaceX' => $input->getString('custom_SpaceX'),
			'SpaceY' => $input->getString('custom_SpaceY'),
			'width' => $input->getString('custom_width'),
			'height' => $input->getString('custom_height'),
			'font-size' => $input->getInt('custom_font_size'),
		);
	}

}
