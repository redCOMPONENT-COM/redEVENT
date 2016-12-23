<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

RHelperAsset::load('redevent-backend.css');

$icons = array(
    array(
        'link' => 'index.php?option=com_redevent&task=tools.importeventlist',
        'icon' => 'icon-48-cleaneventimg.png',
        'text' => JText::_('COM_REDEVENT_IMPORT_EVENTLIST'),
        'desc' => JText::_('COM_REDEVENT_IMPORT_EVENTLIST_DESC'),
        'access' => 'core.edit'),
    array(
        'link' => 'index.php?option=com_redevent&task=tools.autoarchive',
        'icon' => 'icon-48-cleaneventimg.png',
        'text' => JText::_('COM_REDEVENT_TRIGGER_AUTOARCHIVE'),
        'desc' => JText::_('COM_REDEVENT_TRIGGER_AUTOARCHIVE_DESC'),
        'access' => 'core.edit'),
    array(
        'link' => 'index.php?option=com_redevent&task=tools.sampledata',
        'icon' => 'icon-48-cleaneventimg.png',
        'text' => JText::_('COM_REDEVENT_ADD_SAMPLE_DATA'),
        'desc' => JText::_('COM_REDEVENT_ADD_SAMPLE_DATA_DESC'),
        'access' => 'core.edit'),
    array(
        'link' => 'index.php?option=com_redevent&view=attendeescsv',
        'icon' => 'icon-48-cleaneventimg.png',
        'text' => JText::_('COM_REDEVENT_TOOLS_CSV'),
        'desc' => JText::_('COM_REDEVENT_TOOLS_CSV_DESC'),
        'access' => 'core.edit'),
    array(
        'link' => JURI::root() . 'index.php?option=com_redevent&task=reminder.payment&tmpl=component',
        'icon' => 'icon-48-cleaneventimg.png',
        'text' => JText::_('COM_REDEVENT_TOOLS_PAYMENT_REMINDER'),
        'desc' => JText::_('COM_REDEVENT_TOOLS_PAYMENT_REMINDER_DESC'),
        'access' => 'core.edit')
);
?>

<div>
    <?php $iconsRow = array_chunk($icons, 2); ?>
    <?php foreach ($iconsRow as $row) : ?>
        <div class="row-fluid">
            <?php foreach ($row as $icon) : ?>
                <?php if ($this->user->authorise($icon['access'], 'com_redevent')): ?>
                    <div class="span6 row-fluid">
                        <div class="span4">
                        <a class="reDashboardIcons" href="<?php echo JRoute::_($icon['link']); ?>">
                            <div class="row-fluid pagination-centered">
                                <span class="dashboard-icon-link-icon">
                                    <?php echo JHTML::_('image', 'media/com_redevent/images/' . $icon['icon'], $icon['text']); ?>
                                </span>
                            </div>
                            <div class="row-fluid pagination-centered">
                                <p class="dashboard-icon-link-text">
                                    <strong><?php echo $icon['text']; ?></strong>
                                </p>
                            </div>
                        </a>
                        </div>
                        <div class="span8 well">
                            <?php echo $icon['desc']; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
