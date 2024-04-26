<?php

/**
 * Handles Search and Filter functions
 * Note: Only one class can be instantiated per page.
 *
 * @class asdnSearchAndFilter
 */
class asdnSearchAndFilter {
    
    // tracks the number of instantiations of this class (as sub-classes)
    static private $_total_instances;
    
    // handles query
    protected $_post_type;
    static protected $_query_args;
    static protected $_last_sql_query;
    
    // handles display
    static protected $_filter_removal_options;
    static protected $_message;
    
    // handles united states
    static protected $_united_states_list;
    
    //
    //  * Constructor 
    //  *
    //  * @return void
    //
    public function __construct() {
            if ( isset( self::$_total_instances ) ) {
                self::$_total_instances++;
            } else {
                self::$_total_instances = 1;
            }
        
            // init actions of parent class only needs to be run once
            if ( self::$_total_instances <= 1 ) {
                // initialize this class when Wordpress initializes
                add_action('init', array( $this, 'init' ) );        
            }
            
    }
    
    //
    //  * Initializes Search and Filters
    //  *
    //  * @return void
    //
    public function init() {
            
            // init vars
            self::$_query_args = array();
            self::$_filter_removal_options = array();
            self::$_message = '';
            self::AddQueryArg('sf_query','true'); // helps WP distinguish this loop from other loop
            
            // prep united states list
            self::$_united_states_list =  array(
                    'AL'=>"Alabama",  
                    'AK'=>"Alaska",  
                    'AZ'=>"Arizona",  
                    'AR'=>"Arkansas",  
                    'CA'=>"California",  
                    'CO'=>"Colorado",  
                    'CT'=>"Connecticut", 
                    'DC'=>"District Of Columbia",  
                    'DE'=>"Delaware",  
                    'FL'=>"Florida",  
                    'GA'=>"Georgia",  
                    'HI'=>"Hawaii",  
                    'ID'=>"Idaho",  
                    'IL'=>"Illinois",  
                    'IN'=>"Indiana",  
                    'IA'=>"Iowa",  
                    'KS'=>"Kansas",  
                    'KY'=>"Kentucky",  
                    'LA'=>"Louisiana",  
                    'ME'=>"Maine",  
                    'MD'=>"Maryland",  
                    'MA'=>"Massachusetts",  
                    'MI'=>"Michigan",  
                    'MN'=>"Minnesota",  
                    'MS'=>"Mississippi",  
                    'MO'=>"Missouri",  
                    'MT'=>"Montana",
                    'NE'=>"Nebraska",
                    'NV'=>"Nevada",
                    'NH'=>"New Hampshire",
                    'NJ'=>"New Jersey",
                    'NM'=>"New Mexico",
                    'NY'=>"New York",
                    'NC'=>"North Carolina",
                    'ND'=>"North Dakota",
                    'OH'=>"Ohio",  
                    'OK'=>"Oklahoma",  
                    'OR'=>"Oregon",  
                    'PA'=>"Pennsylvania",  
                    'RI'=>"Rhode Island",  
                    'SC'=>"South Carolina",  
                    'SD'=>"South Dakota",
                    'TN'=>"Tennessee",  
                    'TX'=>"Texas",  
                    'UT'=>"Utah",  
                    'VT'=>"Vermont",  
                    'VA'=>"Virginia",  
                    'WA'=>"Washington",  
                    'WV'=>"West Virginia",  
                    'WI'=>"Wisconsin",  
                    'WY'=>"Wyoming"
            );

            // Post Grid
            $this->AddShortcode( 'sf_post_grid', 'PostGridShortcode' );
            // Note: To override how the individual posts are displayed, use the filter: sf_display_individual_post
            // Example: 
            //      1. Add filter to the child_init() function of the child class:
            //          add_filter( 'sf_display_individual_posts', array( $this, 'DisplayIndividualPostsFilter' ), 10, 2 );
            //      2. Then create the filter function:
            //          public function DisplayIndividualPostsFilter( $post_content, $post ) {
            //              // check if this is the appropriate filter to use
            //              if ( self::GetQueryArg('post_type') == 'manufacturer' ) {
            //                  $o = '<a class="sf_body_item" href="'.$post['permalink'].'" >';
            //                      $o .= $post['title'];
            //                  $o .= '</a>';
            //                  $post_content = $o;
            //              }
            //              return $post_content;
            //          }
      
           
            // Custom Query SQL - add custom WHERE, JOIN and DISTINC commands for search / filter features
            add_filter( 'posts_where', array( $this, 'CustomQueryWhereFilters' ), 10, 2 );
            add_filter( 'posts_join', array( $this, 'CustomQueryJoinFilters' ),10, 2 );   
            add_filter( 'posts_distinct', array( $this, 'NoDuplicatesInQuery' ), 10, 2 );

            // Pagination
            asdnURLParams::RegisterURLParam('sf_page');
            $this->AddURLParamHook('PaginationURLParamHook');
            
            // Sort
            asdnURLParams::RegisterURLParam('sf_sort');
            $this->AddURLParamHook('SortURLParamHook');
            $this->AddShortcode( 'sf_sort_field', 'SortFieldShortcode' );
                        
            // Search 
            asdnURLParams::RegisterURLParam('sf_search');
            $this->AddURLParamHook('SearchURLParamHook');
            $this->AddShortcode( 'sf_search_field', 'SearchFieldShortcode' ); 
            self::AddFilterRemovalOption('sf_search');

            
            // Filters            
            //asdnURLParams::RegisterURLParam('sf_country');
            //$this->AddURLParamHook('CountryURLParamHook');
            //$this->AddShortcode( 'sf_country_field', 'CountryFieldShortcode' );
            //self::AddFilterRemovalOption('sf_country');

    }

    
    //===================================================
    //=                POST GRID TOOLS                  =
    //===================================================
    
    //
    //  * Shortcode that displays Post Grid
    //  *
    //  * @return string (html markup)
    //
    public function PostGridShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'post_type'             => '',
                    'class'                 => '',
                    'posts_per_page'        => '30',
                    'no_posts_error'        => 'No results found.',
                    'columns'               => '2',
                    'pagination_link_count' => '1',
                    'sort_shortcode'        => 'sf_sort_field',    
                    'sort_unselected'       => '',
                    'anchor'                => '',
            ), $atts));

            // prepare variables
            switch( $columns ) {
                case '1':
                    $column_class = ' one-column ';
                    break;
                case '2':
                    $column_class = ' two-column ';
                    break;
                case '3':
                    $column_class = ' three-column ';
                    break;
                default:
                    $column_class = ' two-column ';
                    break;                    
            }
            
            // prepare query $args
            if ( ! isset( self::$_query_args['post_type'] ) ) {
                if ( $post_type == '' ) {
                    return '';  // if no post type available, then end it here 
                }
                self::AddQueryArg( 'post_type', $post_type );
            }
            self::AddQueryArg( 'posts_per_page', $posts_per_page );
            self::ParseArgsFromURLParams();
            
            
            // get the query values (based on pre-calculated $args)
            $data = $this->PostGridQuery();
            
            // get pagination and sort markup
            $pagination_info = $pagination_links = '';
            if ( isset( $data['pagination'] ) ) {
                $pagination_info = $this->GetPaginationInformation( $data['pagination'] );
                $pagination_links = $this->GetPaginationLinks( $data['pagination'], $pagination_link_count );
            }
            $sort_field = do_shortcode('['.$sort_shortcode.' unselected="'.$sort_unselected.'"]');
            $filter_removal_options = $this->DisplayFilterRemovalOptions();
            
            if ( $anchor != '' ) {
                $anchor_html = ' id="'.$anchor.'" ';
            } else {
                $anchor_html = '';
            }
                
            // render widget
            $o = '<div '.$anchor_html.' class="sf_post_grid '.$class.'">';
            
                
            if ( $data ) {  // display results of query

                //                
                // UNCOMMENT TO DEBUG QUERY VALUES
                
                //ob_start();
                //var_dump(self::$_query_args);
                //var_dump(self::$_last_sql_query);
                //var_dump($data);  
                //$results = ob_get_clean();
                //$results = '<pre>' . $results . '</pre>';
                //self::$_message .= $results;                
                
                //
                
                // post grid messages 
                // Note: for debugging purposes, may have to display this after the DisplayIndividualPosts fires
                if ( self::$_message != '' ) {
                    //$o .= '<div class="sf_message"><h5>Debug</h5>'.self::$_message.'</div>';
                }
                                
                // post grid header
                $o .= '<div class="sf_header">';
                    $o .= '<div class="sf_row">';
                        $o .= $pagination_info;
                        $o .= '<div class="sf_sort">';
                            $o .= $sort_field;
                        $o .= '</div>';
                    $o .= '</div>';
                    if ( $filter_removal_options != '' ) {                        
                        $o .= '<div class="sf_row">';
                            $o .= '<div class="sf_filter_removal_options">';
                                $o .= $filter_removal_options;
                            $o .= '</div>';
                        $o .= '</div>';
                    }
                $o .= '</div>';
                
                
                // post grid body
                $o .= '<div class="sf_body '.$column_class.'">';
                
                    $o .= $this->DisplayIndividualPosts( $data['posts'] );                    
                            
                $o .= '</div>';
                
                
                // post grid footer 
                $o .= '<div class="sf_footer">';
                    $o .= $pagination_links;
                $o .= '</div>';
                                               
            } else {        // if no query results, then display "no results" message

                //                
                // UNCOMMENT TO DEBUG QUERY VALUES
                //ob_start();
                //var_dump(self::$_query_args);
                //var_dump(self::$_last_sql_query);                
                //var_dump($data); 
                //$results = ob_get_clean();
                //$results = '<pre>' . $results . '</pre>';
                //self::$_message .= $results;                
                //
                
                // post grid messages                
                if ( self::$_message != '' ) {
                    $o .= '<div class="sf_message"><h5>Debug</h5>'.self::$_message.'</div>';
                }
                                
                $o .= '<div class="sf_header">';
                    if ( $filter_removal_options != '' ) {                        
                        $o .= '<div class="sf_row">';
                            $o .= '<div class="sf_filter_removal_options">';
                                $o .= $filter_removal_options;
                            $o .= '</div>';
                        $o .= '</div>';
                    }
                $o .= '</div>';
                
                $o .= '<div class="sf_message">';
                    $o .= $no_posts_error;
                $o .= '</div>';
                
            }
            
            $o .= '</div>';
            
                if ( self::$_message != '' ) {
                    $o .= '<div class="sf_message"><h5>Debug</h5>'.self::$_message.'</div>';
                }
            
            // make sure all shortcodes are processed
            //$o = do_shortcode($o);
                
            return $o;

    }
    
    //
    //  * Filter to display individual post data
    //  * (Can be overriden by replacing sf_display_individual_posts action).
    //  *
    //  * @return null
    //
    public function DisplayIndividualPosts( $posts ) {
        
            $post_content = '';
        
            // get the basic post content
            foreach( $posts as $post ) {
                
                $post_content .= '<a class="sf_body_item" href="'.$post['permalink'].'" >';
                    $post_content .= $post['title'];
                $post_content .= '</a>';
                
            }
            
            // apply any filters
            if ( has_filter( 'sf_display_individual_posts' ) ) {
                $post_content = apply_filters( 'sf_display_individual_posts', $post_content, $posts );
            } 
                        
            return $post_content;
            
    }
    
    
    //
    //  * Handle Post Grid query and return values
    //  *
    //  * @return array of query values
    //
    protected function PostGridQuery() {
            $values = array();
        
            // The Query using pre-calculated $args
            $query = new WP_Query( self::$_query_args );
            self::$_last_sql_query = $query->request;

            // The Loop
            if ( $query->have_posts() ) {
                
                global $wp; // for page urls
                
                // -----------------------------------------
                // get post information
                // -----------------------------------------
                
                $unique_posts = array();
                while ( $query->have_posts() ) {
                    $query->the_post();
                    if( ! in_array( get_the_ID(), $unique_posts ) ) {   // no duplicates
                        $unique_posts[] = get_the_ID();
                        $values['posts'][] = array(
                                'id'            => get_the_ID(),        
                                'title'         => get_the_title(),
                                'permalink'     => get_the_permalink(),
                        );
                    }
                }
                
                // -----------------------------------------
                // get pagination information
                // -----------------------------------------
                
                // page counts
                $page = isset(self::$_query_args['paged']) ? self::$_query_args['paged'] : '1';
                $values['pagination']['page'] = $page;
                $values['pagination']['total_pages'] = $query->max_num_pages;
                
                // post counts 
                $posts_per_page = self::$_query_args['posts_per_page'];
                if ( $posts_per_page != '-1' ) {
                    $values['pagination']['start_post'] = ( ( $page - 1 ) * $posts_per_page ) + 1;                    
                } else {
                    $values['pagination']['start_post'] = 1;
                }
                $posts_on_this_page = $query->post_count;   // in case the last page does not have the full amount of posts per page
                $values['pagination']['end_post'] = $values['pagination']['start_post'] + $posts_on_this_page - 1;
                $values['pagination']['total_posts'] = $query->found_posts;
                
                // prev page 
                if ( $page == '1' ) {
                    $values['pagination']['prev_page'] = '';
                } else {
                    $values['pagination']['prev_page'] = $page - 1;
                }
                
                // next page
                if ( $page == $values['pagination']['total_pages'] ) {
                    $values['pagination']['next_page'] = '';
                } else {
                    $values['pagination']['next_page'] = $page + 1;
                }

                // first and last pages 
                $values['pagination']['first_page'] = 1;
                $values['pagination']['last_page'] = $values['pagination']['total_pages'];
                
            } else {
                // no posts found
            }

            /* Restore original Post Data */
            wp_reset_postdata();  
            
            return $values;
    }
    
    //
    //  * Parse URL params and convert into $args for Post Grid query 
    //  *
    //  * @return null
    //
    static public function ParseArgsFromURLParams() {
            do_action('sf_get_args_from_url_params');
    }    
    
    
    //
    //  * Display the filter removal options
    //  *
    //  * @return string (html markup)
    //    
    public function DisplayFilterRemovalOptions() {
            
            $o = '';
            
            if ( self::$_filter_removal_options ) {

                // generate html markup from $_filter_removal_options
                foreach( self::$_filter_removal_options as $param ) {
                    
                    if ( is_array($param) ) {                        
                        
                        $this_param = ( isset( $param['param'] ) ) ? $param['param'] : '';
                        $value = asdnURLParams::GetURLParam($this_param);
                        $dependent_param = ( isset( $param['dependent'] ) ) ? $param['dependent'] : '';
                        $label = ( isset( $param['label'] ) ) ? $param['label'] : '';
                        if ( $label ) {
                            $label = $label . ': ';
                        }
                        
                    } else {
                        
                        $this_param = $param;
                        $value = asdnURLParams::GetURLParam($this_param);
                        $dependent_param = '';
                        $label = '';
                        
                    }
                    
                    if ( $value != '' ) { 
                        
                        if ( $dependent_param != '' ) {

                            // if there is a dependent attached, then remove that too
                            $new_url = asdnURLParams::RemoveParamFromURL( $this_param, asdnURLParams::GetCurrentURL() ); // remove the param
                            $new_url = asdnURLParams::RemoveParamFromURL( $dependent_param, $new_url ); // remove the param
                            $new_url = asdnURLParams::RemoveParamFromURL( 'sf_page', $new_url ); // also remove/reset page param                               

                        } else {

                            // if there is not a dependent attached, then only remove this param
                            $new_url = asdnURLParams::RemoveParamFromURL( $this_param, asdnURLParams::GetCurrentURL() ); // remove the param
                            $new_url = asdnURLParams::RemoveParamFromURL( 'sf_page', $new_url ); // also remove/reset page param                               

                        }
                      
                        $o .= '<div class="sf_filter_removal_item">';

                                $o .= '<p>'.$label.$value.'</p>';
                                $o .= '<a href="'.$new_url.'">X</a>';

                        $o .= '</div>';
                        
                    }
                                        
                }

            } 
            
            if ( $o != '' ) {
                // if there are filter removal items, then add the appropriate text
                $o = 'Current Filters <span class="asdn-gray">(Click the "X" by the filter to clear your search items)</span>:&nbsp;&nbsp;' . $o; 
            }
            
            return $o;
            
    }    
    
    
    
    //===================================================
    //=     QUERY ARG, URL PARAM & SHORTCODE TOOLS      =
    //===================================================
    
    //
    //  * Add an arg to the query
    //  *
    //  * @return void
    //
    static public function AddQueryArg( $arg, $value, $append = false ) {
        
            // check if query arg already exists
            $key = array_key_exists( $arg, self::$_query_args );
            
            if ( $key === false ) {
                // if key does not exist, then add it
                self::$_query_args[$arg] = $value;
            } else {
                if ( $append === false ) {
                    // if $append == false, then replace
                    self::$_query_args[$arg] = $value;
                } else {
                    // if $append == true...
                    if ( $arg == 'tax_query' || $arg == 'meta_query' ) {
                        
                        // handle special tax_query or meta_query considerations
                        if (  array_key_exists( 'relation', self::$_query_args[$arg] )  ) {
                            // relation already exists, add another dimension
                            self::$_query_args[$arg][] = $value;
                        } else {
                            // append to the additional query with an AND operator
                            $appended = array(
                                'relation'  => 'AND',
                                self::$_query_args[$arg],
                                $value
                            );
                            self::$_query_args[$arg] = $appended;                                
                       }
                       
                    } else {
                        // do nothing
                    }
                }
            }
            
    }
    
    //
    //  * Add an arg to the query
    //  *
    //  * @return void
    //
    static public function GetQueryArg( $arg ) {
            if ( isset(self::$_query_args[$arg]) ) {
                return self::$_query_args[$arg];            
            } else {
                return null;
            }                
    }
    
    //
    //  * Register a hook that handles a specific URL param
    //  *
    //  * @return void
    //
    public function AddURLParamHook( $hook ) {        
        
            add_action( 'sf_get_args_from_url_params', array( $this, $hook ) );
            
    }
    
    //
    //  * Add a specific param to the current URL (or replace if already exists)
    //  * Removes the page param (to avoid confusion for the user)
    //  * Preserves the rest of the current URL as-is (if $preserve is true)
    //  *
    //  * @return url as string
    //
    static public function AddParamRemovePageFromCurrentURL( $param, $value, $preserve = true, $replace = true ) {
            $new_url = asdnURLParams::AddParamToCurrentURL( $param, $value, $preserve, $replace );
            $new_url = asdnURLParams::RemoveParamFromURL( 'sf_page', $new_url ); // remove pagination            
            return $new_url;
    }

    //
    //  * Add a shortcode function to the class
    //  *
    //  * @return void
    //
    public function AddShortcode( $shortcode, $function ) {  
            add_shortcode( $shortcode, array( $this, $function ) );            
    }
    
    //
    //  * Creates script that allows dropdowns to automatically reload the page with the new filter
    //  *
    //  * @return string (html markup)
    //
    static public function GetDropDownFilterScript( $element_id ) {
        
        $script = ' onchange="window.location = document.getElementById(\''.$element_id.'\').value;" ';
        return $script;
        
    }
    
    //
    //  * Register a URL param that can be removed by the user
    //  *
    //  * @return void
    //
    static protected function AddFilterRemovalOption( $param, $dependent = '', $label = ''  ) {        
            if ( $dependent != '' || $label != '' ) {
                self::$_filter_removal_options[] = array(
                    'param'        => $param,
                    'dependent'    => $dependent,
                    'label'        => $label,
                );
            } else {
                self::$_filter_removal_options[] = $param;
            }
    }

    
    
    //===================================================
    //                 PAGINATION TOOLS                 =
    //===================================================
   
    //
    //  * Hook to extract pagination param from URL and add to query args
    //  *
    //  * @return void
    //
    public function PaginationURLParamHook() {
            $sf_page = asdnURLParams::GetURLParam( 'sf_page' );
            if ( $sf_page ) {
                self::AddQueryArg( 'paged', $sf_page );
            } 
    }    
    
    //
    //  * Display pagination information
    //  *
    //  * @return html markup
    //
    protected function GetPaginationInformation( $pagination ) {
        
            $o = '';
            
                if ( $pagination['total_pages'] > 1 ) {
                    $o .= '<div class="sf_pagination">';
                        $o .= $pagination['start_post'].'-'.$pagination['end_post'].' of '.$pagination['total_posts'].' results';            
                    $o .= '</div>';
                } else if ( $pagination['total_pages'] == 1 ) {
                    $o .= '<div class="sf_pagination">';
                        if ( $pagination['total_posts'] == 1 ) {
                            $o .= $pagination['total_posts'].' result';            
                        } else if ( $pagination['total_posts'] > 1 ) {
                            $o .= $pagination['total_posts'].' results';
                        }
                    $o .= '</div>';                                        
                }
                
            return $o;
            
    }
    
    //
    //  * Display pagination links
    //  *
    //  * @return html markup
    //
    protected function GetPaginationLinks( $pagination, $links = 1 ) {
        
            $o = $html = '';
        
            if ( $pagination['total_pages'] > 1 ) {
                    
                // calculate and display pagination links
                $start = ( ( $pagination['page'] - $links ) > 0 ) ? $pagination['page'] - $links : 1;
                $end = ( ( $pagination['page'] + $links ) < $pagination['last_page'] ) ? $pagination['page'] + $links : $pagination['last_page'];

                $html       = '<ul class="sf_pagination_numbers">';

                if ( $pagination['prev_page'] != '' ) {
                    $class      = ( $pagination['page'] == 1 ) ? 'class="disabled"' : '';
                    $html       .= '<li ' . $class . '><a href="'.asdnURLParams::AddParamToCurrentURL( 'sf_page', $pagination['prev_page'] ).'">&lt;</a></li>';
                }

                if ( $start > 1 ) {
                    $html   .= '<li><a href="'.asdnURLParams::AddParamToCurrentURL( 'sf_page', $pagination['first_page'] ).'">1</a></li>';
                    $html   .= '<li class="disabled"><span>...</span></li>';
                }

                for ( $i = $start ; $i <= $end; $i++ ) {
                    if ( $pagination['page'] == $i ) {
                        $html .= '<li class="active">'.$i.'</li>';
                    } else {
                        $html .= '<li><a href="'.asdnURLParams::AddParamToCurrentURL( 'sf_page', $i ).'">' . $i . '</a></li>';
                    }
                }

                if ( $end < $pagination['last_page'] ) {
                    $html   .= '<li class="disabled"><span>...</span></li>';
                    $html   .= '<li><a href="'.asdnURLParams::AddParamToCurrentURL( 'sf_page', $pagination['last_page'] ).'">' . $pagination['last_page'] . '</a></li>';
                }

                if ( $pagination['next_page'] != '' ) {
                    $class      = ( $pagination['page'] == $pagination['last_page'] ) ? 'class="disabled"' : '';
                    $html       .= '<li ' . $class . '><a href="'.asdnURLParams::AddParamToCurrentURL( 'sf_page', $pagination['next_page'] ).'">&gt;</a></li>';
                }

                $html       .= '</ul>';
                    
            }

            $o .= $html;
            
            return $o; 
            
    }
    
    
    //===================================================
    //                    SORT TOOLS                    =
    //===================================================
   
    //
    //  * Hook to extract sort param from URL and add to query args
    //  *
    //  * @return void
    //
    public function SortURLParamHook() {
            $sf_sort = asdnURLParams::GetURLParam( 'sf_sort' );
            if ( $sf_sort ) {
                switch ($sf_sort) {
                    case 'NameASC':
                        self::AddQueryArg( 'orderby', 'title' );
                        self::AddQueryArg( 'order', 'ASC' );
                        break;
                    case 'NameDESC':
                        self::AddQueryArg( 'orderby', 'title' );
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
    public function SortFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                'unselected' => '',
            ), $atts));
            
            
            // determine which sort option is currently selected
            $current_sort = asdnURLParams::GetURLParam('sf_sort');
            
            // sort dropdown markup
            $sort_dropdown = '<select id="sf_sort_field" '.self::GetDropDownFilterScript('sf_sort_field').' >';
                
                if ( $unselected != '' ) {
                    // option blank
                    $sort_dropdown .= '<option '; 
                    $sort_dropdown .= ' disabled value ';
                    $sort_dropdown .= ( ! $current_sort ) ? ' selected ' : '';
                    $sort_dropdown .= ' >'.$unselected.'</option>';                                     
                }

                // option NameASC
                $NameASC_URL = self::AddParamRemovePageFromCurrentURL( 'sf_sort', 'NameASC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$NameASC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'NameASC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Name: A-Z</option>';

                // option NameASC
                $NameDESC_URL = self::AddParamRemovePageFromCurrentURL( 'sf_sort', 'NameDESC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$NameDESC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'NameDESC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Name: Z-A</option>';
                
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
   
    /************************EXAMPLE*********************
    //
    //  * Hook to extract country param from URL and add to query args
    //  *
    //  * @return void
    //
    public function CountryURLParamHook() {
            $sf_country = asdnURLParams::GetURLParam( 'sf_country' );
            if ( $sf_country ) {
                // get country param and set query args
                $tax_query = array(
                    array(
                        'taxonomy' => 'country', 
                        'field' => 'name',       
                        'terms' => urldecode( $sf_country ),
                        'include_children' => true,       
                        'operator' => 'IN'                
                    )                    
                );                
                self::AddQueryArg( 'tax_query', $tax_query, true );
            }
    }
            
    //
    //  * Shortcode to display the country field
    //  *
    //  * @return html markup
    //
    public function CountryFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'post_type'         => 'manufacturer',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'country',
                    'hide_empty' => true,
            );
            $terms = get_terms( $terms_args );
            
            // determine which country option is currently selected
            $current_country = asdnURLParams::GetURLParam('sf_country');
            
            // drop down mark-up
            $country_dropdown = '<select id="sf_country_field" '.self::GetDropDownFilterScript('sf_country_field').' >';
                
                // option blank
                $country_dropdown .= '<option '; 
                $country_dropdown .= ' disabled value selected ';
                    // no need for individual "selected" option on drop downs
                    // because that is handled by the remove filter option
                    //$country_dropdown .= ( ! $current_country ) ? ' selected ' : '';
                $country_dropdown .= ' >-- Select --</option>';                 

                foreach( $terms as $term ) {
                    
                    $country_dropdown .= '<option '; 
                    $country_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sf_country', urlencode($term->name) ).'" ';
                        // no need for individual "selected" option on drop downs
                        // because that is handled by the remove filter option
                        // $country_dropdown .= ( $current_country == $term->name ) ? ' selected ' : '';
                    $country_dropdown .= ' >'.$term->name.'</option>';                    
 
                }
                
            $country_dropdown .= '</select>';
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';            

                $o .= '<div class="sf_country sf_dropdown">';
                    $o .= '<label>Country</label>';
                    $o .= $country_dropdown;
                $o .= '</div>';
                
            $o .= '</div>';

            return $o;
    
    }
    *****************************************************/
    
    //===================================================
    //                   SEARCH TOOLS                   =
    //===================================================

    //
    //  * Hook to extract sort param from URL and add to query args
    //  *
    //  * @return void
    //
    public function SearchURLParamHook() {
            $sf_search = asdnURLParams::GetURLParam( 'sf_search' );
            if ( $sf_search ) {   
                
                // activate special WHERE query to search all fields for search text
                // (see: CustomQueryWhereFilters) 
                self::AddQueryArg('search_all_fields_for_text', $sf_search);                                
                
            } 
    }    

    //
    //  * Shortcode to display the search field
    //  * (Note: the search field automatically clears all other params. This is a byproduct of the form action.)
    //  *
    //  * @return html markup
    //
    public function SearchFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                'search_page'       => '',                
                'placeholder'       => 'Search' 
            ), $atts));
            
            if ( $search_page == '' ) {
                $anchor = ( asdnURLParams::GetAutoTarget() ) ? '#' . asdnURLParams::GetAutoTarget() : '' ;
                $search_page = asdnURLParams::GetCurrentURL() . $anchor;
            }
            
            $search_input = '
                    <form aria-label="Search" method="get" role="search" action="'.$search_page.'" title="Type and press Enter to search.">
                        <div class="sf_search_field_container">
                            <input aria-label="Search" type="search" class="fl-search-input form-control" name="sf_search" placeholder="'.$placeholder.'" value="" onfocus="if (this.value === \'Search\') { this.value = \'\'; }" onblur="if (this.value === \'\') this.value=\'Search\';" />
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </div>
                    </form>
                    ';            
            
            // render widget
            $o = '';

                $o .= '<div class="sf_search">';                
                    $o .= $search_input;
                $o .= '</div>';
            
            return $o;    
    
    }

    
    
    //===================================================
    //                CUSTOM QUERY SQL                  =
    //===================================================

    //
    //  * Filter that adds custom SQL WHERE commands to the query 
    //  * Useful for searches and special custom filters 
    //  *
    //  * @return string (revised WHERE commands)
    //
    public function CustomQueryWhereFilters( $where, $query ) {
        global $wpdb;

        // -----------------------------------------
        // search all fields for search text
        // -----------------------------------------
        $search = esc_sql( $query->get( 'search_all_fields_for_text' ) );
        if ( $search ) {
            $where .= ' AND ( ';
            
                // search title
                $where .= $wpdb->posts.'.post_title LIKE "%'.$search.'%" ';
                
                // search content 
                $where .= ' OR '.$wpdb->posts.'.post_content LIKE "%'.$search.'%" ';

                // search meta 
                $where .= ' OR pm.meta_value LIKE "%'.$search.'%" ';
                
            $where .= ' ) ';
            
        }

        return $where;
    }
    
    //
    //  * Filter that adds custom SQL JOIN for search post meta
    //  * Useful for searches and special custom filters 
    //  *
    //  * @return string (revised JOIN commands)
    //
    public function CustomQueryJoinFilters( $join, $query ) {
        global $wpdb;

        $search = esc_sql( $query->get( 'search_all_fields_for_text' ) );
        if ( $search ) {
            // the search_all_fields_for_text option requires an inner join with the postmeta table
            $join .= " INNER JOIN $wpdb->postmeta AS pm ON ( $wpdb->posts.ID = pm.post_id ) ";
        }
        
        return $join;
    }

    //
    //  * Filter that adds a DISTINCT clause to SQL of sf queries
    //  * This prevents SQL from selecting duplicate entries
    //  *
    //  * @return string (revised DISTINCT commands)
    //
    public function NoDuplicatesInQuery( $distinct, $query ) {
        
        $sf_query = esc_sql( $query->get( 'sf_query' ) );
        if ( $sf_query ) {
            $distinct = "DISTINCT";
        }
        
	return $distinct;       
        
    }
    
    //
    //  * Function that assists when displaying a list of the United States
    //  *
    //  * @return array of states ('XX' => 'State Name')
    //
    public function GetUnitedStatesList() {
        return self::$_united_states_list;
    }
    
}
