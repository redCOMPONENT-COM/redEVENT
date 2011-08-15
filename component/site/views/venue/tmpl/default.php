<?php
/**
 * @version 1.1 $Id: default.php 668 2008-05-12 14:32:13Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$row = & $this->row;
?>

<div class="venue">
  <div class="address">
  <h2 class="eventlist">
    <a href="<?php echo $row->target; ?>"><?php echo $this->escape($row->venue); ?></a>
  </h2>

      <?php if ($row->limage): ?>
      <img id="venueimg" src="<?php echo $row->limage['thumb']; ?>"/>
      <?php endif; ?>

      <dl class="location floattext">
        <?php if (count($row->categories)): ?>
        <dt class="venue_categories"><?php echo (count($row->categories) > 1 ? JText::_( 'CATEGORIES' ) : JText::_( 'CATEGORY' )) .':'; ?></dt>
          <dd class="venue_categories">
          <?php
          $cats = array(); 
          foreach ($row->categories as $cat) {
          	$cats[] = $cat->name;
          }
          echo implode(', ', $cats);
          ?>
          </dd>
        <?php endif; ?>
        
        <?php if (($this->elsettings->showdetlinkvenue == 1) && (!empty($row->url))) : ?>
        <dt class="venue_website"><?php echo JText::_( 'WEBSITE' ).':'; ?></dt>
          <dd class="venue_website">
          <a href="<?php echo $row->url; ?>" target="_blank"> <?php echo $row->urlclean; ?></a>
        </dd>
        <?php endif; ?>

        <?php
          if ( $this->elsettings->showdetailsadress == 1 ) :
          ?>

          <?php if ( $row->street ) : ?>
          <dt class="venue_street"><?php echo JText::_( 'STREET' ).':'; ?></dt>
        <dd class="venue_street">
            <?php echo $this->escape($row->street); ?>
        </dd>
        <?php endif; ?>

        <?php if ( $row->plz ) : ?>
          <dt class="venue_plz"><?php echo JText::_( 'ZIP' ).':'; ?></dt>
        <dd class="venue_plz">
            <?php echo $this->escape($row->plz); ?>
        </dd>
        <?php endif; ?>

        <?php if ( $row->city ) : ?>
          <dt class="venue_city"><?php echo JText::_( 'CITY' ).':'; ?></dt>
          <dd class="venue_city">
            <?php echo $this->escape($row->city); ?>
          </dd>
          <?php endif; ?>

          <?php if ( $row->state ) : ?>
        <dt class="venue_state"><?php echo JText::_( 'STATE' ).':'; ?></dt>
        <dd class="venue_state">
            <?php echo $this->escape($row->state); ?>
        </dd>
        <?php endif; ?>

        <?php if ( $row->country ) : ?>
        <dt class="venue_country"><?php echo JText::_( 'COUNTRY' ).':'; ?></dt>
          <dd class="venue_country">
            <?php echo $row->countryimg ? $row->countryimg : $row->country; ?>
          </dd>
          <?php endif; ?>

          <dt class="venue_assignedevents"><?php echo JText::_( 'EVENTS' ).':'; ?></dt>
          <dd class="venue_assignedevents">
            <a href="<?php echo $row->target; ?>"><?php echo $row->assignedevents; ?></a>
          </dd>
      <?php
      endif;
      ?>

    </dl>
  </div>
    <?php if ($this->elsettings->showlocdescription == 1 && !empty($row->locdescription)) : ?>
    <div class="description">
      <h2 class="description"><?php echo JText::_( 'DESCRIPTION' ).':'; ?></h2>
        <?php echo $row->locdescription; ?>
    </div>
    <?php endif; ?>
</div>