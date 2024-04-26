<?php

/*
Plugin Name: ASDN Search and Filter
Plugin URI: https://asdn.org
Description: Enhanced search and filters on Custom Post Types 
Version: 1.3
Text Domain: asdn-search-and-filter
*/

defined( 'ABSPATH' ) || exit;



//===================================================
//=               PLUGIN CONSTANTS                  =
//===================================================
define( 'ASDN_SEARCH_AND_FILTER_DIR', WP_PLUGIN_DIR . '/asdn-search-and-filter' );
define( 'ASDN_SEARCH_AND_FILTER_URL', plugins_url() . '/asdn-search-and-filter' );

 
 
//===================================================
//=          ENQUEUE STYLES AND SCRIPTS             =
//===================================================
add_action( 'wp_enqueue_scripts', 'sf_enqueue_styles_and_scripts', 1000);
function sf_enqueue_styles_and_scripts() {
        // enqueue styles with time added to versioning, so most recent changes are always displayed 

        // plugin styles
        $style_slug = 'asdn_search_and_filter';
        $style_uri = ASDN_SEARCH_AND_FILTER_URL . '/assets/css/asdn-search-and-filter.css';
        $style_filetime = filemtime( ASDN_SEARCH_AND_FILTER_DIR . '/assets/css/asdn-search-and-filter.css' );
        wp_enqueue_style(
                $style_slug,
                $style_uri,
                array(),
                $style_filetime  // adds time to versioning
        );
}

           

//===================================================
//=               VIMEO API CLASS                   =
//===================================================
require_once 'vimeo/asdn-vimeo.php';



//===================================================
//=           PREPARE CUSTOM POST TYPES             =
//===================================================
require_once 'custom-post-type/custom-post-types.php';
//require_once 'custom-post-type/asdn-course-custom-fields.php';  // handles custom fields for "course" cpt



//===================================================
//=            INCLUDE HELPFUL TOOLS                =
//===================================================
require_once 'widgets/asdn-columns-and-rows.php';           // handles column / row shortcodes
require_once 'inc/asdn-post-meta.php';                      // handles post meta



//===================================================
//=           LOAD SEARCH/FILTER CLASSES            =
//===================================================
require_once 'classes/class-asdn-url-params.php';             // handles url params
require_once 'classes/class-asdn-search-and-filter.php';      // search-and-filter base class
require_once 'classes/class-asdn-course-filters.php';         // "course" custom post type search / filters

require_once 'custom-post-type/asdn-video-series-cpt.php';   // video-series cpt functions (include before search/filter class)
                                                             //                            (include after asdn-post-meta.php)
require_once 'classes/class-asdn-video-series-filters.php';  // video-series cpt search / filters



//===================================================
//=       ACTIVATE SEARCH/FILTER CAPABILITY         =
//===================================================
// set the page anchor to always scroll to after each filter/search
// note: for this to work, add param to post_grid shortcode: anchor="asdn-search-and-filter" 
asdnURLParams::AutoTarget('asdn-search-and-filter');

// Course Custom Post Type
$asdn_sf_courses = new asdnCourseFilters();
$asdn_sf_video_series = new asdnVideoSeriesFilters();



// --------------------------------------------------
// --------------------------------------------------
// DEBUG BEGINS
// --------------------------------------------------
// --------------------------------------------------
function hc_special_code_goes_here() {
    global $debug;

    // testing video series filters

    // VideoSeries Custom Post Type

} 
global $debug;
$debug = array();
add_action( 'wp_footer', 'hc_debug');
function hc_debug() {
    global $debug;
    $developer_id = 3;
    $current_user = wp_get_current_user(); 
    if ( $current_user ) {
        $user_id = $current_user->ID;
    } else {
        $user_id = -1;
    }
    if ( $debug && $user_id == $developer_id ) {
        ob_start();
        print_r($debug);
        $results = ob_get_clean();
        $results = '<pre>' . $results . '</pre>';
        echo $results;
    }
}

add_action( 'init', 'hc_error_checking' );
function hc_error_checking() {
    global $debug;
    $developer_id = 3;
    $other_user_id = 1;
    $current_user = wp_get_current_user(); 
    if ( $current_user ) {
        $user_id = $current_user->ID;
        $other_user_id = 1;
        if ( $user_id == $developer_id || $user_id == $other_user_id ) {
            hc_special_code_goes_here();
        }
    }
}
// --------------------------------------------------
// --------------------------------------------------
// DEBUG ENDS
// --------------------------------------------------
// --------------------------------------------------




