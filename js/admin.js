/**
 * Admin Control Panel JavaScripts
 */

jQuery(document).ready(function(){



//datepicker
if(jQuery('.custom_date').length > 0) {
		jQuery('.custom_date').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth : true,
		changeYear : true,
		showAnim : false
		});
}


		 jQuery("#asz_ind_select").change(function() {

				var asz_user_id = jQuery("#asz_ind_select").val();
				//alert(asz_user_id);
				jQuery('.asz_ind').hide();
				jQuery('#asz_ind_' + asz_user_id).toggle();

				jQuery('.asz_ind_posts').hide();
				jQuery('.asz_ind_posts_'+asz_user_id).toggle();

				jQuery('.asz_ind_posts_status_'+asz_user_id).toggle();


		});

		jQuery('table').tablesorter( { sortList: [[1,0]]  });



function asz_author_posts(authorname) {

jQuery('#user_stats_loading').show();

	//authorname = jQuery(this).attr('id');
	startdate = jQuery('#asz_start_date').val();
	enddate = jQuery('#asz_end_date').val();


				data = {
					action: 'asz_ind_show_posts',
					type: 'POST',
					dataType: 'text',
					"authorname" : authorname,
					"startdate" : startdate,
					"enddate" : enddate,

				};

				jQuery.post(ajaxurl, data, function(response) {

					//console.log(response);

					//jQuery('#statz_single_author_articles_table tbody').empty();
					jQuery('#statz_single_author_articles_table').append(response);


					//Reapply the sorting due to new content
					//seems ok unless the post has shit loads of categories then it goes really, REALLY slow.
					var resort = true; // re-apply the current sort
					jQuery('#statz_single_author_articles_table').trigger("updateAll", [resort]);

				//had to turn this into a function as otherwise it wouldnt work, think it needed to be called after the ajax call as the elements werent there.
				jQuery('.asz_reset').click(resetclicks);

jQuery('#user_stats_loading').hide();

				});


}







		function resetclicks() {
					var answer = confirm("Are you sure you want to delete the view count? \n\nTHIS IS PERMANENT!")
					if (answer){
						//nothing carry on and delete
					}
					else{
						//stop function!
						return;
					}


					postid = jQuery(this).attr('id');

								data = {
									action: 'asz_reset_count',
									type: 'POST',
									dataType: 'text',
									"postid" : postid,

								};

								jQuery.post(ajaxurl, data, function(response) {
									console.log(response);

									if(response > 0) {
											//jQuery('img #'+postid).parent().append("ERROR");
											//jQuery('img #'+postid).remove();
										jQuery('#viewcount_'+postid).empty();
										jQuery('#viewcount_'+postid).append('ERROR');

									}
									else {
										jQuery('#viewcount_'+postid).empty();
										jQuery('#viewcount_'+postid).append(response);
									}


								});


		}







		jQuery('#usn').click(function() {

						data = {
							action: 'user_stats_dismiss',
							type: 'POST',
							dataType: 'text',

						};

						jQuery.post(ajaxurl, data, function(response) {

							jQuery('#usn_message').remove();


						});



		});



		jQuery('#usn_e').click(function() {

				var test = jQuery ('#usn_message').length;
				if( test > 0 ) { return; }

						data = {
							action: 'user_stats_n_ajax',
							type: 'POST',
							dataType: 'text',

						};

						jQuery.post(ajaxurl, data, function(response) {
								console.log(response);
							jQuery('#user-stats-form').prepend(response);
							jQuery('#usn_a').click(function() {

																jQuery('#usn_message').remove();

																  });
						});



		});



/************************
*
* This is for the Running total in the Overview section
*
*************************/

var cell_values_noarticles = '';
var cell_values_totalview = '';
var cell_values_avgview = '';
var cell_values_dollarart = '';
var cell_values_totalart = '';
var cell_values_avgdollarview = '';
var cell_values_dollarthou = '';
var cell_values_avgdollarthou = '';


jQuery(".asz_overview_totals").change(function() {


//hide the running totals unless a box is ticked
if(jQuery('#statz_overview_table input[type=checkbox]:checked').length) {
	jQuery('#asz_running_total').show();
	//show date refresh
	//jQuery('#asz_refresh').show();

} else {
	jQuery('#asz_running_total').css('display','none');
	//show date refresh
	//jQuery('#asz_refresh').hide();

}




//http://stackoverflow.com/questions/18793954/using-javascript-jquery-how-can-i-get-values-from-each-cells-in-a-selected-row
    if(this.checked) {
				var values = [];
				var $header = jQuery(this).closest('table').find('thead th');
				var $cols   = jQuery(this).closest('tr').find('td');

				$header.each(function(idx,hdr) {
						var $curCol = $cols.eq(idx);
						var $labels = $curCol.find('label');
						var value;

						if($labels.length) {

								value = {};
								$labels.each(function(lblIdx, lbl) {
										value[jQuery(lbl).text().trim()] = jQuery(lbl).find('input').is(':checked');
								});

						} else {
						value = $curCol.text().trim();
						}
						//values.push( { name: jQuery(hdr).text(), value: value } );
						//values.push( { bleh: value } );
						values[jQuery(hdr).attr('id')] = value;
				});

				//console.log(values);

				cell_values_noarticles = +cell_values_noarticles + +values['asz_noart'];
				cell_values_totalview = +cell_values_totalview + +values['asz_totview'];
				cell_values_avgview = +cell_values_avgview + +values['asz_avgview'];
				cell_values_dollarart = +cell_values_dollarart + +values['asz_dolart'];
				cell_values_totalart = +cell_values_totalart + +values['asz_totdolart'];
				cell_values_avgdollarview = +cell_values_avgdollarview + +values['asz_avgdolv'];
				cell_values_dollarthou = +cell_values_dollarthou + +values['asz_dolthv'];
				cell_values_avgdollarthou = +cell_values_avgdollarthou + +values['asz_avgdolthv'];

				jQuery('#asz_running_noarticles').text(cell_values_noarticles);
				jQuery('#asz_running_totalview').text(cell_values_totalview);
				jQuery('#asz_running_avgview').text(Math.round(cell_values_avgview * 100) / 100);
				jQuery('#asz_running_dollarart').text(cell_values_dollarart);
				jQuery('#asz_running_totalart').text(cell_values_totalart);
				jQuery('#asz_running_avgdollarview').text(Math.round(cell_values_avgdollarview * 100) / 100);
				jQuery('#asz_running_dollarthou').text(cell_values_dollarthou);
				jQuery('#asz_running_avgdollarthou').text(Math.round(cell_values_avgdollarthou * 100) / 100);


				//jQuery('#asz_ind_' + values['asz_auth']).toggle();

				asz_author_posts(values['asz_auth']);

	}
    if(!this.checked) {

				var values = [];
				var $header = jQuery(this).closest('table').find('thead th');
				var $cols   = jQuery(this).closest('tr').find('td');

				$header.each(function(idx,hdr) {
						var $curCol = $cols.eq(idx);
						var $labels = $curCol.find('label');
						var value;

						if($labels.length) {

								value = {};
								$labels.each(function(lblIdx, lbl) {
										value[jQuery(lbl).text().trim()] = jQuery(lbl).find('input').is(':checked');
								});

						} else {
						value = $curCol.text().trim();
						}
						//values.push( { name: jQuery(hdr).text(), value: value } );
						values[jQuery(hdr).attr('id')] = value;

				});

				//console.log(JSON.stringify(values, null, 2));


				cell_values_noarticles = +cell_values_noarticles - +values['asz_noart'];
				cell_values_totalview = +cell_values_totalview - +values['asz_totview'];
				cell_values_avgview = +cell_values_avgview - +values['asz_avgview'];
				cell_values_dollarart = +cell_values_dollarart - +values['asz_dolart'];
				cell_values_totalart = +cell_values_totalart - +values['asz_totdolart'];
				cell_values_avgdollarview = +cell_values_avgdollarview - +values['asz_avgdolv'];
				cell_values_dollarthou = +cell_values_dollarthou - +values['asz_dolthv'];
				cell_values_avgdollarthou = +cell_values_avgdollarthou - +values['asz_avgdolthv'];


				jQuery('#asz_running_noarticles').text(cell_values_noarticles);
				jQuery('#asz_running_totalview').text(cell_values_totalview);
				jQuery('#asz_running_avgview').text(Math.round(cell_values_avgview * 100) / 100);
				jQuery('#asz_running_dollarart').text(cell_values_dollarart);
				jQuery('#asz_running_totalart').text(cell_values_totalart);
				jQuery('#asz_running_avgdollarview').text(Math.round(cell_values_avgdollarview * 100) / 100);
				jQuery('#asz_running_dollarthou').text(cell_values_dollarthou);
				jQuery('#asz_running_avgdollarthou').text(Math.round(cell_values_avgdollarthou * 100) / 100);

				//jQuery('#asz_ind_' + values['asz_auth']).toggle();

jQuery('.asz_ind_posts_'+values['asz_auth']).remove();
					var resort = true; // re-apply the current sort
					jQuery('#statz_single_author_articles_table').trigger("updateAll", [resort]);


	}
});


				jQuery('#asz_goto_totals').hide();

                var totalrow = jQuery('#asz_bottom_total').html();

                var rowcount = jQuery(".asz_profile_table tr").length;

                if(rowcount >= 12) {
                jQuery('.asz_profile_table thead').append('<tr class="asz_totals">'+ totalrow +'</tr>')
            	}
}); //end all


