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
 * @since  __deploy_version__
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
	 * @since  __deploy_version__
	 */
	public function onRedeventViewGetToolbar(RedeventViewAdmin $view, RToolbar &$toolbar)
	{
		if ($view instanceof RedeventViewAttendees)
		{
			$group = new RToolbarButtonGroup;
			$button = RToolbarBuilder::createStandardButton(
				'attendees.labels',
				JText::_('PLG_SYSTEM_REDEVENT_LABELS_BUTTON_GET_LABELS'), '', 'icon-print', false
			);
			$group->addButton($button);

			$toolbar->addGroup($group);
		}
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
	 * @since __deploy_version__
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
	 * @since __deploy_version__
	 */
	private function getPdf()
	{
		$session = $this->getSession();
		$attendees = $this->getAttendees();

		$rawText = $this->getTextFromTextlibrary($this->params->get('label_content'));

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
	 * @since __deploy_version__
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
	 * @since __deploy_version__
	 */
	private function getFormat()
	{
		$format = $this->params->get('print_layout');

		if (!'custom' == $format)
		{
			return $format;
		}

		return array(
			'paper-size' => $this->params->get('custom_paper_size'),
			'metric' => 'mm',
			'marginLeft' => $this->params->get('custom_marginLeft'),
			'marginTop' => $this->params->get('custom_marginTop'),
			'NX' => $this->params->get('custom_nx'),
			'NY' => $this->params->get('custom_ny'),
			'SpaceX' => $this->params->get('custom_SpaceX'),
			'SpaceY' => $this->params->get('custom_SpaceY'),
			'width' => $this->params->get('custom_width'),
			'height' => $this->params->get('custom_height'),
			'font-size' => $this->params->get('custom_font_size'),
		);
	}

}
