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
<table cellspacing="0" cellpadding="4" border="0" width="100%">
	<tr>
		<td width="10%">
			<div class="linkicon">
				<a href="index.php?option=com_redevent&amp;controller=tools&amp;task=cleaneventimg">
					<?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_('COM_REDEVENT_CLEANUP_EVENT_IMG' ) ); ?>
					<span><?php echo JText::_('COM_REDEVENT_CLEANUP_EVENT_IMG' ); ?></span>
				</a>
			</div>
		</td>
		<td width="40%" valign="middle">
			<?php echo JText::_('COM_REDEVENT_CLEANUP_EVENT_IMG_DESC' ); ?>
		</td>
		<td width="10%">
			<div class="linkicon">
				<a href="index.php?option=com_redevent&amp;controller=tools&amp;task=cleanvenueimg">
					<?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleanvenueimg.png',  JText::_('COM_REDEVENT_CLEANUP_VENUE_IMG' ) ); ?>
					<span><?php echo JText::_('COM_REDEVENT_CLEANUP_VENUE_IMG' ); ?></span>
				</a>
			</div>
		</td>
		<td width="40%" valign="middle">
			<?php echo JText::_('COM_REDEVENT_CLEANUP_VENUE_IMG_DESC' ); ?>
		</td>
	</tr>
	<tr>
    <td width="10%">
      <div class="linkicon">
        <a href="index.php?option=com_redevent&amp;task=importeventlist">
          <?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_('COM_REDEVENT_IMPORT_EVENTLIST' ) ); ?>
          <span><?php echo JText::_('COM_REDEVENT_IMPORT_EVENTLIST' ); ?></span>
        </a>
      </div>
    </td>
    <td width="40%" valign="middle">
      <?php echo JText::_('COM_REDEVENT_IMPORT_EVENTLIST_DESC' ); ?>
    </td>	
    
    <td width="10%">
      <div class="linkicon">
        <a href="index.php?option=com_redevent&amp;task=autoarchive">
          <?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_('COM_REDEVENT_TRIGGER_AUTOARCHIVE' ) ); ?>
          <span><?php echo JText::_('COM_REDEVENT_TRIGGER_AUTOARCHIVE' ); ?></span>
        </a>
      </div>
    </td>
    <td width="40%" valign="middle">
      <?php echo JText::_('COM_REDEVENT_TRIGGER_AUTOARCHIVE_DESC' ); ?>
    </td>	
	</tr>
	<tr>    
    <td width="10%">
      <div class="linkicon">
        <a href="index.php?option=com_redevent&controller=tools&task=checkdb">
          <?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_('COM_REDEVENT_CHECK_DATABASE' ) ); ?>
          <span><?php echo JText::_('COM_REDEVENT_CHECK_DATABASE' ); ?></span>
        </a>
      </div>
    </td>
    <td width="40%" valign="middle">
      <?php echo JText::_('COM_REDEVENT_CHECK_DATABASE_DESC' ); ?>
    </td>	
    
    <td width="10%">
      <div class="linkicon">
        <a href="index.php?option=com_redevent&controller=tools&task=fixdb">
          <?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_('COM_REDEVENT_FIX_DATABASE' ) ); ?>
          <span><?php echo JText::_('COM_REDEVENT_FIX_DATABASE' ); ?></span>
        </a>
      </div>
    </td>
    <td width="40%" valign="middle">
      <?php echo JText::_('COM_REDEVENT_FIX_DATABASE_DESC' ); ?>
    </td>	
	</tr>
	<tr>
    <td width="10%">
      <div class="linkicon">
        <a href="index.php?option=com_redevent&amp;task=sampledata">
          <?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_('COM_REDEVENT_ADD_SAMPLE_DATA' ) ); ?>
          <span><?php echo JText::_('COM_REDEVENT_ADD_SAMPLE_DATA' ); ?></span>
        </a>
      </div>
    </td>
    <td width="40%" valign="middle">
      <?php echo JText::_('COM_REDEVENT_ADD_SAMPLE_DATA_DESC' ); ?>
    </td>	
    
    <td width="10%">
      <div class="linkicon">
        <a href="index.php?option=com_redevent&amp;view=csvtool">
          <?php echo JHTML::_('image', 'administrator/components/com_redevent/assets/images/icon-48-cleaneventimg.png',  JText::_( 'COM_REDEVENT_TOOLS_CSV' ) ); ?>
          <span><?php echo JText::_( 'COM_REDEVENT_TOOLS_CSV' ); ?></span>
        </a>
      </div>
    </td>
    <td width="40%" valign="middle">
      <?php echo JText::_( 'COM_REDEVENT_TOOLS_CSV_DESC' ); ?>
    </td>	
    <td width="40%" valign="middle">
      
    </td>	
	</tr>
	
</table>