<?php
/*
Plugin Name: User Stats
Plugin URI: http://www.apinapress.com/user-stats
Description: Get post count, post view count, cost per article, cost per 1000 views and more stats for your Users.
Version: 1.0.7
Author: Dean Robinson
Author URI: http://www.apinapress.com
License: GPL3

Plugin Template courtesy of Dave Shepard  (email : dave@kynatro.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Include constants file
require_once( dirname( __FILE__ ) . '/lib/constants.php' );

class UserStats {
    var $namespace = "user-stats";
    var $friendly_name = "User Stats";
    var $version = "1.0.7";

    // Default plugin options
    var $defaults = array(
        'nagmessage' => true
    );

    /**
     * Instantiation construction
     *
     * @uses add_action()
     * @uses UserStats::wp_register_scripts()
     * @uses UserStats::wp_register_styles()
     */
    function __construct() {
        // Name of the option_value to store plugin options in
        $this->option_name = '_' . $this->namespace . '--options';

        // Load all library files used by this plugin
        $libs = glob( USERSTATS_DIRNAME . '/lib/*.php' );
        foreach( $libs as $lib ) {
            include_once( $lib );
        }

        /**
         * Make this plugin available for translation.
         * Translations can be added to the /languages/ directory.
         */
		$locale = apply_filters( 'plugin_locale', get_locale(), 'user-stats' );
		load_textdomain( 'user-stats', WP_LANG_DIR.'/languages/user-stats-'.$locale.'.mo' );
		load_plugin_textdomain( 'user-stats', false, dirname( plugin_basename( __FILE__ ) ).'/languages' );


		// Add all action, filter and shortcode hooks
		$this->_add_hooks();
    }

    /**
     * Add in various hooks
     *
     * Place all add_action, add_filter, add_shortcode hook-ins here
     */
    private function _add_hooks() {
        // Options page for configuration
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
        // Route requests for form processing
        add_action( 'init', array( &$this, 'route' ) );

        // Add a settings link next to the "Deactivate" link on the plugin listing page
        add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );

        // Register all JavaScripts for this plugin
        add_action( 'init', array( &$this, 'wp_register_scripts' ), 1 );
        // Register all Stylesheets for this plugin
        add_action( 'init', array( &$this, 'wp_register_styles' ), 1 );
    }

    /**
     * Process update page form submissions
     *
     * @uses UserStats::sanitize()
     * @uses wp_redirect()
     * @uses wp_verify_nonce()
     */
    private function _admin_options_update() {
        // Verify submission for processing using wp_nonce


        if( wp_verify_nonce( $_REQUEST['_wpnonce'], "{$this->namespace}-update-options" ) ) {
            $data = array();
            /**
             * Loop through each POSTed value and sanitize it to protect against malicious code. Please
             * note that rich text (or full HTML fields) should not be processed by this function and
             * dealt with directly.
             */

            foreach( $_POST['data'] as $key => $val ) {
                $data[$key] = $this->_sanitize( $val );
            }

            /**
             * Place your options processing and storage code here
             */

            // Update the options value with the data submitted
            update_option( $this->option_name, $data );

            // Redirect back to the options page with the message flag to show the saved message
            wp_safe_redirect( $_REQUEST['_wp_http_referer'] . '&message=1' );
            exit;
        }
    }

    /**
     * Sanitize data
     *
     * @param mixed $str The data to be sanitized
     *
     * @uses wp_kses()
     *
     * @return mixed The sanitized version of the data
     */
    private function _sanitize( $str ) {
        if ( !function_exists( 'wp_kses' ) ) {
            require_once( ABSPATH . 'wp-includes/kses.php' );
        }
        global $allowedposttags;
        global $allowedprotocols;

        if ( is_string( $str ) ) {
            $str = wp_kses( $str, $allowedposttags, $allowedprotocols );
        } elseif( is_array( $str ) ) {
            $arr = array();
            foreach( (array) $str as $key => $val ) {
                $arr[$key] = $this->_sanitize( $val );
            }
            $str = $arr;
        }

        return $str;
    }

    /**
     * Hook into register_activation_hook action
     *
     * Put code here that needs to happen when your plugin is first activated (database
     * creation, permalink additions, etc.)
     */
    static function activate() {
        // Do activation actions

		global $wpdb;

		$asz_table_name = $wpdb->prefix . "userstats_count";

		$sql = "CREATE TABLE $asz_table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  post_id int NOT NULL,
		  count bigint NOT NULL,
		  UNIQUE KEY id (id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


add_option( "user_stats_nag", date("Y-m-d") );

    }

    /**
     * Define the admin menu options for this plugin
     *
     * @uses add_action()
     * @uses add_options_page()
     */
    function admin_menu() {
        //$page_hook = add_options_page( $this->friendly_name, $this->friendly_name, 'administrator', $this->namespace, array( &$this, 'admin_options_page' ) );
		$page_hook = add_submenu_page( 'users.php', $this->friendly_name, $this->friendly_name, 'administrator', $this->namespace, array( &$this, 'admin_options_page' ));

        // Add print scripts and styles action based off the option page hook
        add_action( 'admin_print_scripts-' . $page_hook, array( &$this, 'admin_print_scripts' ) );
        add_action( 'admin_print_styles-' . $page_hook, array( &$this, 'admin_print_styles' ) );
    }


    /**
     * The admin section options page rendering method
     *
     * @uses current_user_can()
     * @uses wp_die()
     */
    function admin_options_page() {
        if( !current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page' );
        }

        $page_title = $this->friendly_name . '';
        $namespace = $this->namespace;

        include( USERSTATS_DIRNAME . "/views/options.php" );
    }

    /**
     * Load JavaScript for the admin options page
     *
     * @uses wp_enqueue_script()
     */
    function admin_print_scripts() {
        wp_enqueue_script( "{$this->namespace}-admin" );
		wp_enqueue_script('jquery-ui-datepicker');
    }

    /**
     * Load Stylesheet for the admin options page
     *
     * @uses wp_enqueue_style()
     */
    function admin_print_styles() {
        wp_enqueue_style( "{$this->namespace}-admin" );
		wp_enqueue_style('datepicker-smoothness', USERSTATS_URLPATH . '/css/datepicker-smoothness.css');

    }

    /**
     * Hook into register_deactivation_hook action
     *
     * Put code here that needs to happen when your plugin is deactivated
     */
    static function deactivate() {
        // Do deactivation actions
    }

    /**
     * Retrieve the stored plugin option or the default if no user specified value is defined
     *
     * @param string $option_name The name of the TrialAccount option you wish to retrieve
     *
     * @uses get_option()
     *
     * @return mixed Returns the option value or false(boolean) if the option is not found
     */
    function get_option( $option_name ) {
        // Load option values if they haven't been loaded already
        if( !isset( $this->options ) || empty( $this->options ) ) {
            $this->options = get_option( $this->option_name, $this->defaults );
        }

        if( isset( $this->options[$option_name] ) ) {
            return $this->options[$option_name];    // Return user's specified option value
        } elseif( isset( $this->defaults[$option_name] ) ) {
            return $this->defaults[$option_name];   // Return default option value
        }
        return false;
    }

    /**
     * Initialization function to hook into the WordPress init action
     *
     * Instantiates the class on a global variable and sets the class, actions
     * etc. up for use.
     */
    static function instance() {
        global $UserStats;

        // Only instantiate the Class if it hasn't been already
        if( !isset( $UserStats ) ) $UserStats = new UserStats();
    }

	/**
	 * Hook into plugin_action_links filter
	 *
	 * Adds a "Settings" link next to the "Deactivate" link in the plugin listing page
	 * when the plugin is active.
	 *
	 * @param object $links An array of the links to show, this will be the modified variable
	 * @param string $file The name of the file being processed in the filter
	 */
	function plugin_action_links( $links, $file ) {
		if( $file == plugin_basename( USERSTATS_DIRNAME . '/' . basename( __FILE__ ) ) ) {
            $old_links = $links;
            $new_links = array(
                "settings" => '<a href="../wp-admin/users.php?page=' . $this->namespace . '">' . __( 'Settings' ) . '</a>'
            );
            $links = array_merge( $new_links, $old_links );
		}

		return $links;
	}


    /**
     * Route the user based off of environment conditions
     *
     * This function will handling routing of form submissions to the appropriate
     * form processor.
     *
     * @uses UserStats::_admin_options_update()
     */
    function route() {
        $uri = $_SERVER['REQUEST_URI'];
        $protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';
        $hostname = $_SERVER['HTTP_HOST'];
        $url = "{$protocol}://{$hostname}{$uri}";
        $is_post = (bool) ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == "POST" );

        // Check if a nonce was passed in the request
        if( isset( $_REQUEST['_wpnonce'] ) ) {
            $nonce = $_REQUEST['_wpnonce'];

            // Handle POST requests
            if( $is_post ) {
                if( wp_verify_nonce( $nonce, "{$this->namespace}-update-options" ) ) {
                    $this->_admin_options_update();
                }
            }
            // Handle GET requests
            else {

            }
        }
    }

    /**
     * Register scripts used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_script()
     */
    function wp_register_scripts() {
        // Admin JavaScript
        wp_register_script( "{$this->namespace}-admin", USERSTATS_URLPATH . "/js/admin.js", array( 'jquery' ), $this->version, true );
        wp_register_script( "{$this->namespace}-frontjs", USERSTATS_URLPATH . "/js/userstats.js", array( 'jquery' ), $this->version, true );

        wp_register_script( "{$this->namespace}-tablesorter", USERSTATS_URLPATH . "/js/jquery.tablesorter.min.js", array( 'jquery' ), $this->version, true );
        wp_register_script( "{$this->namespace}-tablesorter-widget", USERSTATS_URLPATH . "/js/jquery.tablesorter.widgets.min.js", array( 'jquery' ), $this->version, true );
    }

    /**
     * Register styles used by this plugin for enqueuing elsewhere
     *
     * @uses wp_register_style()
     */
    function wp_register_styles() {
        // Admin Stylesheet
        wp_register_style( "{$this->namespace}-admin", USERSTATS_URLPATH . "/css/admin.css", array(), $this->version, 'screen' );
        wp_register_style( "{$this->namespace}-front", USERSTATS_URLPATH . "/css/userstats.css", array(), $this->version, 'screen' );
    }
}
if( !isset( $UserStats ) ) {
	UserStats::instance();
}

register_activation_hook( __FILE__, array( 'UserStats', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'UserStats', 'deactivate' ) );






function asz_round($value) {
    if(version_compare(PHP_VERSION, '5.3.0', '<')) {
        $x = round(floor($value * 100) / 100,2);
    } else {
        $x = round($value, 2, PHP_ROUND_HALF_UP);
    }

    return $x;
}

function asz_get_post_views() {

	if(is_single()) {
		$statz_post_id = get_the_ID();
		$statz_count_value = get_post_meta( $statz_post_id, '_statz_count', 'true');
		$statz_count_value = (int)$statz_count_value;

		//if($statz_count_value == 0) { $statz_count_value = 1; }


		$statz_count_value++;

		//echo $statz_count_value;

		update_post_meta($statz_post_id, '_statz_count', $statz_count_value);

global $wpdb;

$asz_table_name = $wpdb->prefix . "userstats_count";

$todays_date = date('Y-m-d');

$wpdb->insert(
	$asz_table_name,
	array(
		'post_id' => $statz_post_id,
		'date' => $todays_date ,
		'count' => 1
	),
	array(
		'%d',
		'%s',
		'%d'
	)
);


	}//end if is single
}
add_action('wp_head','asz_get_post_views');



add_action( 'wp_ajax_asz_ind_show_posts', 'asz_ind_show_posts' );
function asz_ind_show_posts() {

$ajax_author_name = $_POST['authorname'];
$startdate = $_POST['startdate'];
$enddate = $_POST['enddate'];

global $wpdb;

$asz_the_users = asz_get_users();

foreach($asz_the_users as $users) {
	//var_dump($users);
//var_dump($_POST);


if($users['user_nicename'] == $ajax_author_name) {

	$asz_the_posts = asz_ind_get_posts($users['ID'], $startdate, $enddate);

		foreach($asz_the_posts as $the_posts) {

			$da_author = $the_posts->post_author;



			$asz_options = get_option( '_user-stats--options' );

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

		//var_dump($users);
			echo "<tr class='asz_ind_posts asz_ind_posts_" . $users['user_nicename'] . "' >";

				if($asz_options['author_author'] != 'author_author') {echo "<td>" . $users['user_nicename'] . "</td>"; }

				if($asz_options['author_article'] != 'author_article') {echo "<td class='asz_article_column'><a href='" . $the_posts->guid . "' target='_blank'>" . $the_posts->post_title . "</a><span class='asz_edit'><a href='" . get_edit_post_link( $the_posts->ID ) . "' target='_blank'>edit</a></span></td>"; }
				if($asz_options['author_date'] != 'author_date') {echo "<td>" . $da_date . "</td>"; }
				if($asz_options['author_cat'] != 'author_cat') {echo "<td>" .  $cat_string . "</td>"; }
				if($asz_options['author_views'] != 'author_views') {echo "<td id='viewcount_" . $the_posts->ID . "'>" . $asz_post_meta . "<span><img title='Reset View Count' class='asz_reset' id='" . $the_posts->ID . "' src='" . USERSTATS_URLPATH . "/images/delete.png" . "' /></span></td>"; }
				if($asz_options['author_dollarviews'] != 'author_dollarviews') {echo "<td>" . $asz_dollar_per_views . "</td>"; }
				if($asz_options['author_dollarthousand'] != 'author_dollarthousand') {echo "<td>" . $asz_dollar_per_thousand_views . "</td>"; }
			echo "</tr>";

		}

}//end if

}

die();
}



//get users
function asz_get_users() {
	global $wpdb;

	$asz_users = $wpdb->get_results(
		"
		SELECT *
		FROM $wpdb->users
		", ARRAY_A
	);

	return $asz_users;
}




function asz_author_post_daterange( $where = '') {
    global $wpdb;
  	global $asz_array;

if($asz_array[0] == '') {} else {

    $where .= $wpdb->prepare( " AND post_date >= %s", date( 'Y-m-d', strtotime($asz_array[0]) ) );

}

if($asz_array[1] == '') {} else {

    $where .= $wpdb->prepare( " AND post_date <= %s", date( 'Y-m-d', strtotime($asz_array[1]) ) );

}

    return $where;
}


function asz_ind_get_posts($auth_id, $startdate, $enddate) {

		$aszstartdate = $startdate;
		$aszenddate = $enddate;


//globals are evil but it you know of another way....
global $asz_array;

$asz_array = array(
				  $aszstartdate,
				  $aszenddate
				  );

//var_dump($asz_array);

		add_filter( 'posts_where', 'asz_author_post_daterange');


		$single_author_posts = get_posts(
			array(
				'author'	=>	$auth_id,
				'post_type'   => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'suppress_filters' => false,
				//'meta_query' => array(
				//	array(
				//		'key' => '_statz_count'
				//	)
				//)
			)
		);


	// Important to avoid modifying other queries - JUST IN CASE!
	remove_filter( 'posts_where', 'asz_author_post_daterange' );


	//return apply_filters(asz_author_post_daterange, $single_author_posts, $aszstartdate, $aszenddate);
	return $single_author_posts;

} // end asz_ind_get_posts





add_action( 'wp_ajax_asz_reset_count', 'asz_reset_count' );
function asz_reset_count() {

global $wpdb;

$postid = $_POST['postid'];

//remove the post meta
update_post_meta($postid, '_statz_count', 0);

//get it to confirm it has been reset
$return_value = get_post_meta($postid, '_statz_count', true);

//remove the database entries
$asz_table_name = $wpdb->prefix . "userstats_count";

$sql = $wpdb->prepare("DELETE FROM $asz_table_name WHERE post_id = %d ", $postid);
$wpdb->query($sql);


//return it, if not 0 can mark as unsuccesfull.
echo $return_value;

die();

}




add_action( 'wp_ajax_user_stats_dismiss', 'user_stats_dismiss' );
function user_stats_dismiss() {

update_option( "user_stats_nag", "1" );

die();

}


function user_stats_n() {

?>
 <div id="usn_message">
 <p>
 <span id="usn">Dismiss</span>
<p>Hi, </p>
<p>You have been using User Stats for 30 days now, I hope you are finding it useful!</p>
<p>If you have, please help support its continued development by purchasing one of these great products or by sending me a little gift to cheer me up :D</p>
</p>
<table id="usn_imgs">
<tr>
<td>
<span class='usn_img'><a href="https://managewp.com/?utm_source=A&utm_medium=Banner&utm_content=mwp_banner_14_250x250&utm_campaign=A&utm_mrl=74"><img title='' src='<?php echo USERSTATS_URLPATH . "/images/managewp.jpg"; ?>' /></a></span>

</td>
<td>
<span class='usn_img'><a href="http://www.shareasale.com/r.cfm?b=394686&u=578774&m=41388&urllink=&afftrack="><img title='' src='<?php echo USERSTATS_URLPATH . "/images/wpengine.jpg"; ?>' /></a></span>
</td>
<td>
<span class='usn_img'><a href="http://themify.me/member/go.php?r=8759&i=b10"><img title='' src='<?php echo USERSTATS_URLPATH . "/images/themify.jpg"; ?>' /></a></span>
</td>
<td>
<span class='usn_img'><a href="http://www.amazon.co.uk/registry/wishlist/3E86Y5KRL1O2P"><img title='' src='<?php echo USERSTATS_URLPATH . "/images/amazon.jpg"; ?>' /></a></span>
</td>
</tr>

<tr>
<td>
Awesome Management
</td>
<td>
Expert Hosting
</td>
<td>
Quality Themes
</td>
<td>
Random Gifts
</td>

</tr>
</table>

 </div>
 <?php

}

add_action( 'wp_ajax_user_stats_n_ajax', 'user_stats_n_ajax' );
function user_stats_n_ajax() {

?>
 <div id="usn_message">
 <p>
 <span id="usn_a">Dismiss</span>
If you find User Stats useful, help support its continued development by purchasing one of these great products or by sending me a little gift to cheer me up :D
</p>
<table id="usn_imgs">
<tr>
<td>
<span class='usn_img'><a href="https://managewp.com/?utm_source=A&utm_medium=Banner&utm_content=mwp_banner_14_250x250&utm_campaign=A&utm_mrl=74"><img title='' src='<?php echo USERSTATS_URLPATH . "/images/managewp.jpg"; ?>' /></a></span>
</td>
<td>
<span class='usn_img'><a href="http://www.shareasale.com/r.cfm?b=394686&u=578774&m=41388&urllink=&afftrack="><img title='' src='<?php echo USERSTATS_URLPATH . "/images/wpengine.jpg"; ?>' /></a></span>
</td>
<td>
<span class='usn_img'><a href="http://themify.me/member/go.php?r=8759&i=b10"><img title='' src='<?php echo USERSTATS_URLPATH . "/images/themify.jpg"; ?>' /></a></span>
</td>
<td>
<span class='usn_img'><a href="http://www.amazon.co.uk/registry/wishlist/3E86Y5KRL1O2P"><img title='' src='<?php echo USERSTATS_URLPATH . "/images/amazon.jpg"; ?>' /></a></span>
</td>
</tr>

<tr>
<td>
Awesome Management
</td>
<td>
Expert Hosting
</td>
<td>
Quality Themes
</td>
<td>
Random Gifts
</td>

</tr>
</table>
 </div>

 <?php

die();

}