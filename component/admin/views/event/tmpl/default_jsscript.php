<script type="text/javascript" charset="utf-8">
	
	jQuery("#submission_type_email_check").bind('click', function() {
		if (jQuery("#submission_type_email_check").attr('checked')) {
			jQuery("#submission_type_email_input").show('slideUp');
      jQuery("#submission_type_email_body_input").css('display', 'block');
		}
		else {
		  jQuery("#submission_type_email_input").hide('slideUp');
      jQuery("#submission_type_email_body_input").css('display', 'none');
		}
	})
	jQuery("#submission_type_phone_check").bind('click', function() {
		if (jQuery("#submission_type_phone_check").attr('checked')) {
			jQuery("#submission_type_phone_input").show('slideUp');
		}
		else jQuery("#submission_type_phone_input").hide('slideUp');
	})
	
	jQuery("#submission_type_formaloffer_check").bind('click', function() {
		if (jQuery("#submission_type_formaloffer_check").attr('checked')) {
			jQuery("#submission_type_formaloffer_input").show('slideUp');
      jQuery("#submission_type_formaloffer_body_input").css('display', 'block');
		}
		else {
		  jQuery("#submission_type_formaloffer_input").hide('slideUp');
      jQuery("#submission_type_formaloffer_body_input").css('display', 'none');
    }
	})
	
	jQuery("#submission_type_webform_check").bind('click', function() {
		if (jQuery("#submission_type_webform_check").attr('checked')) {
			jQuery("#submission_type_webform_input").show('slideUp');
		}
		else jQuery("#submission_type_webform_input").hide('slideUp');
	})
	jQuery("#submission_type_formaloffer_body_check").bind('click', function() {
		if (jQuery("#submission_type_formaloffer_body_check").attr('checked')) {
			jQuery("#submission_type_formaloffer_body_input").show('slideUp');
		}
		else jQuery("#submission_type_formaloffer_body_input").hide('slideUp');
	})
	
	jQuery("#submission_type_email_body_check").bind('click', function() {
		if (jQuery("#submission_type_email_body_check").attr('checked')) {
			jQuery("#submission_type_email_body_input").show('slideUp');
		}
		else jQuery("#submission_type_email_body_input").hide('slideUp');
	})
</script>
