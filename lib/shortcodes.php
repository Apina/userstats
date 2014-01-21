<?php
$asz_options = get_option('_user-stats--options');

if(isset($asz_options['profile_enable']) == 'profile_enable') {
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );
}

add_shortcode( 'userstats', 'my_show_extra_profile_fields');

function my_show_extra_profile_fields( $user ) {

$asz_options = get_option( '_user-stats--options' );

/*
$asz_options['author_article_front']
$asz_options['author_date_front']
$asz_options['author_views_front']
$asz_options['over_dollararticles_front']
$asz_options['over_articleearnings_front']
$asz_options['author_dollarthousand_front']
*/


        if(is_user_logged_in()) {

                $current_user = wp_get_current_user();
                $profile_posts = asz_ind_get_posts($current_user->ID, $startdate ='', $enddate='');
        }
        else {
            return false;
        }

        if(!is_admin()) {

        wp_enqueue_style( "user-stats-front" );
        wp_enqueue_script( "user-stats-tablesorter" );
        wp_enqueue_script( "user-stats-tablesorter-widget" );
        wp_enqueue_script( "user-stats-frontjs" );
        }
        else {
        wp_enqueue_script( "user-stats-admin" );
        wp_enqueue_script( "user-stats-tablesorter" );
        wp_enqueue_script( "user-stats-tablesorter-widget" );
        wp_enqueue_style( "user-stats-admin" );
        }

        ?>

        <div>
            <?php
            if(!is_admin()) {} else {
            ?>
            <h3><?php echo __('User Stats'); ?></h3>
            <?php
            }
            ?>
            <span id="asz_goto_totals"><a href="#asz_bottom_total">Go to totals</a></span>
            <table class="asz_profile_table asz_table widefat tablesorter tablesorter-blue">
            <thead>
                <tr class="tablesorter-headerRow">
                	<?php if(isset($asz_options['author_article_front']) && $asz_options['author_article_front'] != 'author_article_front') { ?>
                    <th class="tablesorter-header"><?php if($asz_options['over_article_front_name'] == '') { echo __('Article', $namespace); } else { echo $asz_options['over_article_front_name']; } ?></th>
                	<?php } ?>

                	<?php if(isset($asz_options['author_date_front']) && $asz_options['author_date_front'] != 'author_date_front') {  ?>
                    <th class="tablesorter-header"><?php if($asz_options['over_pubdate_front_name'] == '') { echo __('Date Published', $namespace); } else { echo $asz_options['over_pubdate_front_name']; } ?></th>
                	<?php } ?>

                	<?php if(isset($asz_options['author_views_front']) && $asz_options['author_views_front'] != 'author_views_front') {  ?>
                    <th class="tablesorter-header"><?php if($asz_options['over_dol_article_front_name'] == '') { echo __('Views', $namespace); } else { echo $asz_options['over_dol_article_front_name']; } ?></th>
                	<?php } ?>

                	<?php if(isset($asz_options['over_dollararticles_front']) && $asz_options['over_dollararticles_front'] != 'over_dollararticles_front') {  ?>
                    <th class="tablesorter-header"><?php if($asz_options['over_views_front_name'] == '') { echo __('Article Earnings', $namespace); } else { echo $asz_options['over_views_front_name']; } ?></th>
                	<?php } ?>

                	<?php if(isset($asz_options['over_articleearnings_front']) && $asz_options['over_articleearnings_front'] != 'over_articleearnings_front') {  ?>
                    <th class="tablesorter-header"><?php if($asz_options['over_article_earnings_front_name'] == '') { echo __('$/views', $namespace); } else { echo $asz_options['over_article_earnings_front_name']; } ?></th>
                	<?php } ?>

                	<?php if(isset($asz_options['author_dollarthousand_front']) && $asz_options['author_dollarthousand_front'] != 'author_dollarthousand_front') {  ?>
                    <th class="tablesorter-header"><?php if($asz_options['over_dol_thou_front_name'] == '') { echo __('$/1000 views', $namespace); } else { echo $asz_options['over_dol_thou_front_name']; } ?></th>
                    <?php } ?>
                </tr>
            </thead>
        <?php

        if(empty($profile_posts)) {
            echo "<tr><td colspan=5>No posts found.</td></tr>";
        }

        $total_views 					= 0;
        $total_dollar_per_art 			= 0;
        $total_dollar_per_art_views 	= 0;
        $total_dollar_per_tho 			= 0;

        foreach($profile_posts as $the_posts) {

            $da_author = $the_posts->post_author;

            (int)$da_author_article_cost = $asz_options['asz_dollar_per_article-' .$da_author];
            (int)$da_author_thousand_cost = $asz_options['asz_dollar_per_thousand-' .$da_author];


            $da_date = $the_posts->post_date;
            $da_date_format = get_option('date_format');
            $da_date = date($da_date_format, strtotime($da_date));

            $da_cat = get_the_category( $the_posts->ID );
            $cat_string = '';
            foreach ( $da_cat as $dacat) {
                $cat_string .= " " . $dacat->name;
            }
            $asz_post_meta = get_post_meta( $the_posts->ID, '_statz_count', true );
                if($asz_post_meta == '') { $asz_post_meta = 0; }

            //$asz_dollar_per_views = $da_author_article_cost / $asz_post_meta;
            if($asz_post_meta == 0 || $da_author_article_cost == 0) { $asz_dollar_per_views = 0; } else { $asz_dollar_per_views = $da_author_article_cost / $asz_post_meta; }


            //$asz_dollar_per_views = round($asz_dollar_per_views, 2, PHP_ROUND_HALF_UP);
            $asz_dollar_per_views = asz_round($asz_dollar_per_views);

            $asz_dollar_per_thousand_views = $da_author_thousand_cost / 1000;
            $asz_dollar_per_thousand_views = $asz_dollar_per_thousand_views * $asz_post_meta;
                //$asz_dollar_per_thousand_views = round($asz_dollar_per_thousand_views, 2, PHP_ROUND_HALF_UP);
                $asz_dollar_per_thousand_views = asz_round($asz_dollar_per_thousand_views);

        $total_views 					= $total_views + $asz_post_meta;
        $total_dollar_per_art_views 	= $total_dollar_per_art_views + $asz_dollar_per_views;
        $total_dollar_per_art 			= $total_dollar_per_art + $da_author_article_cost;
        $total_dollar_per_tho 			= $total_dollar_per_tho + $asz_dollar_per_thousand_views;
        ?>


        <tr>

        <td><?php if(isset($asz_options['author_article']) != 'author_article') {echo "<a href='" . $the_posts->guid . "' target='_blank'>" . $the_posts->post_title . "</a>"; } ?></td>

        <td><?php if(isset($asz_options['author_date']) != 'author_date') {echo $da_date; } ?></td>

    	<?php if(isset($asz_options['author_views_front']) != 'author_views_front') {  ?>
        <td><?php if(isset($asz_options['author_views']) != 'author_views') {echo $asz_post_meta; } ?></td>
    	<?php } ?>

    	<?php if(isset($asz_options['over_dollararticles_front']) != 'over_dollararticles_front') {  ?>
        <td><?php if(isset($da_author_article_cost)) {echo $da_author_article_cost; } ?></td>
    	<?php } ?>

    	<?php if(isset($asz_options['over_articleearnings_front']) != 'over_articleearnings_front') {  ?>
        <td><?php if(isset($asz_options['author_dollarviews']) != 'author_dollarviews') {echo $asz_dollar_per_views; } ?></td>
    	<?php } ?>

    	<?php if(isset($asz_options['author_dollarthousand_front']) != 'author_dollarthousand_front') {  ?>
        <td><?php if(isset($asz_options['author_dollarthousand']) != 'author_dollarthousand') {echo $asz_dollar_per_thousand_views; } ?></td>
        <?php } ?>

        </tr>
        <?php
        }
        ?>

    <tfoot>
        <tr id="asz_bottom_total" class="asz_totals">
        	<td colspan="2"><?php echo __('Totals'); ?></td>
        <?php if(isset($asz_options['author_views_front']) != 'author_views_front') {  ?>
        <td><?php echo $total_views; ?></td>
    	<?php } ?>

    	<?php if(isset($asz_options['over_dollararticles_front']) != 'over_dollararticles_front') {  ?>
        <td><?php echo $total_dollar_per_art; ?></td>
    	<?php } ?>

    	<?php if(isset($asz_options['over_articleearnings_front']) != 'over_articleearnings_front') {  ?>
        <td><?php echo $total_dollar_per_art_views; ?></td>
    	<?php } ?>

    	<?php if(isset($asz_options['author_dollarthousand_front']) != 'author_dollarthousand_front') {  ?>
        <td><?php echo $total_dollar_per_tho; ?></td>
        <?php } ?>

        </tr>
    </tfoot>

        </table>
    </div>

<?php
}