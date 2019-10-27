/**
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

(function($){

	$(document).ready(function() {
		$('.dynfilter').change(function() {
			this.form.submit();
			return true;
		});

		// show/hide filters in views
		if ($('#el-events-filters'))
		{
			if ($('#f-showfilters') && $('#f-showfilters').val() > 0) {
				$('#el-events-filters').css('display', 'block');
			}
			else {
				$('#el-events-filters').css('display', 'none');
			}
			if ($('#filters-toggle'))
			{
				$('#filters-toggle').click(function(){
					if ($('#el-events-filters').css('display') == 'none')
					{
						$('#el-events-filters').css('display', 'block');
						$('#f-showfilters').val(1);
					}
					else
					{
						$('#el-events-filters').css('display', 'none');
						$('#f-showfilters').val(0);
					}
				});
			}
		}

		if ($('#filters-reset'))
		{
			$('#filters-reset').click(function(){
				$('#el-events-filters').find(':input').each(function(el){
					$(this).val('');
				});

				this.form.submit();

				return true;
			});
		}
	});
})(jQuery);

