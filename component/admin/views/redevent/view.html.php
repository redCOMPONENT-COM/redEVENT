<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View class for the redEVENT home screen
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewRedevent extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		// Load pane behavior
		jimport('joomla.html.pane');

		// Initialise variables
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Build toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT'), 'home');

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		$model = FOFModel::getTmpInstance('Redevent', 'RedeventModel');

		// Get data from the model
		$events = $model->getEventsdata();
		$venue = $model->getVenuesdata();
		$category = $model->getCategoriesdata();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_REDEVENT'));

		// Add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		// Create Submenu
		ELAdmin::setMenu();

		// Assign vars to the template
		$this->events = $events;
		$this->venue = $venue;
		$this->category = $category;
		$this->user = $user;

		parent::display($tpl);
	}

	/**
	 * Creates the buttons view
	 *
	 * @param   string   $link   targeturl
	 * @param   string   $image  path to image
	 * @param   string   $text   image description
	 * @param   boolean  $modal  1 for loading in modal
	 *
	 * @return void
	 */
	protected function quickiconButton($link, $image, $text, $modal = 0)
	{
		// Initialise variables
		$lang = JFactory::getLanguage();
  		?>

		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<?php
				if ($modal == 1)
				{
					JHTML::_('behavior.modal');
				?>
					<a href="<?php echo $link . '&amp;tmpl=component'; ?>" style="cursor:pointer" class="modal" rel="{handler: 'iframe', size: {x: 650, y: 400}}">
				<?php
				}
				else
				{
				?>
					<a href="<?php echo $link; ?>">
				<?php
				}

					echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/' . $image, $text);
				?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
}
