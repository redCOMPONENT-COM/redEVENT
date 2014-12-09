/**
 * @package    Redevent.js
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */
(function($){
	$(document).ready(function() {
		$('#add-role').click(function(){
			var sel = $('#trnewrole').find('select');
			var rrole = sel[0];
			var urole = sel[1];
			if (! ($(rrole).val() && $(urole).val())) {
				return true;
			}

			// Value ok, add new row
			var newrow = $('#trnewrole').clone(false).removeAttr('id');
			newrow.find('select.rrole').removeAttr('id').value = rrole.value;
			newrow.find('select[name^=urole]').removeAttr('id').value = urole.value;
			newrow.find('button').removeAttr('name').addClass('remove-role').text(Joomla.JText._("COM_REDEVENT_REMOVE"));
			$('#trnewrole').before(newrow);

			// Reset 'new row' values
			$(rrole).val(0);
			$(urole).val(0);

			$("#re-roles").trigger("chosen:updated");
		});

		$('#re-roles').on('click', 'button.remove-role', function(){
			$(this).parents('tr').remove();
		});
	});
})(jQuery);
