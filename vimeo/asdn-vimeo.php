<?php

// -----------------------------------------------------------
// ASDN_Vimeo CLASS
// 
// Purpose: 
//      Uses Vimeo API to extract data about videos for video course catalog
// 
// Relies on: 
//      vimeo.php and other source files from: https://github.com/vimeo/vimeo.php
//      
// How to use: 
//      $response = ASDNVimeoAPI()->test_connection();
// -----------------------------------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// get support tools from vimeo for api requests
require_once 'Vimeo.php';
    
if ( ! class_exists( 'ASDN_VIMEO_API' ) ) :
    
    class ASDN_VIMEO_API {

            // Instance of this class.
            protected static $_instance = null;

            // User Registration Data 
            protected $_vimeo_connection = null;

            
            // Return an instance of this class.
            public static function instance() {
                    // If the single instance hasn't been set, set it now.
                    if ( is_null( self::$_instance ) ) {
                            self::$_instance = new self();
                    }

                    return self::$_instance;
            }
            
            
            // *****************************************************************
            // INITIALIZE - PREPARE FOR VIMEO API REQUESTS
            // *****************************************************************
            
            // constructor
            public function __construct() {
                
                    // establish connection variables
                    $client_id = '2e83ca2582e389a0106c8734de32ae969c3b2e37';
                    $client_secret = 'HNgIiZyhEug984krRKeKLZRhHYs8YS9BWa2BvDx5Swtbppa5qhH/4LpmCz9rPEHez1FtymjNggxLeObDKYIBGp9hH3rlK9G3QCVG+p4oPcQQ0XiyRG7sF4HVuNYtvN3q';
                    $access_token = '3384faf2d4b85ca6e206433773fca62b';

                    // create the connection
                    $lib = new Vimeo\Vimeo( $client_id, $client_secret, $access_token );    

                    if ( $lib instanceof Vimeo\Vimeo ) {
                        $this->_vimeo_connection = $lib;
                    } else {     
                        $this->_vimeo_connection = false;      // if the connection is invalid, set to false
                    }

            }

            // test vimeo connection
            function test_connection() {
                
                    if ( $this->_vimeo_connection ) {
                        $response = $this->_vimeo_connection->request('/tutorial', array(), 'GET');    
                    } else {
                        $response = false;      // if the test is invalid, return false
                    }

                    return $response;   

            }


            // *****************************************************************
            // GET VIDEO DATA
            // *****************************************************************

            // get data for each vimeo video in a string of videos (separated by carriage return)
            public function get_videos( $vimeo_url_block ) {
                // -----------------------------------------------------
                // steps:
                // 1) parse vimeo url string block into individual vimeo urls and vimeo ids
                // 2) for each video,
                //      a) get the video data using Vimeo API 
                //      b) convert that data into a usable format
                // -----------------------------------------------------

                // parse the vimeo urls
                $videos = $this->parse_url_block( $vimeo_url_block ); 

                $video_data = array();
                foreach( $videos as $video ) {

                    // first check to see if this is a private video
                    $private_data = $this->get_private_video_data( $video['id'] );

                    if ( $private_data ) {

                        $video_data[] = $private_data;

                    } else {

                        // if not a private video, then check to see if it is a public video 
                        $public_data = $this->get_public_video_data( $video['url'] );                

                        if ( $public_data ) {

                            $video_data[] = $public_data[0];

                        }

                    }

                }

                return $video_data;

            }

            // parse a block of vimeo urls into array of urls and ids
            public function parse_url_block( $vimeo_url_block ) {

                // parse the vimeo urls 
                $vimeo_urls = explode( PHP_EOL, $vimeo_url_block );    // convert the url string into an array

                // clean up the array and extract the video ids
                $videos = array();
                foreach( $vimeo_urls as $key => $value ) {              

                    // clean up the string and remove any blanks
                    $value = trim( $value );
                    if ( ! $value ) {
                        // get rid of any blanks
                        unset( $vimeo_urls[$key] );
                        continue;
                    }

                    // extract the video id 
                    $id = str_replace( 'https://vimeo.com/', '', $value );
                    if (  strpos( $id, '/' )  ) {
                        $id = substr(  $id, 0, strpos( $id, '/' )  ); 
                    }        

                    // prepare the data 
                    $videos[$key] = array(
                        'url'   => $value,
                        'id'    => $id,
                    );

                }

                // reorder the array after removing blanks
                $videos = array_values( $videos );      

                return $videos;

            }

            
            // *****************************************************************
            // GET DATA FOR PRIVATE VIDEOS
            // *****************************************************************
            public function get_private_video_data( $video_id ) {

                $response = false;

                // get raw video data from vimeo api
                $video_data = $this->private_vimeo_api_request( $video_id );

                if ( $video_data && 
                     isset( $video_data['data']['body']['data'][0] )  
                    ) {
                        $values = $video_data['data']['body']['data'][0];

                        // convert raw data into a usable format 
                        $response = array(
                            'id'            => $video_data['id'],
                            'title'         => $values['name'],
                            'upload_date'   => date_format( date_create( $values['created_time'] ), "F d, Y" ),
                            'description'   => $values['description'],
                            'thumb'         => $values['pictures']['sizes'][0]['link'],
                            'url'           => $values['link'],
                            'iframe'        => '<iframe src="'.$values['player_embed_url'].'" class="asdn-video-series-iframe" width="480" height="240" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>',
                        );

                }

                return $response;

            }

            // grab data from vimeo api 
            public function private_vimeo_api_request( $video_id ) {

                $response = false; 

                if ( $this->_vimeo_connection && $video_id ) {

                    $request_args = array(
                        'query' => $video_id,
                    );
                    $data = $this->_vimeo_connection->request( '/me/videos', $request_args, 'GET' );
                    if (  isset( $data['body']['total'] ) &&  
                          $data['body']['total'] != 0
                        ) {
                        $response = array(
                            'id'    => $video_id,
                            'data'  => $data,
                        );
                    }

                }

                return $response;

            }

            
            // *****************************************************************
            // GET DATA FOR PUBLIC VIDEOS
            // *****************************************************************

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
            public function get_public_video_data( $vimeo_url_string ) {

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
                        $data = $this->public_vimeo_api_request( $check_ID );
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
            public function public_vimeo_api_request( $id = '', $thumbType = 'small' ) {

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

            // *****************************************************************
            // HANDLE DEBUGGING
            // 
            // USE:
            //      $this->_debug = $variable;  
            //      // to display the $variable outside the object (e.g. in footer), use: 
            //      // echo URD()->debug(); 
            // *****************************************************************
            public $_debug = null;    
            public function debug() {
                ob_start();
                print_r( $this->_debug );
                $o = ob_get_clean();
                $o = '<pre>' . $o . '</pre>';
                return $o;
            }

    } // end class: ASDN_VIMEO_API

endif;


/**
 * Main instance of UserRegistrationData.
 *
 * Returns the main instance of FT to prevent the need to use globals.
 */
function ASDNVimeoAPI() {
	return ASDN_VIMEO_API::instance();
}

// Global for backwards compatibility.
$GLOBALS['asdn-vimeo'] = ASDNVimeoAPI();
