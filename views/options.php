<script type="text/javascript">var __namespace = '<?php echo $namespace; ?>';</script>

<?php

if(is_admin()) {wp_enqueue_script( '{$this->namespace}-tablesorter', USERSTATS_URLPATH .'/js/jquery.tablesorter.min.js', array('jquery') );}

?>

<div class="wrap">

    <h2 id="asz_title"><?php echo $page_title; ?></h2>
    <span id="byap">by <a href="http://www.apinapress.com/" target="_blank">ApinaPress</a> | <a href="http://www.apinapress.com/user-stats/" target="_blank">Documentation</a> | <a href="http://www.apinapress.com/user-stats/#contact" target="_blank">Features, Bugs and Feedback</a></span> | <a href="http://wordpress.org/support/view/plugin-reviews/user-stats" target="_blank">Leave a review on WordPress.org</a></span>
    <span id="usn_e"><img src="<?php echo USERSTATS_URLPATH . '/images/euro.png';?> " /></span>

    <?php if( isset( $_GET['message'] ) ): ?>
        <div id="message" class="updated below-h2"><p><?php echo __('Options successfully updated!', $namespace); ?></p></div>
    <?php endif; ?>


<?php
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'author_stats';
?>



<h2 class="nav-tab-wrapper">
	<a href="?page=user-stats&tab=author_stats" class="nav-tab <?php echo $active_tab == 'author_stats' ? 'nav-tab-active' : ''; ?>"><?php echo __('User Stats', $namespace); ?></a>
	<a href="?page=user-stats&tab=settings" class="nav-tab  <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Settings', $namespace); ?></a>
	<!-- <a href="#" class="nav-tab">Tab #2</a> -->
</h2>




    <form action="" method="post" id="<?php echo $namespace; ?>-form">

        <?php wp_nonce_field( $namespace . "-update-options" ); ?>


 		<?php


// General functions



/********************
*
* Start the main if statement for the tabs
*
*********************/
if( $active_tab == 'author_stats' ) { ?>






<?php



//get total counts

function asz_get_total_counts($auth_id) {

		$single_author_posts = get_posts(
			array(
				'author'	=>	$auth_id,
				'post_type'   => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => '_statz_count'
					)
				)
			)
		);


		$auth_total_post_count = 0;

		foreach($single_author_posts as $single) {

			$temp_count = get_metadata('post', $single, '_statz_count');

			(int)$temp_count_str= $temp_count[0];

			if(!is_int($temp_count[0])) { $temp_count = 0; }
			$auth_total_post_count = $auth_total_post_count + $temp_count_str;
		}

		return $auth_total_post_count;

}

//get user roles
// good old Justin Tadlock http://wordpress.org/support/topic/get-a-users-role-by-user-id
function asz_user_role($asz_user_id) {

	$user = new WP_User( $asz_user_id );

	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role )
			return $role;
	}

}





//Overview section

function asz_overview() {

		$asz_options = get_option( '_user-stats--options' );

		$asz_users = asz_get_users();

		foreach($asz_users as $asz_overview) {


		//Remove the user if selected.
		$asz_noauthor = "over_noauthor_" . $asz_overview['user_nicename'];
		$get_check_cos_its_late = isset($asz_options[$asz_noauthor]);

		if (isset($noauthorcheck) != $get_check_cos_its_late) {  continue; }


		//var_dump($asz_overview);

		(int)$tempdollarperarticle = $asz_options['asz_dollar_per_article-' .$asz_overview['ID']];
		(int)$tempdollarperthousand = $asz_options['asz_dollar_per_thousand-' .$asz_overview['ID']];


		$overview_post_count = count_user_posts( $asz_overview['ID'] );
		$overview_total_count = asz_get_total_counts($asz_overview['ID']);

		if($overview_post_count == 0) { $overview_avg_views = 0; } else { $overview_avg_views = $overview_total_count / $overview_post_count; }

		//$overview_avg_views = round($overview_avg_views, 2, PHP_ROUND_HALF_UP);
		$overview_avg_views = asz_round($overview_avg_views);

		$overview_dollar_per_article = $tempdollarperarticle;
				if($overview_dollar_per_article == '') { $overview_dollar_per_article = 0; } else { $overview_dollar_per_article = $overview_dollar_per_article; }
		$overview_total_dollar_per_article = $overview_post_count * $tempdollarperarticle;
		if($overview_total_count == 0) { $overview_avg_dollar_per_view = 0; } else { $overview_avg_dollar_per_view = $overview_total_dollar_per_article / $overview_total_count; }
		//$overview_avg_dollar_per_view = round($overview_avg_dollar_per_view, 2, PHP_ROUND_HALF_UP);
		$overview_avg_dollar_per_view = asz_round($overview_avg_dollar_per_view);

		$overview_dollar_per_thousand = $tempdollarperthousand;
				if($overview_dollar_per_thousand == '') { $overview_dollar_per_thousand = 0; } else { $overview_dollar_per_thousand = $overview_dollar_per_thousand; }
		$overview_total_dollar_per_thousand = $overview_total_count * $overview_dollar_per_thousand;
		if($overview_total_count == 0) { $overview_avg_dollar_per_thousand = 0; } else { $overview_avg_dollar_per_thousand = $overview_total_dollar_per_thousand / $overview_total_count; }
		//$overview_avg_dollar_per_thousand = round($overview_total_dollar_per_thousand, 2, PHP_ROUND_HALF_UP)/1000;
		$overview_avg_dollar_per_thousand = asz_round($overview_total_dollar_per_thousand);



		echo "<tr>";
				echo "<td><input type='checkbox' class='asz_overview_totals' /></td>";

			echo "<td class='asz_author_name' id='" . $asz_overview['user_nicename'] . "'>" . $asz_overview['user_nicename'] . "</td>";
			if(isset($asz_options['over_role']) != 'over_role') {echo "<td>" . asz_user_role($asz_overview['ID']) . "</td>"; }

			if(isset($asz_options['over_noarticles']) != 'over_noarticles') {echo "<td>" . $overview_post_count . "</td>"; }
			if(isset($asz_options['over_tviews']) != 'over_tviews') {echo "<td>" . $overview_total_count . "</td>"; }
			if(isset($asz_options['over_aviews']) != 'over_aviews') {echo "<td>" . $overview_avg_views . "</td>"; }
			if(isset($asz_options['over_dollararticles']) != 'over_dollararticles') {echo "<td>" . $overview_dollar_per_article . "</td>"; }
			if(isset($asz_options['over_totaldollararticles']) != 'over_totaldollararticles') {echo "<td>" . $overview_total_dollar_per_article . "</td>"; }
			if(isset($asz_options['over_avgdollarview']) != 'over_avgdollarview') {echo "<td>" . $overview_avg_dollar_per_view . "</td>"; }
			if(isset($asz_options['over_dollarthousandview']) != 'over_dollarthousandview') {echo "<td>" . $overview_dollar_per_thousand . "</td>"; }
			if(isset($asz_options['over_avgdollarthousandview']) != 'over_avgdollarthousandview') {echo "<td>" . $overview_avg_dollar_per_thousand . "</td>"; }
		echo "</tr>";


	}

	// This will be the running total
		echo "<tfoot><tr id='asz_running_total'>";
				echo "<td></td>";

			echo "<td class='asz_author_name' id=''>TOTAL</td>";
			if(isset($asz_options['over_role']) != 'over_role') {echo '<td id="asz_running_role"></td>'; }
			if(isset($asz_options['over_noarticles']) != 'over_noarticles') {echo '<td id="asz_running_noarticles"></td>'; }
			if(isset($asz_options['over_tviews']) != 'over_tviews') {echo '<td id="asz_running_totalview"></td>'; }
			if(isset($asz_options['over_aviews']) != 'over_aviews') {echo '<td id="asz_running_avgview"></td>'; }
			if(isset($asz_options['over_dollararticles']) != 'over_dollararticles') {echo '<td id="asz_running_dollarart"></td>'; }
			if(isset($asz_options['over_totaldollararticles']) != 'over_totaldollararticles') {echo '<td id="asz_running_totalart"></td>'; }
			if(isset($asz_options['over_avgdollarview']) != 'over_avgdollarview') {echo '<td id="asz_running_avgdollarview"></td>'; }
			if(isset($asz_options['over_dollarthousandview']) != 'over_dollarthousandview') {echo '<td id="asz_running_dollarthou"></td>'; }
			if(isset($asz_options['over_avgdollarthousandview']) != 'over_avgdollarthousandview') {echo '<td id="asz_running_avgdollarthou"></td>'; }
		echo "</tr></tfoot>";

}
// end of overview section



$asz_users = asz_get_users();

//var_dump($asz_users);


	foreach($asz_users as $userz) {


		$user = new WP_User( $userz['ID'] );

		if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
			foreach ( $user->roles as $role )
		$asz_author_role = $role;
		}

		$asz_author_id = $userz['ID'];
		$asz_author_name = $userz['user_nicename'];

		$user_post_count = count_user_posts( $asz_author_id );

		$posts[$asz_author_name] = get_posts(
			array(
				'author'	=>	$asz_author_id,
				'post_type'   => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				//'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => '_statz_count'
					)
				)
			)
		);

		$posts[$asz_author_name]['asz_user_post_count'] = $user_post_count;
		$posts[$asz_author_name]['actual_author_name'] = $asz_author_name;

	}

	//var_dump($posts);

?>


<?php


$asz_options = get_option( '_user-stats--options' );

$asz_currency = $asz_options['asz_display_currency'];

if ($asz_currency == '') { $asz_currency = "$"; }

?>

<?php

$usn = get_option('user_stats_nag', true);
if ( $usn == 1 ) {} else {

	$curr_date = date('Y-m-d');

	$diff = abs(strtotime($curr_date) - strtotime($usn));

	if(isset($years) && isset($months)) {
		$us_daterange = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	}


	if(isset($us_daterange) >= 30) {
		user_stats_n();
	}
}
?>


            <br />
            <br />

			<h2><?php _e( "Overview", $namespace ) ?></h2>

<form>
<label><em>Enter dates (leave both blank for all) and then tick the box for each user you wish to see stats on.</em></label><br /><br>
<input id="asz_start_date" type="text" class="custom_date" placeholder="Start Date" /><input id="asz_end_date" type="text" class="custom_date" placeholder="End Date" /><label id="asz_refresh">REFRESH</label>
</form>
<br />

            		<div id="statz_overview">
					<table id="statz_overview_table" class="widefat asz_table tablesorter tablesorter-blue">
						<thead>
							<tr>
                             	<th class="" title="<?php echo __('Click to combine stats');?>" id="asz_comb"><img src="<?php echo USERSTATS_URLPATH . '/images/combine.png'; ?>" /></th>

								<th class="" id="asz_auth"><?php if($asz_options['over_author_name'] == '') { echo __('Author', $namespace); } else { echo $asz_options['over_author_name']; } ?></th>

								<?php if(isset($asz_options['over_role']) != 'over_role') { ?><th class="" id="asz_role"><?php if($asz_options['over_role_name'] == '') { echo __('Role', $namespace);  } else { echo $asz_options['over_role_name']; } ?></th> <?php  } ?>

								<?php if(isset($asz_options['over_noarticles']) != 'over_noarticles') { ?><th id="asz_noart"><?php if($asz_options['over_no_articles_name'] == '') { echo __('# Articles', $namespace );  } else { echo $asz_options['over_no_articles_name']; } ?></th> <?php  } ?>

                                <?php if(isset($asz_options['over_tviews']) != 'over_tviews') { ?><th id="asz_totview"><?php if($asz_options['over_total_views_name'] == '') { echo __('Total Views', $namespace );  } else { echo $asz_options['over_total_views_name']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['over_aviews']) != 'over_aviews') { ?><th id="asz_avgview"><?php if($asz_options['over_avg_views_name'] == '') { echo __('Avg. Views', $namespace );  } else { echo $asz_options['over_avg_views_name']; } ?></th> <?php } ?>

                                <?php if(isset($asz_options['over_dollararticles']) != 'over_dollararticles') { ?><th id="asz_dolart">
                                <?php if($asz_options['over_dol_article_name'] == '') { echo $asz_currency . __('/Article', $namespace );  } else { echo $asz_options['over_dol_article_name']; } ?></th> <?php } ?>

                                <?php if(isset($asz_options['over_totaldollararticles']) != 'over_totaldollararticles') { ?><th id="asz_totdolart"><?php if($asz_options['over_total_article_name'] == '') { echo __('Total ', $namespace ) . $asz_currency . __(' for Articles', $namespace );  } else { echo $asz_options['over_total_article_name']; } ?></th> <?php } ?>

                                <?php if(isset($asz_options['over_avgdollarview']) != 'over_avgdollarview') { ?><th id="asz_avgdolv"><?php if($asz_options['over_dol_view_name'] == '') { echo __('Avg.', $namespace ); ?> <?php echo $asz_currency . __('/View', $namespace );  } else { echo $asz_options['over_dol_view_name']; } ?></th> <?php } ?>

                                <?php if(isset($asz_options['over_dollarthousandview']) != 'over_dollarthousandview') { ?><th id="asz_dolthv"><?php if($asz_options['over_dole_thou_name'] == '') { echo $asz_currency . __('/1000 Views', $namespace );  } else { echo $asz_options['over_dole_thou_name']; } ?></th> <?php } ?>

                                <?php if(isset($asz_options['over_avgdollarthousandview']) != 'over_avgdollarthousandview') { ?><th id="asz_avgdolthv"><?php if($asz_options['over_dol_avg_thou_name'] == '') { echo __('Avg.', $namespace ) . $asz_currency . __('/1000 Views', $namespace );  } else { echo $asz_options['over_dol_avg_thou_name']; } ?></th> <?php } ?>


							</tr>
						</thead>
						<tbody>
                        <?php echo asz_overview(); ?>
						</tbody>
					</table>
                    </div>

                    <br />
                    <br />
                    <br />

                    <h2><?php _e( "Posts", $namespace ) ?><img id="user_stats_loading" src="<?php echo USERSTATS_URLPATH . '/images/ajax-loader.gif';?>" /></h2>


                    <br />


            		<div id="statz_single_author_articles" class="stats_single_author">
					<table id="statz_single_author_articles_table"class="asz_table widefat tablesorter tablesorter-blue">
						<thead>
							<tr>
                            	<?php if(isset($asz_options['author_author']) != 'author_author') { ?><th class=""><?php if($asz_options['over_author_name2'] == '') { echo __('Author', $namespace); } else { echo $asz_options['over_author_name2']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['author_article']) != 'author_article') { ?><th class=""><?php if($asz_options['over_article_name'] == '') { echo __('Article', $namespace); } else { echo $asz_options['over_article_name']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['author_date']) != 'author_date') { ?><th><?php if($asz_options['over_pubdate_name'] == '') { echo __('Date Published', $namespace); } else { echo $asz_options['over_pubdate_name']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['author_cat']) != 'author_cat') { ?><th><?php if($asz_options['over_cat_name'] == '') { echo __('Category', $namespace); } else { echo $asz_options['over_cat_name']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['author_views']) != 'author_views') { ?><th><?php if($asz_options['over_views_name'] == '') { echo __('Views', $namespace); } else { echo $asz_options['over_views_name']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['author_dollarviews']) != 'author_dollarviews') { ?><th><?php if($asz_options['over_dol_views_name'] == '') { echo $asz_currency . __('/View', $namespace); } else { echo $asz_options['over_dol_views_name']; } ?></th> <?php } ?>

								<?php if(isset($asz_options['author_dollarthousand']) != 'author_dollarthousand') { ?><th><?php if($asz_options['over_dol_thou_views_name'] == '') { echo $asz_currency . __('/1000', $namespace); } else { echo $asz_options['over_dol_thou_views_name']; } ?></th> <?php } ?>
							</tr>
						</thead>
						<tbody>
                        <?php //echo asz_ind_show_posts(); ?>
						</tbody>
					</table>
                    </div>

			<?php } else {



/**
 * *******************************************
 *
 *
 * This is the actual settings section
 *
 * *******************************************
 */

				$asz_settings_users = asz_get_users();

?>

					<p class="submit user_stats_submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php _e( "Save Changes", $namespace ) ?>" />
					</p>


<div class="userstats_settings_style">
				<h2><?php echo __( "General Settings", $namespace ); ?></h2>
				<table>
					<tr>
						<td class='asz_option_label'><?php echo __('Currency Symbol (default is $)', $namespace ); ?></td>
						<td><input class='small-text' type='text' value='<?php echo $this->get_option( 'asz_display_currency' ); ?>' name='data[asz_display_currency]'></td>
					</tr>
					<tr>
			            <td class='asz_option_label'><?php echo __('Enable stats in users Profile?') ?></td>
			            <td><input type="checkbox" name="data[profile_enable]" value="profile_enable" <?php $profile_enable = $this->get_option( 'profile_enable' ); if ($profile_enable == 'profile_enable') { echo 'checked="checked"'; } ?></td>
		            </tr>
				</table>
</div>

<div class="userstats_settings_style">


<h2><?php echo __('Add/remove Columns', $namespace ); ?></h2>

<div id="asz_addremove_admin" class="asz_addremove_options">
		<p><?php echo __('Tick the box to <strong>remove</strong> a column from the admin tables.', $namespace ); ?></p>
		<table>
		<th><?php echo __('Overview Columns', $namespace ); ?></th>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Role', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_role]" value="over_role" <?php $over_role = $this->get_option( 'over_role' ); if ($over_role == 'over_role') { echo 'checked="checked"'; } ?>/></td>
		</tr>

		<tr>
		<td class='asz_option_label'><label><?php echo __('# Articles', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_noarticles]" value="over_noarticles" <?php $over_noarticles = $this->get_option( 'over_noarticles' ); if ($over_noarticles == 'over_noarticles') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Total Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_tviews]" value="over_tviews" <?php $over_tviews = $this->get_option( 'over_tviews' ); if ($over_tviews == 'over_tviews') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Avg. Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_aviews]" value="over_aviews" <?php $over_aviews = $this->get_option( 'over_aviews' ); if ($over_aviews == 'over_aviews') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/Article', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_dollararticles]" value="over_dollararticles" <?php $over_dollararticles = $this->get_option( 'over_dollararticles' ); if ($over_dollararticles == 'over_dollararticles') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Total $ for Articles', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_totaldollararticles]" value="over_totaldollararticles" <?php $over_totaldollararticles = $this->get_option( 'over_totaldollararticles' ); if ($over_totaldollararticles == 'over_totaldollararticles') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Avg. $/View', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_avgdollarview]" value="over_avgdollarview" <?php $over_avgdollarview = $this->get_option( 'over_avgdollarview' ); if ($over_avgdollarview == 'over_avgdollarview') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/1000 Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_dollarthousandview]" value="over_dollarthousandview" <?php $over_dollarthousandview = $this->get_option( 'over_dollarthousandview' ); if ($over_dollarthousandview == 'over_dollarthousandview') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Avg. $/1000 Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_avgdollarthousandview]" value="over_avgdollarthousandview" <?php $over_avgdollarthousandview = $this->get_option( 'over_avgdollarthousandview' ); if ($over_avgdollarthousandview == 'over_avgdollarthousandview') { echo 'checked="checked"'; } ?>/></td>
		</tr>

		<tr><td></td></tr>
		<tr><td></td></tr>
		<tr><td></td></tr>
		<tr><td></td></tr>

		<th><?php echo __('Posts'); ?></th>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Article', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_article]" value="author_article" <?php $author_article = $this->get_option( 'author_article' ); if ($author_article == 'author_article') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Date Published', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_date]" value="author_date" <?php $author_date = $this->get_option( 'author_date' ); if ($author_date == 'author_date') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Category', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_cat]" value="author_cat" <?php $author_cat = $this->get_option( 'author_cat' ); if ($author_cat == 'author_cat') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_views]" value="author_views" <?php $author_views = $this->get_option( 'author_views' ); if ($author_views == 'author_views') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td><label><?php echo __('$/Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_dollarviews]" value="author_dollarviews" <?php $author_dollarviews = $this->get_option( 'author_dollarviews' ); if ($author_dollarviews == 'author_dollarviews') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/1000', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_dollarthousand]" value="author_dollarthousand" <?php $author_dollarthousand = $this->get_option( 'author_dollarthousand' ); if ($author_dollarthousand == 'author_dollarthousand') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		</table>
</div><!-- end addremoveadmin-->

<div id="asz_addremove_front" class="asz_addremove_options">
		<p><?php echo __('Tick the box to <strong>remove</strong> a column from the <strong>Front</strong> facing tables and the <strong>Profile</strong> tables.', $namespace ); ?></p>
		<table>

		<th><?php echo __('Posts'); ?></th>
	<!--
		<tr>
		<td class='asz_option_label'><label><?php //echo __('Article', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_article_front]" value="author_article_front" <?php //$author_article = $this->get_option( 'author_article_front' ); if ($author_article == 'author_article_front') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php //echo __('Date Published', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_date_front]" value="author_date_front" <?php //$author_date_front = $this->get_option( 'author_date_front' ); if ($author_date_front == 'author_date_front') { echo 'checked="checked"'; } ?>/></td>
		</tr>
	-->
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/Article', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_dollararticles_front]" value="over_dollararticles_front" <?php $over_dollararticles_front = $this->get_option( 'over_dollararticles_front' ); if ($over_dollararticles_front == 'over_dollararticles_front') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Views', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_views_front]" value="author_views_front" <?php $author_views_front = $this->get_option( 'author_views_front' ); if ($author_views_front == 'author_views_front') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Article Earnings', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[over_articleearnings_front]" value="over_articleearnings_front" <?php $over_articleearnings_front = $this->get_option( 'over_articleearnings_front' ); if ($over_articleearnings_front == 'over_articleearnings_front') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/1000', $namespace ); ?></label></td>
		<td><input type="checkbox" name="data[author_dollarthousand_front]" value="author_dollarthousand_front" <?php $author_dollarthousand_front = $this->get_option( 'author_dollarthousand_front' ); if ($author_dollarthousand_front == 'author_dollarthousand_front') { echo 'checked="checked"'; } ?>/></td>
		</tr>
		</table>
</div><!-- end addremovefront-->

</div>







<div class="userstats_settings_style">


<h2><?php echo __('Rename Columns', $namespace ); ?></h2>

<div id="asz_rename_admin" class="asz_addremove_options">
		<h3><?php echo __('Admin Tables', $namespace ); ?></h3>

		<table>
		<th colspan=2><?php echo __('Overview Columns', $namespace ); ?></th>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Author', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_author_name' ); ?>' name='data[over_author_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Role', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_role_name' ); ?>' name='data[over_role_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('# Articles', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_no_articles_name' ); ?>' name='data[over_no_articles_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Total Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_total_views_name' ); ?>' name='data[over_total_views_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Avg. Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_avg_views_name' ); ?>' name='data[over_avg_views_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/Article', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_article_name' ); ?>' name='data[over_dol_article_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Total $ for Articles', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_total_article_name' ); ?>' name='data[over_total_article_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Avg. $/View', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_view_name' ); ?>' name='data[over_dol_view_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/1000 Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dole_thou_name' ); ?>' name='data[over_dole_thou_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Avg. $/1000 Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_avg_thou_name' ); ?>' name='data[over_dol_avg_thou_name]'></td>
		</tr>

		<tr><td></td></tr>
		<tr><td></td></tr>
		<tr><td></td></tr>
		<tr><td></td></tr>

		<th colspan="2"><?php echo __('Posts'); ?></th>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Author', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_author_name2' ); ?>' name='data[over_author_name2]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Article', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_article_name' ); ?>' name='data[over_article_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Date Published', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_pubdate_name' ); ?>' name='data[over_pubdate_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Category', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_cat_name' ); ?>' name='data[over_cat_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_views_name' ); ?>' name='data[over_views_name]'></td>
		</tr>
		<tr>
		<td><label><?php echo __('$/Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_views_name' ); ?>' name='data[over_dol_views_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/1000', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_thou_views_name' ); ?>' name='data[over_dol_thou_views_name]'></td>
		</tr>
		</table>
</div><!-- end renameadmin-->

<div id="asz_rename_front" class="asz_addremove_options">
		<h3><?php echo __('Shortcode and Profile Tables', $namespace ); ?></h3>
		<p>&nbsp;</p>
		<table>

		<tr>
		<td class='asz_option_label'><label><?php echo __('Article', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_article_front_name' ); ?>' name='data[over_article_front_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Date Published', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_pubdate_front_name' ); ?>' name='data[over_pubdate_front_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/Article', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_article_front_name' ); ?>' name='data[over_dol_article_front_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Views', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_views_front_name' ); ?>' name='data[over_views_front_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('Article Earnings', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_article_earnings_front_name' ); ?>' name='data[over_article_earnings_front_name]'></td>
		</tr>
		<tr>
		<td class='asz_option_label'><label><?php echo __('$/1000', $namespace ); ?></label></td>
		<td><input class='regular-text' type='text' value='<?php echo $this->get_option( 'over_dol_thou_front_name' ); ?>' name='data[over_dol_thou_front_name]'></td>
		</tr>
		</table>
</div><!-- end renamefront-->

</div>




<div class="userstats_settings_style">

			<div id="asz_price_per_article_option" class="asz_price_per_options">

				<h2><?php echo __( "Price per article", $namespace ); ?></h2>

				<table>
                <?php
				foreach ($asz_settings_users as $users) {

					$da_author_article_cost = get_user_meta( $users['ID'], 'asz_dollar_per_article', true );
				?>
					<tr>
					<td class='asz_option_label'><?php echo $users['user_nicename']; ?></td>
					<td><input class='small-text' type='text' value='<?php echo $this->get_option( "asz_dollar_per_article-".$users["ID"] ); ?>' name='data[asz_dollar_per_article-<?php echo $users['ID']; ?>]'></td>
					</tr>
				<?php
				}
				?>
				</table>

			</div>

			<div id="asz_price_per_thousand_option" class="asz_price_per_options">

				<h2><?php echo __( "Price per 1000 views", $namespace ); ?></h2>

				<table>
                <?php
				foreach ($asz_settings_users as $users) {

					$da_author_thousand_cost = get_user_meta( $users['ID'], 'asz_dollar_per_thousand', true );
				?>
					<tr>
					<td class='asz_option_label'><?php echo $users['user_nicename']; ?></td>
					<td><input class='small-text' type='text' value='<?php echo $this->get_option( 'asz_dollar_per_thousand-'.$users['ID'] ); ?>' name='data[asz_dollar_per_thousand-<?php echo $users['ID']; ?>]'></td>
					</tr>
				<?php
				}
				?>
				</table>
			</div>

</div>


<div class="userstats_settings_style">

            <h2><?php echo __('Add/remove Users', $namespace ); ?></h2>
            <p><?php echo __('Tick the box to <strong>remove</strong> a user from the display', $namespace ); ?></p>

            <table>
            <?php
				foreach ($asz_settings_users as $users) {

				$da_author_thousand_cost = get_user_meta( $users['ID'], 'asz_dollar_per_thousand', true );
            ?>

            <tr>
            <td class='asz_option_label'><?php echo $users['user_nicename']; ?></td>

            <td><input type="checkbox" name="data[over_noauthor_<?php echo $users['user_nicename']; ?>]" value="over_noauthor_<?php echo $users['user_nicename']; ?>"<?php $asz_this_author = "over_noauthor_" . $users['user_nicename']; $over_noauthor = $this->get_option( $asz_this_author ); if ($over_noauthor == $asz_this_author) { echo 'checked="checked"'; } ?>/></td>


            </tr>
            <?php
				}
				?>
            </table>
</div>






            		<!-- example setting
					<p>
					<label><input type="text" name="data[option_1]" value="<?php //echo $this->get_option( 'option_1' ); ?>" /> This is an example of an option.</label>
					</p>
                    -->

					<p class="submit user_stats_submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php _e( "Save Changes", $namespace ) ?>" />
					</p>


			<?php } // end if/else

			?>

    </form>






</div>

