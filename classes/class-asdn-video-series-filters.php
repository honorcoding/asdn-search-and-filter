<?php
class asdnVideoSeriesFilters extends asdnSearchAndFilter {
                    
    //
    //  * Constructor 
    //  *
    //  * @return void
    //
    public function __construct() {
            // initialize this class when Wordpress initializes
        
            parent::__construct();
            // DEBUG: FOR TESTING PURPOSES, USING INIT ALREADY, SO HANDLING THIS IN TEMPLATE_REDIRECT            
            // DEBUG: WHEN FINISHED TESTING AND READY TO DO LIVE, DISABLE TEMPLATE_REDIRECT AND GO BACK TO INIT
            //add_action('init', array( $this, 'child_init' ) );        
            add_action('template_redirect', array( $this, 'child_init' ) );        
            
    }
    
    //
    //  * Initializes the child class for manufacturers only
    //  *
    //  * @return void
    //
    public function child_init() {
    
            // This class only handles the manufacturer post type
            //self::AddQueryArg( 'post_type', 'manufacturer' );
            
            // Post Grid for manufacturers only
            $this->AddShortcode( 'sfvc_post_grid', 'VideoSeriesPostGridShortcode' );
            add_filter( 'sf_display_individual_posts', array( $this, 'DisplayIndividualPostsFilter' ), 10, 2 );
            
            // Sort
            asdnURLParams::RegisterURLParam('sfvc_sort');
            $this->AddURLParamHook('VideoSeriesSortURLParamHook');
            $this->AddShortcode( 'sfvc_sort_field', 'VideoSeriesSortFieldShortcode' );

            // Filters
            asdnURLParams::RegisterURLParam('sfvc_presenter');
            $this->AddURLParamHook('PresenterURLParamHook');
            $this->AddShortcode( 'sfvc_presenter_field', 'PresenterFieldShortcode' );
            self::AddFilterRemovalOption('sfvc_presenter');        
            
            asdnURLParams::RegisterURLParam('sfvc_topic');
            $this->AddURLParamHook('TopicURLParamHook');
            $this->AddShortcode( 'sfvc_topic_field', 'TopicFieldShortcode' );
            self::AddFilterRemovalOption('sfvc_topic');        
            
            $this->AddShortcode( 'sfvc_new_videos_field', 'NewVideosFieldShortcode' );
            
    }   

    //
    //  * Handle Post Grid query for manufacturer post type needs and return values
    //  *
    //  * @return array of query values
    //

    protected function PostGridQuery() {
        
            // get post data 
            $data = parent::PostGridQuery();
            
            // add post custom fields to post data
            foreach( $data['posts'] as $key => $post ) {
                
                // get the description
                $description = get_the_content( null, false, $post['id'] );                
                
                // get the presenters
                $terms    = self::GetPostTerms( $post['id'], 'video_series_presenter', false );
                if ( $terms ) {
                    $presenters = implode( ', ', $terms );
                } else {
                    $presenters = '';
                }
                
                // get dates 
                $dates = array();
                if ( function_exists('asdn_get_video_series_dates') ) {
                    $dates = asdn_get_video_series_dates( $post['id'] );
                } 
                        

                // ----------------------------------------------------
                // get video data 
                // ----------------------------------------------------
                /*
                // get the videos
                $series_url_string  = get_post_meta( $post['id'], 'video_series_video_urls', true );                
                $videos = array();
                if ( function_exists( 'ASDNVimeoAPI') ) {
                    $videos = ASDNVimeoAPI()->get_videos( $series_url_string );
                }
                 */
                 
                // get video data 
                $videos = array();
                if ( function_exists('asdn_get_video_data_from_post_meta') ) {
                    $videos = asdn_get_video_data_from_post_meta( $post['id'] );
                }
                
                /*
                $videos = array();
                $vimeo_data_meta_key = 'asdn_vimeo_data';

                // first attempt to grab the video data from post meta 
                // (note: every time the video-series post type is saved, 
                //        it grabs the vimeo data and saves to post meta
                //        this facilitates faster recall on page load)
                $videos = asdn_get_meta( $post['id'], $vimeo_data_meta_key );

                // if the video data is not found in post meta, 
                // then grab the video data from vimeo 
                // and save to post meta for future reference
                if ( ! $videos ) {
                    if ( function_exists( 'ASDNVimeoAPI') ) {
                        $videos = ASDNVimeoAPI()->get_videos( $series_url_string );
                        if ( $videos ) {
                            asdn_update_meta( $post['id'], $vimeo_data_meta_key, $videos );
                        }
                    }        
                }
                 * 
                 */
                
                
                // get post data 
                $data['posts'][$key]['custom_fields'] = array(
                    'dates'             => $dates,
                    'description'       => $description,
                    'presenters'        => $presenters,
                    'videos'            => $videos,
                );
            }
            
            return $data;
            
    }

    
    //
    //  * Retrieve the taxonomy terms for this post 
    //  *
    //  * @return array 
    //
    public static function GetPostTerms( $post_id, $taxonomy, $first_only = true ) {
            
            if ( $first_only === true ) {
                $results = '';
            } else {
                $results = array();
            }
        
            $terms = get_the_terms( $post_id, $taxonomy );
            if ( $terms && !is_wp_error($terms) ) {
                
                foreach ( $terms as $term ) {

                    if ( $first_only === true ) {
                        $term_name = $term->name;
                        if ( $term_name ) { // only return legitimate values
                            $results = $term_name;
                        } else { 
                            $results = '';
                        }
                        break;
                    } else {
                        $term_name = $term->name;
                        if ( $term_name ) { // only return legitimate values
                            $results[] = $term_name;
                        }
                    }
                    
                }
                
            }
            
            return $results;
        
    }
    
    
    
    //
    //  * Shortcode that displays Post Grid for manufacturers only
    //  *
    //  * @return string (html markup)
    //
    public function VideoSeriesPostGridShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'class'             => '',
                    'posts_per_page'    => '30',
                    'no_posts_error'    => 'No results found.',
                    'columns'           => '1',
                    'anchor'            => '',
            ), $atts));
            
            // support meta sorts
            //$this->MetaSortQueries();
            
            // generate shortcode

            // REMOVED: (called directly. otherwise it doesn't properly recognize the child class) 
            //$post_grid_shortcode = '[sf_post_grid post_type="video_series" class="sfvc_post_grid '.$class.'" posts_per_page="'.$posts_per_page.'" no_posts_error="'.$no_posts_error.'" columns="'.$columns.'" anchor="'.$anchor.'" sort_shortcode="sfvc_sort_field" ]';
            //$o = do_shortcode( $post_grid_shortcode );
            $parent_atts = array(
                    'post_type'             => 'video_series',
                    'class'                 => 'sfvc_post_grid '.$class,
                    'posts_per_page'        => $posts_per_page,
                    'no_posts_error'        => $no_posts_error,
                    'columns'               => $columns,
                    'sort_shortcode'        => 'sfvc_sort_field',    
                    'anchor'                => $anchor,                
            );
            $o = $this->PostGridShortcode( $parent_atts );

            // return the results
            return $o;
            
    }
    
    //
    //  * Filter to display individual manufacturer post data
    //  * (Can be overriden by replacing sf_display_individual_posts action).
    //  *
    //  * @return null
    //
    public function DisplayIndividualPostsFilter( $post_content, $posts ) {
        
        // check if this is the appropriate filter to use (i.e. if this is the manufacturer CPT)
        if ( self::GetQueryArg('post_type') == 'video_series' ) {
                 
            $post_content = ''; 
            foreach ($posts as $post ) {
                
                // create the html markup for the video_series post
                $o = '';
                
                    // item container
                    $o .= '<a class="sf_body_item sfvc_body_item" href="'.$post['permalink'].'" target="_blank">';
                    
                        // item title
                        $o .= '<div class="sfvc_body_item_left">';
                        
                            $o .= '<div class="sfvc_video_image">';
                            if (  isset( $post['custom_fields']['videos'][0]['thumb'] )  ) {
                                $o .= '<img src="'.$post['custom_fields']['videos'][0]['thumb'].'" />';                            
                            } else {
                                $o .= '<img src="/wp-content/uploads/no-video-available.jpg" />';
                            }
                            $o .= '</div>';
                            
                        $o .= '</div>';
                        
                        // item body
                        $o .= '<div class="sfvc_body_item_right">';
                        
                            if (  isset( $post['title'] )  ) {                            
                                $o .= '<h3 class="sfvc_post_title">'.$post['title'].'</h3>';
                            }
                            
                            if (  isset( $post['custom_fields']['dates']['end_display'] ) &&
                                  isset( $post['custom_fields']['dates']['end'] ) &&  
                                  $post['custom_fields']['dates']['end'] != '1900-01-01' 
                                ) {
                                $o .= '<div class="sfvc_post_date">'.$post['custom_fields']['dates']['end_display'].'</div>';                            
                            }
                            
                            if (  isset( $post['custom_fields']['description'] )  ) {     
                                $description = $post['custom_fields']['description'];
                                $description = wp_trim_words( $description, 20 );
                                $o .= '<div class="sfvc_post_description">'.$description.'</div>';
                            }
                            
                        $o .= '</div>';
                        
                    $o .= '</a>';

                $post_content .= $o;
                
            } // end: foreach
            
        } // end: if this is manufacturer cpt
        
        return $post_content;
        
    }
    

    //===================================================
    //                VIDEO SERIES SORT TOOLS           =
    //===================================================
   
    //
    //  * Allow wordpress query to handle sorting by meta
    //  *
    //  * @return void
    //
    public function MetaSortQueries() { 
        
            // add custom meta queries to the query args to support sorting by meta
            $meta_query = array(
                'recent_date_clause' => array(
                    'key' => 'recent_date', 
                    'compare' => 'EXISTS',                
                )                    
            );                
            self::AddQueryArg( 'meta_query', $meta_query, true );            

    }
    
    //
    //  * Hook to extract sort param from URL and add to query args
    //  *
    //  * @return void
    //
    public function VideoSeriesSortURLParamHook() {
            $sfvc_sort = asdnURLParams::GetURLParam( 'sfvc_sort' );
            if ( $sfvc_sort ) {
                switch ($sfvc_sort) {
                    case 'VideoSeriesTitleASC':
                        self::AddQueryArg( 'orderby', 'title' );
                        self::AddQueryArg( 'order', 'ASC' );
                        break;
                    case 'VideoSeriesTitleDESC':
                        self::AddQueryArg( 'orderby', 'title' );
                        self::AddQueryArg( 'order', 'DESC' );
                        break;
                    case 'VideoSeriesDateASC':
                        self::AddQueryArg( 'meta_key', 'asdn_video_date_end' );
                        self::AddQueryArg( 'meta_type', 'DATE' );
                        self::AddQueryArg( 'orderby', 'meta_key' );
                        self::AddQueryArg( 'order', 'ASC' );
                        break;
                    case 'VideoSeriesDateDESC':
                        self::AddQueryArg( 'meta_key', 'asdn_video_date_end' );
                        self::AddQueryArg( 'meta_type', 'DATE' );
                        self::AddQueryArg( 'orderby', 'meta_key' );
                        self::AddQueryArg( 'order', 'DESC' );
                        break;
                    default:
                        // do nothing
                        break;
                }                
            } else {
                self::AddQueryArg( 'orderby', 'title' );
                self::AddQueryArg( 'order', 'ASC' );                        
            }
    }    
    
    //
    //  * Shortcode to display the sort field
    //  *
    //  * @return html markup
    //
    public function VideoSeriesSortFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                'unselected' => '',
            ), $atts));
            
            
            // determine which sort option is currently selected
            $current_sort = asdnURLParams::GetURLParam('sfvc_sort');
            
            // sort dropdown markup
            $sort_dropdown = '<select id="sf_sort_field" '.self::GetDropDownFilterScript('sf_sort_field').' >';
                
                if ( $unselected != '' ) {
                    // option blank
                    $sort_dropdown .= '<option '; 
                    $sort_dropdown .= ' disabled value ';
                    $sort_dropdown .= ( ! $current_sort ) ? ' selected ' : '';
                    $sort_dropdown .= ' >'.$unselected.'</option>';                                     
                }

                // option VideoSeriesTitleASC
                $NameASC_URL = self::AddParamRemovePageFromCurrentURL( 'sfvc_sort', 'VideoSeriesTitleASC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$NameASC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'VideoSeriesTitleASC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Title: A-Z</option>';

                // option VideoSeriesTitleASC
                $NameDESC_URL = self::AddParamRemovePageFromCurrentURL( 'sfvc_sort', 'VideoSeriesTitleDESC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$NameDESC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'VideoSeriesTitleDESC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Title: Z-A</option>';
                
                // option VideoSeriesDateASC
                $DateASC_URL = self::AddParamRemovePageFromCurrentURL( 'sfvc_sort', 'VideoSeriesDateASC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$DateASC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'VideoSeriesDateASC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Date: Newest</option>';

                // option VideoSeriesDateDESC
                $DateDESC_URL = self::AddParamRemovePageFromCurrentURL( 'sfvc_sort', 'VideoSeriesDateDESC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$DateDESC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'VideoSeriesDateDESC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Date: Oldest</option>';
                
            $sort_dropdown .= '</select>';
                
            
            // render widget
            $o = '';

                $o .= '<div class="sf_sort">';
                    $o .= 'Sort by: '. $sort_dropdown;
                $o .= '</div>';
            
            return $o;
    
    }
    


    //===================================================
    //                   FILTER TOOLS                   =
    //===================================================
    //
    //  * Hook to extract presenter param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function PresenterURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'video_series' ) {
                $sfvc_presenter = asdnURLParams::GetURLParam( 'sfvc_presenter' );
                if ( $sfvc_presenter ) {
                    // get content_area param and set query args
                    // get category param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'video_series_presenter', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfvc_presenter ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );
                }
            }
    }    
    
    //
    //  * Shortcode to display the presenter field
    //  *
    //  * @return html markup
    //
                    
    public function PresenterFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Presenter',
                    'post_type'         => 'video_series',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'video_series_presenter',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which content_area option is currently selected
            $current_presenter = asdnURLParams::GetURLParam('sfvc_presenter');
            
            $selected_presenter = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_presenter) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_presenter = asdnURLParams::GetURLSafeText( $current_presenter );
                    break;
                }
            }            
            
            // drop down mark-up
            $presenter_dropdown = '<select id="sfvc_presenter_field" '.self::GetDropDownFilterScript('sfvc_presenter_field').' >';

                // option blank
                $presenter_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfvc_presenter', asdnURLParams::GetCurrentURL() );
                $presenter_dropdown .= ' value="'.$url_all_removed.'" ';
                //$presenter_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfvc_presenter', 'All' ).'" ';
                $presenter_dropdown .= ( $selected_presenter == '' ) ? ' selected ' : '';
                $presenter_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $presenter_dropdown .= '<option '; 
                    $presenter_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfvc_presenter', $term->name ).'" ';
                    $presenter_dropdown .= (  $selected_presenter == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $presenter_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $presenter_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfvc_presenter sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $presenter_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }    
    
    //
    //  * Hook to extract topic param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function TopicURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'video_series' ) {
                $sfvc_topic = asdnURLParams::GetURLParam( 'sfvc_topic' );
                if ( $sfvc_topic ) {
                    // get content_area param and set query args
                    // get category param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'video_series_topic', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfvc_topic ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );
                }
            }
    }    
    
    //
    //  * Shortcode to display the topic field
    //  *
    //  * @return html markup
    //
                    
    public function TopicFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Topic',
                    'post_type'         => 'video_series',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'video_series_topic',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which content_area option is currently selected
            $current_topic = asdnURLParams::GetURLParam('sfvc_topic');
            
            $selected_topic = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_topic) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_topic = asdnURLParams::GetURLSafeText( $current_topic );
                    break;
                }
            }            
            
            // drop down mark-up
            $topic_dropdown = '<select id="sfvc_topic_field" '.self::GetDropDownFilterScript('sfvc_topic_field').' >';

                // option blank
                $topic_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfvc_topic', asdnURLParams::GetCurrentURL() );
                $topic_dropdown .= ' value="'.$url_all_removed.'" ';
                //$topic_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfvc_topic', 'All' ).'" ';
                $topic_dropdown .= ( $selected_topic == '' ) ? ' selected ' : '';
                $topic_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $topic_dropdown .= '<option '; 
                    $topic_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfvc_topic', $term->name ).'" ';
                    $topic_dropdown .= (  $selected_topic == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $topic_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $topic_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfvc_topic sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $topic_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }   
    
    
    //
    //  * Shortcode to display the new videos field
    //  *
    //  * @return html markup
    //
    public function NewVideosFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'See Latest Videos',
                    'value'             => 'VideoSeriesDateASC',
                    'post_type'         => 'video',
                    'class'             => '',                
            ), $atts));
            
            // add new sort value
            $new_videos_url = self::AddParamRemovePageFromCurrentURL( 'sfvc_sort', $value );    // note: automatically clears old sort value
            $new_videos_button = '<a href="'.$new_videos_url.'" class="sf_button">'.$label.'</a>';
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_featured_video sf_dropdown">';
                    $o .= $new_videos_button;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }
    
        
} // end class 



