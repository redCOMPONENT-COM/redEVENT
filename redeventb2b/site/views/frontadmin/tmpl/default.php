<?php
/**
 * @package    Redevent
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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

$params = JFactory::getApplication()->getParams('com_redeventb2b');
$gaCode = trim($params->get('gacode'));
$gaAffiliation = $params->get('gaaffiliation');

if ($gaCode)
{
	$script = <<<JS
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', '$gaCode', 'maersktraining.com');
	  ga('send', 'pageview');
	  ga('require', 'ecommerce', 'ecommerce.js');

	  var gaAffiliation = '$gaAffiliation';
JS;

	JFactory::getDocument()->addScriptDeclaration($script);
}

RHelperAsset::load('lib/bootstrap/css/boostrap.min.css', 'redcore');

JHtml::_('script', 'media/jui/js/jquery.autocomplete.min.js', false, false, false, false, true);
?>
<div id="redevent-admin">

<div id="redadmin-toolbar">
	<?php echo $this->loadTemplate('toolbar'); ?>
</div>

<div id="redadmin-main">
	<?php echo $this->loadTemplate('main'); ?>
</div>

</div>
