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

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<div id="redevent" class="el_categoriesview<?= $this->params->get('pageclass_sfx') ?>">
<p class="buttons">
	<?php
		echo RedeventHelperOutput::submitbutton($this->canCreate, $this->params);
	?>
</p>

<div class="redevent-top">
	<div id="feature">
		<?php if (!empty($row->banner)): ?>
		<div class="banner-image" style="background-image: url(<?php echo "banner image"; ?>)">
		</div>
		<?php endif; ?>
	</div>
</div>
<div class="container">
	<div class="breadcrumb"></div>
	<div class="category-info reditem-content-header text-center">
		<?php if ($this->params->def( 'show_pagepage_title', 1 )) : ?>
			<h1 class="componentheading category-title header-title">
				<?php echo $this->escape($this->pagetitle); ?>
			</h1>
		<?php endif; ?>
		<div class="category-introtext">
		<pre>
		<?php print_r($description); ?>
		</pre>
		</div>
    </div>
    <div class="aesir-container">
	  	<div class="row">
	      	<?php foreach ($this->rows as $row) : ?>
	        <div class="col-xs-6">
	        	<div class="item-grid grid-horizontal">
	        		<div class="aesir-item-image">
						<a <?php if (empty($row->image)): ?>class="empty"<?php endif; ?> href="<?php echo $row->linktarget; ?>" style="background-image: url(<?php echo $row->image; ?>);"></a>
					</div>
					<div class="aesir-item-info">
						<div class="aesir-item-info-top">
							<div class="aesir-item-title">
								<?php echo JHTML::_('link', JRoute::_($row->linktarget), $row->name); ?>
							</div>
						</div>
						<div class="aesir-item-descr">
							<?php echo $row->description ; ?>
						</div>
					</div>
	        	</div>
	        </div>
	        <?php endforeach ?>
	    </div>
	</div>
</div>

