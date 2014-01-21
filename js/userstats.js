jQuery(document).ready(function() {
                jQuery(".asz_profile_table").tablesorter();

                jQuery('#asz_goto_totals').hide();

                var totalrow = jQuery('#asz_bottom_total').html();
                var rowcount = jQuery(".asz_profile_table tr").length;

                if(rowcount >= 12) {
                jQuery('.asz_profile_table thead').append('<tr class="asz_totals">'+ totalrow +'</tr>')
            	}
} );

