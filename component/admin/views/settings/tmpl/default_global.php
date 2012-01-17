<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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
 
defined('_JEXEC') or die('Restricted access');
?>
<table class="noshow">
  <tr>
    <td>
    <fieldset class="adminform"><legend><?php echo JText::_('COM_REDEVENT_ATTENTION' ); ?></legend>
    <table class="admintable" cellspacing="1">
      <tbody>
        <tr>
          <td><?php echo JText::_('COM_REDEVENT_GLOBAL_PARAM_DESC' ); ?></td>
        </tr>
      </tbody>
    </table>
    </fieldset>
    </td>
  </tr>
  <tr>
    <td>
	<?php
	echo JHtml::_('tabs.start','config-tabs-global', array('useCookie'=>1));
		$fieldSets = $this->globalparams->getFieldsets();
		foreach ($fieldSets as $name => $fieldSet) :
			$label = empty($fieldSet->label) ? 'COM_REDEVENT_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
			echo JHtml::_('tabs.panel',JText::_($label), 'publishing-details');
			if (isset($fieldSet->description) && !empty($fieldSet->description)) :
				echo '<p class="tab-description">'.JText::_($fieldSet->description).'</p>';
			endif;
	?>
			<ul class="config-option-list">
			<?php
			foreach ($this->globalparams->getFieldset($name) as $field):
			?>
				<li>
				<?php if (!$field->hidden) : ?>
				<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
				</li>
			<?php
			endforeach;
			?>
			</ul>


	<div class="clr"></div>
	<?php
		endforeach;
	echo JHtml::_('tabs.end');/*
	?>
    <?php echo $this->tabs->startPane('globalparams'); ?>
		<?php $j = 0; ?>
    <?php echo $this->tabs->startPanel(JText::_('COM_REDEVENT_GLOBAL_PARAMETERS' ), 'global'.$j); ?>
    <?php echo $this->globalparams->render('globalparams'); ?></fieldset>
    <?php echo $this->tabs->endPanel(); ?>
    
    <?php foreach ($this->globalparams->getGroups() as $key => $groups): ?>
		<?php if (strtolower($key) != '_default' && strtolower($key) != 'listslayout'): ?>
    <?php echo $this->tabs->startPanel(JText::_( strtoupper($key) ), 'global'.$j); ?>
        <?php echo $this->globalparams->render('globalparams', $key); ?></fieldset>
        
        <?php echo $this->tabs->endPanel(); ?>
		<?php endif; ?>
    <?php endforeach; ?>
    
    <?php echo $this->tabs->endPane(); */?>
    </td>
  </tr>
</table>