<?php
/**
 * @version 1.0 $Id: default.php 30 2009-05-08 10:22:21Z roland $
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php if (count((array)$this->groups)) : ?>

<h2><?php echo JText::_('My groups'); ?></h2>

<table class="eventtable" summary="venues list">
	<thead>
		<tr>
			<th id="el_title" class="sectiontableheader" align="left"><?php echo JText::_('Name'); ?></th>
		</tr>
	</thead>
	<tbody>
  <?php 
  $i = 0;
  foreach ((array) $this->groups as $row) : 
  ?>
    <tr class="sectiontableentry<?php echo $i + 1 . $this->params->get( 'pageclass_sfx' ); ?>" >
      <td headers="el_title" align="left" valign="top"><?php echo $row->name; ?></td>   
    </tr>
  <?php 
  $i = 1 - $i;
  endforeach;
  ?>
	</tbody>
</table>
<?php endif; ?>