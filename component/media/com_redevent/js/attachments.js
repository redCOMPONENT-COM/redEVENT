/**
* @package    Redevent.js
* @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
* @license    GNU/GPL, see LICENSE.php
*/

/**
 * this file manages the js script for adding/removing attachements in event
 */
(function($){

	var addattach = function(){
	};

	$(document).ready(function () {
		var tbody = $('#re-attachments').find('tbody');

		tbody.on('click', '.attach-add', function(){
			var row = tbody.find('tr:last-of-type').clone(false);
			row.find('.attach-field').val('');
			row.appendTo(tbody);
			tbody.trigger("chosen:updated");
		});

		tbody.on('click', '.attach-remove', function(event){
			var element = $(this);

			var row = element.parents('tr').first();

			if (!confirm(Joomla.JText._("COM_REDEVENT_ATTACHMENT_CONFIRM_MSG"))) {
				return false;
			}

			var inputId = row.find('input[name="attached-id[]"]');

			// Check if the row is empty
			if (!inputId.length)
			{
				// Remove if there are other rows
				if (tbody.find('tr').length > 1)
				{
					row.remove();
					return;
				}
				else
				{
					// Just reset it
					row.find('input').val('');
					return;
				}
			}

			var id = inputId.val();
			var url = 'index.php?option=com_redevent&task=attachments.remove&id='+id;

			$.ajax({
				url: url,
				dataType: 'json'
			}).done(function(data){
				if (data.success || 1)
				{
					var dummy = element;
					element.parents('tr').first().remove();
				}
				else
				{
					if (data.error)
					{
						alert(data.error);
					}
					else
					{
						alert('error');
					}
				}
			});
		});
	});
})(jQuery);
