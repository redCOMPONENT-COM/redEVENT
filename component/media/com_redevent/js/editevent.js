/**
 * @version 2.5
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
 
window.addEvent('domready', function() {

	$$( '.tagsdiv' ).each(function(item){
		// could put better init for modal
	});
	
	$$('input.reg-type').addEvent('click', function() {
		if (this.getProperty('checked')) {
			this.getParent().getElement('fieldset').setStyle('display', 'block');
		}
		else {
			this.getParent().getElement('fieldset').setStyle('display', 'none');
		}
	});
	
	if ($('submission_type_email_check')) {
		$('submission_type_email_check').addEvent('click', redEVENTEventCheck.checkSubmissionEmailState);
	}
	
	if ($$("input[name=send_pdf_form]")) {
		$$("input[name=send_pdf_form]").addEvent('change', redEVENTEventCheck.checkEmailPdfState);
	}
	
	$('activate1').addEvent('click', function(){
		$('notify1').setProperty('checked', 'checked');
		if ($('activate1').getProperty('checked')) {
			$$('.activation-field').setStyle('display', '');
		}
	});
	$('activate0').addEvent('click', function(){
		if ($('activate0').getProperty('checked')) {
			$$('.activation-field').setStyle('display', 'none');
		}
	});
	
	if ($('activate0').getProperty('checked')) {
		$$('.activation-field').setStyle('display', 'none');
	}

	$('notify0').addEvent('click', function(){
		$('activate0').setProperty('checked', 'checked').fireEvent('click');
	});
});

redEVENTEventCheck = {
	
	checkSubmissionEmailState : function ()	{
		redEVENTEventCheck.checkEmailPdfState();
		if ($("submission_type_email_check").getProperty('checked')) {
			$("submission_type_email_input").setStyle('display', 'block');
			$("submission_type_email_body_input").setStyle('display', 'block');
		}
		else {
			$("submission_type_email_input").setStyle('display', 'none');
			$("submission_type_email_body_input").setStyle('display', 'none');
		}
	},
	
	checkEmailPdfState : function()	{
		if ($("send_pdf_form1").getProperty('checked')) {
			$$(".submission_type_email_pdf_options").setStyle('display', '');
		}
		else {
			$$(".submission_type_email_pdf_options").setStyle('display', 'none');
		}	
	}
};
