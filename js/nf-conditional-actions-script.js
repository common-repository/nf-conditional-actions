(function( $ ) {
    $( document ).ready( function() {

		if (document.createStyleSheet) {
			document.createStyleSheet(nf_conditional_actions.js_style_url);
		} else {
			$("head").append($("<link rel='stylesheet' href='" + nf_conditional_actions.js_style_url + "' type='text/css' />"));
		}

		$(".no-quicktags").parent().find(".quicktags-toolbar").css("display", "none");

		$( document ).on("click", ".nf_addConditionButton", function() {
			var button = $(this);
			var prefix = button.data('prefix');
			var length = parseInt($("#settings-conditional_"+prefix+"_length").val());
			var data = {
				'action': 'add_conditional_action_' + prefix,
				'index': length,
				'form_id': parseInt($("#_form_id").val())
			};

			$.post(ajaxurl, data, function(response) {
				button.closest("tr").before(response);
				$( '.nf-fields-combobox' ).combobox();
				quicktags({id : 'settings-conditional_'+prefix+'_condition_'+length});
				//tinyMCE.execCommand('mceAddEditor', false, 'settings-conditional_condition_'+length);
				quicktags({id : 'settings-conditional_'+prefix+'_'+length});
				tinyMCE.execCommand('mceAddEditor', false, 'settings-conditional_'+prefix+'_'+length);
				$("#settings-conditional_"+prefix+"_length").val(length + 1);
				$(".no-quicktags").parent().find(".quicktags-toolbar").css("display", "none");
			});
		});

		$( document).on("click", ".nf_removeConditionButton", function() {
			$(this).closest("tr").next().remove();
			$(this).closest("tr").remove();
		});
    } );
} )( jQuery );