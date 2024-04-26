<?php

/* 
 * 
 * HANDLES FUNCTIONS AND FILTERS FOR THE video_series CUSTOM POST TYPE
 * (NOTE: RELIES ON ADVANCED CUSTOM FIELDS PLUGIN)
 * (NOTE: COORDINATES WITH THEME TEMPLATE FILE single-video_series.php)
 * 
 */

// ALLOW THE TEMPLATE TO BE HANDLED BY THE PLUGIN
// THIS IS CURRENTLY DISABLED
//add_filter( 'single_template', 'asdn_video_series_single_template', 99 );
function asdn_video_series_add_single_template( $template ) {
    if ( get_post_type() == 'video_series' ) {
      return plugin_dir_path( __FILE__ ) . '/templates/single-video_series.php';
    }
    return $template;
}


// ------------------------------------------------------------
// get video dates (start, end) for a video series CPT by post_id
// ------------------------------------------------------------
function asdn_get_video_series_dates( $post_id ) {
    
        // only applies to video series CPT
        if ( get_post_type( $post_id ) != 'video_series' ) {
            return;
        }
    
        $start_date_meta_key = 'asdn_video_date_start';
        $end_date_meta_key   = 'asdn_video_date_end';
            
        $start_date = asdn_get_meta( $post_id, $start_date_meta_key );
        $end_date = asdn_get_meta( $post_id, $end_date_meta_key );
        
        $dates = array();
        
        if ( $start_date && $end_date ) {

            $dates['start'] = $start_date;
            $dates['start_display'] = date_format( date_create( $start_date ), 'F d, Y');
                                        
            $dates['end']   = $end_date;        
            $dates['end_display'] = date_format( date_create( $end_date ), 'F d, Y');

        } 
        
        return $dates;
                
}


// ------------------------------------------------------------
// handle updates for single video series CPT
// ------------------------------------------------------------
add_action('save_post_video_series', 'asdn_save_video_series_dates');
function asdn_save_video_series_dates( $post_id ) {

        // Unhook this action to prevent an infinite loop
        remove_action( 'save_post_video_series', 'asdn_save_video_series_dates' );
        
        // only applies to video series CPT
        if ( get_post_type( $post_id ) == 'video_series' ) {


            // get series urls as a string
            $series_url_list = get_field( 'video_series_video_urls', $post_id );    

            // get videos from series url list
            $videos = array();
            if ( function_exists( 'ASDNVimeoAPI') ) {
                $videos = ASDNVimeoAPI()->get_videos( $series_url_list );
            }
    
            if ( $videos ) {
                
                // SAVE THE DATES
                // get the dates from vimeo and store in post meta
                $dates = array();
                if ( function_exists('asdn_get_video_series_dates_from_vimeo') ) {
                    $dates = asdn_get_video_series_dates_from_vimeo( $videos );    

                    // save the start date to post meta
                    $start_date_meta_key = 'asdn_video_date_start';
                    if ( isset( $dates['start'] ) ) {
                        $start_date = date_format( date_create( $dates['start'] ), "Y-m-d" );
                    } else {
                        $start_date = '1900-01-01';
                    }
                    if ( function_exists('asdn_update_meta') ) {
                        asdn_update_meta( $post_id, $start_date_meta_key, $start_date );
                    }

                    // save the end date to post meta
                    $end_date_meta_key   = 'asdn_video_date_end';
                    if ( isset( $dates['end'] ) ) {
                        $end_date = date_format( date_create( $dates['end'] ), "Y-m-d" );
                    } else {
                        $end_date = '1900-01-01';
                    }
                    if ( function_exists('asdn_update_meta') ) {
                        asdn_update_meta( $post_id, $end_date_meta_key, $end_date );
                    }

                } // end : if $dates
                
                
                // SAVE THE VIMEO DATA 
                // store all data in post_meta for later retrieval 
                $vimeo_data_meta_key = 'asdn_vimeo_data';
                asdn_update_meta( $post_id, $vimeo_data_meta_key, $videos );
                
            } // end : if $videos
                
        } // end: if video series post type
    
        // Now re-hook the action
	add_action( 'save_post_video_series', 'asdn_save_video_series_dates' );
        
}



// get the dates for a video series by extracting them from the vimeo url data
function asdn_get_video_series_dates_from_vimeo( $videos ) {
    
    $dates = array();
    
    $first_date = ''; $last_date = '';
    if ( $videos ) {
        
        // get dates
        foreach( $videos as $video ) {
            $this_date = $video['upload_date'];
            if (  $first_date == '' || $this_date < $first_date  ) {
                $first_date = $this_date;
            }
            if (  $last_date == '' || $this_date > $last_date  ) {
                $last_date = $this_date;
            }
        }
        
    }
    
    if ( $first_date != '' && $last_date != '' ) {
        
        $dates['start'] = $first_date;
        $dates['end'] = $last_date;        
        
    }
    
    return $dates;
    
}

function asdn_get_video_data_from_post_meta( $post_id ) {
    
    // 1. first, look for the video data in post meta 
    // 2. if not found there, then call to vimeo
    // 2b. but be sure to save the data to post meta 
    
    // ----------------------------------------------------
    // get video data 
    // ----------------------------------------------------
    $videos = array();
    $vimeo_data_meta_key = 'asdn_vimeo_data';
    $video_series_urls_key = 'video_series_video_urls';
    
    // 1. first attempt to grab the video data from post meta 
    // (note: every time the video-series post type is saved, 
    //        it grabs the vimeo data and saves to post meta
    //        this facilitates faster recall on page load)
    $videos = asdn_get_meta( $post_id, $vimeo_data_meta_key );
    
    // 2. if the video data is not found in post meta, 
    // then grab the video data from vimeo 
    // and save to post meta for future reference
    if ( ! $videos ) {
        if ( function_exists( 'ASDNVimeoAPI') ) {
            $series_url_string = get_field( $video_series_urls_key, $post_id );    
            $videos = ASDNVimeoAPI()->get_videos( $series_url_string );
            if ( $videos ) {
                asdn_update_meta( $post_id, $vimeo_data_meta_key, $videos );
            }
        }        
    }
    
    return $videos;
    
}


// ------------------------------------------------------------
// get vimeo data from list of vimeo urls (separated by PHP_EOL - end-of-line)
// ------------------------------------------------------------
// returns properties:
//      $vimeo_data['id']
//      $vimeo_data['title']
//      $vimeo_data['upload_date']
//      $vimeo_data['description']
//      $vimeo_data['thumb']
//      $vimeo_data['url']
//      $vimeo_data['iframe']
function asdn_video_series_parse_vimeo_urls( $vimeo_url_string ) {
    
    // parse the vimeo urls 
    $vimeo_urls = explode( PHP_EOL, $vimeo_url_string );    // convert the url string into an array
    foreach( $vimeo_urls as $key => $value ) {              // remove blank lines from the array 
        $value = trim( $value );
        if ( ! $value ) {
            // get rid of any blanks
            unset( $vimeo_urls[$key] );
        }        
    }        
    $vimeo_urls = array_values( $vimeo_urls );              // reorder the array after removing blanks
    
    // extrapolate vimeo information
    $vimeo_data = array();
    foreach( $vimeo_urls as $key => $url ) {
        $is_vimeo = strpos( $url, 'vimeo.com' );
        if (  $is_vimeo !== false  ) {  // if the item is a vimeo link

            // get the vimeo id
            $check_ID = str_replace( 'https://vimeo.com/', '', $url );
            
            // get the vimeo information from vimeo api
            $data = asdn_get_vimeo_data( $check_ID );
            if ( $data ) {  // if valid vimeo ID, then populate data
                            // otherwise, ignore
                
                $vimeo_data[$key]['id'] = $data['id'];
                $vimeo_data[$key]['title'] = $data['title'];
                $vimeo_data[$key]['upload_date'] = date_format( date_create( $data['upload_date'] ), "F d, Y" );
                $vimeo_data[$key]['description'] = $data['description'];
                $vimeo_data[$key]['thumb'] = $data['thumb'];

                // get the url and iframe code
                $vimeo_data[$key]['url'] = $url;            
                $video_url = str_replace( 'https://vimeo.com/', 'https://player.vimeo.com/video/', $url );
                $vimeo_data[$key]['iframe'] = '<iframe src="'.$video_url.'" class="asdn-video-series-iframe" width="480" height="240" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
                
            }
                        
        }        
    }
    
    return $vimeo_data;
    
}


// get vimeo thumbnails based on vimeo id
function asdn_get_vimeo_data( $id = '', $thumbType = 'small' ) {

    $id = trim( $id );

    $ids_array = explode( '/', $id );
    $id = $ids_array[0];

    if ( $id == '' ) {
        return FALSE;
    }

    $apiData = unserialize( file_get_contents( "http://vimeo.com/api/v2/video/$id.php" ) );
    //$apiData = unserialize( file_get_contents( "https://api.vimeo.com/videos/:$id" ) );

    if ( is_array( $apiData ) && count( $apiData ) > 0 ) {

        $videoInfo = $apiData[ 0 ];
        
        $vimeo_data = array();
        
        $vimeo_data['id'] = $videoInfo[ 'id' ];
        $vimeo_data['title'] = $videoInfo[ 'title' ];
        $vimeo_data['upload_date'] = $videoInfo[ 'upload_date' ];
        $vimeo_data['description'] = $videoInfo[ 'description' ];
        
        switch ( $thumbType ) {
            case 'small':
                $vimeo_data['thumb'] = $videoInfo[ 'thumbnail_small' ];
                break;
            case 'large':
                $vimeo_data['thumb'] =  $videoInfo[ 'thumbnail_large' ];
                break;
            case 'medium':
                $vimeo_data['thumb'] =  $videoInfo[ 'thumbnail_medium' ];
                break;
            default:
                break;
        }
        
        return $vimeo_data;

    }

    return FALSE;

}


