<?php

class asdnCourseFilters extends asdnSearchAndFilter {
    
    //
    //  * Constructor 
    //  *
    //  * @return void
    //
    public function __construct() {
            // initialize this class when Wordpress initializes
            parent::__construct();
            add_action('init', array( $this, 'child_init' ) );        
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
            $this->AddShortcode( 'sfc_post_grid', 'CoursePostGridShortcode' );
            add_filter( 'sf_display_individual_posts', array( $this, 'DisplayIndividualPostsFilter' ), 10, 2 );
            
            // Sort
            asdnURLParams::RegisterURLParam('sfc_sort');
            $this->AddURLParamHook('CourseSortURLParamHook');
            $this->AddShortcode( 'sfc_sort_field', 'CourseSortFieldShortcode' );

                        
            // Filters
            asdnURLParams::RegisterURLParam('sfc_content_area');
            $this->AddURLParamHook('ContentAreaURLParamHook');
            $this->AddShortcode( 'sfc_content_area_field', 'ContentAreaFieldShortcode' );
            self::AddFilterRemovalOption('sfc_content_area');        
            
            asdnURLParams::RegisterURLParam('sfc_credits');
            $this->AddURLParamHook('CreditsURLParamHook');
            $this->AddShortcode( 'sfc_credits_field', 'CreditsFieldShortcode' );
            self::AddFilterRemovalOption('sfc_credits','','Credits');        
            
            asdnURLParams::RegisterURLParam('sfc_course_type');
            $this->AddURLParamHook('CourseTypeURLParamHook');
            $this->AddShortcode( 'sfc_course_type_field', 'CourseTypeFieldShortcode' );
            self::AddFilterRemovalOption('sfc_course_type');                    
            
            asdnURLParams::RegisterURLParam('sfc_course_partner');
            $this->AddURLParamHook('CoursePartnerURLParamHook');
            $this->AddShortcode( 'sfc_course_partner_field', 'CoursePartnerFieldShortcode' );
            self::AddFilterRemovalOption('sfc_course_partner');    
            
            asdnURLParams::RegisterURLParam('sfc_materials');
            $this->AddURLParamHook('MaterialsURLParamHook');
            $this->AddShortcode( 'sfc_materials_field', 'MaterialsFieldShortcode' );
            self::AddFilterRemovalOption('sfc_materials');    
            
            asdnURLParams::RegisterURLParam('sfc_featured_course');
            $this->AddURLParamHook('FeaturedCourseURLParamHook');
            $this->AddShortcode( 'sfc_new_courses_field', 'NewCoursesFieldShortcode' );
            self::AddFilterRemovalOption('sfc_featured_course');                            
            $this->AddShortcode( 'sfc_featured_courses', 'FeaturedCoursesShortcode' );
            
            asdnURLParams::RegisterURLParam('sfc_grade_level');
            $this->AddURLParamHook('GradeLevelURLParamHook');
            $this->AddShortcode( 'sfc_grade_level_field', 'GradeLevelFieldShortcode' );
            self::AddFilterRemovalOption('sfc_grade_level');    
            
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
                // get post meta
                $prefix          = get_post_meta( $post['id'], 'prefix', true );
                $crs             = get_post_meta( $post['id'], 'crs', true );
                $tuition         = get_post_meta( $post['id'], 'tuition', true );
                $view_course_url = get_post_meta( $post['id'], 'view_course_url', true );
                
                // get post terms 
                $course_partner  = self::GetPostTerms( $post['id'], 'course_partner' );
                $content_area    = self::GetPostTerms( $post['id'], 'content_area' );
                $credits         = self::GetPostTerms( $post['id'], 'credits' );
                $course_type     = self::GetPostTerms( $post['id'], 'course_type' );
                $grade_level     = self::GetPostTerms( $post['id'], 'grade_level' );
                
                $featured_course = self::GetPostTerms( $post['id'], 'featured_course', false );
                $new_course = 'No';
                foreach ( $featured_course as $fc ) {
                    if ( $fc == 'New' ) {
                        $new_course = 'Yes';
                    }
                }
                
                $data['posts'][$key]['custom_fields'] = array(
                    'course_partner'    => $course_partner,
                    'content_area'      => $content_area,
                    'prefix'            => $prefix,
                    'crs'               => $crs,
                    'credits'           => $credits,
                    'course_type'       => $course_type,
                    'grade_level'       => $grade_level,
                    'tuition'           => $tuition,
                    'view_course_url'   => $view_course_url,
                    'new_course'        => $new_course,
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
    public function CoursePostGridShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'class'             => '',
                    'posts_per_page'    => '30',
                    'no_posts_error'    => 'No results found.',
                    'columns'           => '2',
                    'anchor'            => '',
            ), $atts));
            
            // support meta sorts
            //$this->MetaSortQueries();
            
            // generate shortcode
            // REMOVED: (called directly. otherwise it doesn't properly recognize the child class) 
            //$post_grid_shortcode = '[sf_post_grid post_type="course" class="sfc_post_grid '.$class.'" posts_per_page="'.$posts_per_page.'" no_posts_error="'.$no_posts_error.'" columns="'.$columns.'" anchor="'.$anchor.'" sort_shortcode="sfc_sort_field" ]';
            //$o = do_shortcode( $post_grid_shortcode );
            $parent_atts = array(
                    'post_type'             => 'course',
                    'class'                 => 'sfc_post_grid '.$class,
                    'posts_per_page'        => $posts_per_page,
                    'no_posts_error'        => $no_posts_error,
                    'columns'               => $columns,
                    'sort_shortcode'        => 'sfc_sort_field',    
                    'anchor'                => $anchor,                
            );
            $o = $this->PostGridShortcode( $parent_atts );
            
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
        if ( self::GetQueryArg('post_type') == 'course' ) {
                 
            $post_content = ''; 
            foreach ($posts as $post ) {
                
                // create the html markup for the course post
                $o = '';
                
                    // item container
                    $o .= '<div class="sf_body_item sfc_body_item">';
                    
                        // item title
                        $o .= '<div class="sfc_body_item_header">';
                            $o .= '<div class="sfc_content_area">'.$post['custom_fields']['content_area'].'</div>';
                            $o .= '<a class="sfc_view_course_button" href="'.$post['custom_fields']['view_course_url'].'" target="_blank">View Course</a>';
                        $o .= '</div>';
                        
                        // item body
                        $o .= '<div class="sfc_body_item_body">';
                        
                            $o .= '<div class="sfc_prefix_crs_and_new_course">';
                                $o .= '<div class="sfc_prefix_crs">'.$post['custom_fields']['prefix'].$post['custom_fields']['crs'].'</div>';
                                $o .= '<div class="sfc_new_course">';
                                    if ( $post['custom_fields']['new_course'] == 'Yes' ) {
                                        $o .= '<span class="new_text">New!</span>';
                                    }
                                $o .= '</div>';
                            $o .= '</div>';
                            
                            $o .= '<div class="sfc_post_title">'.$post['title'].'</div>';
                            
                            $o .= '<div class="sfc_credits_and_tuition">';
                            
                                $grade_level = $post['custom_fields']['grade_level'];
                                if ( $grade_level ) {
                                    $grade_level_html = '<br>Grades:&nbsp;'.$grade_level;
                                } else {
                                    $grade_level_html = '';
                                }
                                $o .= '<div class="sfc_credits">'.$post['custom_fields']['credits'].'&nbsp;Credit(s)'.$grade_level_html.'</div>';
                                //$o .= '<div class="sfc_credits">'.$post['custom_fields']['credits'].'&nbsp;Credit(s)</div>';
                                
                                $o .= '<div class="sfc_tuition">Tuition:&nbsp;'.$post['custom_fields']['tuition'].'&nbsp;<br><a href="/alaska-staff-development-network-sponsors-information/" target="_blank">(Level 1/Level 2)</a></div>';                                                                
                            $o .= '</div>';
                            
                        $o .= '</div>';
                        
                    $o .= '</div>';

                $post_content .= $o;
                
            } // end: foreach
            
        } // end: if this is manufacturer cpt
        
        return $post_content;
        
    }
    

    //===================================================
    //                COURSE SORT TOOLS                 =
    //===================================================
   
    //
    //  * Allow wordpress query to handle sorting by meta
    //  *
    //  * @return void
    //
    public function MetaSortQueries() {
        
            // add custom meta queries to the query args to support sorting by meta
            $meta_query = array(
                'content_area_clause' => array(
                    'key' => 'content_area', 
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
    public function CourseSortURLParamHook() {
            $sfc_sort = asdnURLParams::GetURLParam( 'sfc_sort' );
            if ( $sfc_sort ) {
                switch ($sfc_sort) {
                    case 'CourseTitleASC':
                        self::AddQueryArg( 'orderby', 'title' );
                        self::AddQueryArg( 'order', 'ASC' );
                        break;
                    case 'CourseTitleDESC':
                        self::AddQueryArg( 'orderby', 'title' );
                        self::AddQueryArg( 'order', 'DESC' );
                        break;
                    /*
                    case 'ContentAreaASC':
                        self::AddQueryArg( 'orderby', 'content_area_clause' );
                        self::AddQueryArg( 'order', 'ASC' );
                        break;                        
                    case 'ContentAreaDESC':
                        self::AddQueryArg( 'orderby', 'content_area_clause' );
                        self::AddQueryArg( 'order', 'DESC' );
                        break;                        
                     * 
                     */
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
    public function CourseSortFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                'unselected' => '',
            ), $atts));
            
            
            // determine which sort option is currently selected
            $current_sort = asdnURLParams::GetURLParam('sfc_sort');
            
            // sort dropdown markup
            $sort_dropdown = '<select id="sf_sort_field" '.self::GetDropDownFilterScript('sf_sort_field').' >';
                
                if ( $unselected != '' ) {
                    // option blank
                    $sort_dropdown .= '<option '; 
                    $sort_dropdown .= ' disabled value ';
                    $sort_dropdown .= ( ! $current_sort ) ? ' selected ' : '';
                    $sort_dropdown .= ' >'.$unselected.'</option>';                                     
                }

                // option CourseTitleASC
                $NameASC_URL = self::AddParamRemovePageFromCurrentURL( 'sfc_sort', 'CourseTitleASC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$NameASC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'CourseTitleASC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Course Title: A-Z</option>';

                // option CourseTitleASC
                $NameDESC_URL = self::AddParamRemovePageFromCurrentURL( 'sfc_sort', 'CourseTitleDESC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$NameDESC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'CourseTitleDESC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Course Title: Z-A</option>';
                
                /*
                // option ContentAreaASC
                $ContentAreaASC_URL = self::AddParamRemovePageFromCurrentURL( 'sfc_sort', 'ContentAreaASC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$ContentAreaASC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'ContentAreaASC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Content Area: A-Z</option>';

                // option ContentAreaASC
                $ContentAreaDESC_URL = self::AddParamRemovePageFromCurrentURL( 'sfc_sort', 'ContentAreaDESC' );
                $sort_dropdown .= '<option '; 
                $sort_dropdown .= ' value="'.$ContentAreaDESC_URL.'" ';
                $sort_dropdown .= ( $current_sort == 'ContentAreaDESC' ) ? ' selected ' : '';
                $sort_dropdown .= ' >Content Area: Z-A</option>';                
                 * 
                 */
                
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
    //  * Hook to extract content area param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function ContentAreaURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_content_area = asdnURLParams::GetURLParam( 'sfc_content_area' );
                if ( $sfc_content_area ) {
                    // get content_area param and set query args
                    // get category param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'content_area', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_content_area ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );
                }
            }
    }    
    
    //
    //  * Shortcode to display the content_area field
    //  *
    //  * @return html markup
    //
    public function ContentAreaFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Content Area',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'content_area',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which content_area option is currently selected
            $current_content_area = asdnURLParams::GetURLParam('sfc_content_area');
            
            $selected_content_area = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_content_area) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_content_area = asdnURLParams::GetURLSafeText( $current_content_area );
                    break;
                }
            }            
            
            // drop down mark-up
            $content_area_dropdown = '<select id="sfc_content_area_field" '.self::GetDropDownFilterScript('sfc_content_area_field').' >';

                // option blank
                $content_area_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfc_content_area', asdnURLParams::GetCurrentURL() );
                $content_area_dropdown .= ' value="'.$url_all_removed.'" ';
                //$content_area_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_content_area', 'All' ).'" ';
                $content_area_dropdown .= ( $selected_content_area == '' ) ? ' selected ' : '';
                $content_area_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $content_area_dropdown .= '<option '; 
                    $content_area_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_content_area', $term->name ).'" ';
                    $content_area_dropdown .= (  $selected_content_area == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $content_area_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $content_area_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_content_area sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $content_area_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }     
    
    //
    //  * Hook to extract credits param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function CreditsURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_credits = asdnURLParams::GetURLParam( 'sfc_credits' );
                if ( $sfc_credits ) {
                    // get credits param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'credits', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_credits ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );                    
                }
            }
    }    
    
    //
    //  * Shortcode to display the credits field
    //  *
    //  * @return html markup
    //
    public function CreditsFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Credits',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'credits',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which credits option is currently selected
            $current_credits = asdnURLParams::GetURLParam('sfc_credits');
            
            $selected_credits = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_credits) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_credits = asdnURLParams::GetURLSafeText( $current_credits );
                    break;
                }
            }            
            
            // drop down mark-up
            $credits_dropdown = '<select id="sfc_credits_field" '.self::GetDropDownFilterScript('sfc_credits_field').' >';

                // option blank
                $credits_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfc_credits', asdnURLParams::GetCurrentURL() );
                $credits_dropdown .= ' value="'.$url_all_removed.'" ';
                $credits_dropdown .= ( $selected_credits == '' ) ? ' selected ' : '';
                $credits_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $credits_dropdown .= '<option '; 
                    $credits_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_credits', $term->name ).'" ';
                    $credits_dropdown .= (  $selected_credits == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $credits_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $credits_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_credits sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $credits_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }     
    
    //
    //  * Hook to extract course type param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function CourseTypeURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_course_type = asdnURLParams::GetURLParam( 'sfc_course_type' );
                if ( $sfc_course_type ) {
                    // get course_type param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'course_type', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_course_type ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );                    
                }
            }
    }    
    
    //
    //  * Shortcode to display the course_type field
    //  *
    //  * @return html markup
    //
    public function CourseTypeFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Course Time Frame',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'course_type',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which course_type option is currently selected
            $current_course_type = asdnURLParams::GetURLParam('sfc_course_type');
            
            $selected_course_type = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_course_type) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_course_type = asdnURLParams::GetURLSafeText( $current_course_type );
                    break;
                }
            }            
            
            // drop down mark-up
            $course_type_dropdown = '<select id="sfc_course_type_field" '.self::GetDropDownFilterScript('sfc_course_type_field').' >';

                // option blank
                $course_type_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfc_course_type', asdnURLParams::GetCurrentURL() );
                $course_type_dropdown .= ' value="'.$url_all_removed.'" ';
                $course_type_dropdown .= ( $selected_course_type == '' ) ? ' selected ' : '';
                $course_type_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $course_type_dropdown .= '<option '; 
                    $course_type_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_course_type', $term->name ).'" ';
                    $course_type_dropdown .= (  $selected_course_type == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $course_type_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $course_type_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_course_type sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $course_type_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }     
    
    //
    //  * Hook to extract course partner param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function CoursePartnerURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_course_partner = asdnURLParams::GetURLParam( 'sfc_course_partner' );
                if ( $sfc_course_partner ) {
                    // get course_partner param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'course_partner', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_course_partner ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );                    
                }
            }
    }    
    
    //
    //  * Shortcode to display the course_partner field
    //  *
    //  * @return html markup
    //
    public function CoursePartnerFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Course Provider',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'course_partner',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which course_partner option is currently selected
            $current_course_partner = asdnURLParams::GetURLParam('sfc_course_partner');
            
            $selected_course_partner = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_course_partner) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_course_partner = asdnURLParams::GetURLSafeText( $current_course_partner );
                    break;
                }
            }            
            
            // drop down mark-up
            $course_partner_dropdown = '<select id="sfc_course_partner_field" '.self::GetDropDownFilterScript('sfc_course_partner_field').' >';

                // option blank
                $course_partner_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfc_course_partner', asdnURLParams::GetCurrentURL() );
                $course_partner_dropdown .= ' value="'.$url_all_removed.'" ';
                $course_partner_dropdown .= ( $selected_course_partner == '' ) ? ' selected ' : '';
                $course_partner_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $course_partner_dropdown .= '<option '; 
                    $course_partner_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_course_partner', $term->name ).'" ';
                    $course_partner_dropdown .= (  $selected_course_partner == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $course_partner_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $course_partner_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_course_partner sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $course_partner_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }     
    
    //
    //  * Hook to extract course partner param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function MaterialsURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_materials = asdnURLParams::GetURLParam( 'sfc_materials' );
                if ( $sfc_materials ) {
                    // get course_partner param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'materials', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_materials ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );                    
                }
            }
    }    
    
    //
    //  * Shortcode to display the course_partner field
    //  *
    //  * @return html markup
    //
    public function MaterialsFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Materials',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'materials',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which course_partner option is currently selected
            $current_materials = asdnURLParams::GetURLParam('sfc_materials');
            
            $selected_materials = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_materials) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_materials = asdnURLParams::GetURLSafeText( $current_materials );
                    break;
                }
            }            
            
            // drop down mark-up
            $materials_dropdown = '<select id="sfc_materials_field" '.self::GetDropDownFilterScript('sfc_materials_field').' >';

                // option blank
                $materials_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfc_materials', asdnURLParams::GetCurrentURL() );
                $materials_dropdown .= ' value="'.$url_all_removed.'" ';
                $materials_dropdown .= ( $selected_materials == '' ) ? ' selected ' : '';
                $materials_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $materials_dropdown .= '<option '; 
                    $materials_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_materials', $term->name ).'" ';
                    $materials_dropdown .= (  $selected_materials == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $materials_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $materials_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_materials sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $materials_dropdown;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }     
    
    //
    //  * Hook to extract featured course param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function FeaturedCourseURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_featured_course = asdnURLParams::GetURLParam( 'sfc_featured_course' );
                if ( $sfc_featured_course ) {
                    // get course_partner param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'featured_course', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_featured_course ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );                    
                }
            }
    }    
    
    //
    //  * Shortcode to display the new courses field
    //  *
    //  * @return html markup
    //
    public function NewCoursesFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'See New Courses',
                    'value'             => 'New',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            $new_course_url = self::AddParamRemovePageFromCurrentURL( 'sfc_featured_course', $value );
            $new_course_button = '<a href="'.$new_course_url.'" class="sf_button">'.$label.'</a>';
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_featured_course sf_dropdown">';
                    $o .= $new_course_button;
                $o .= '</div>';

            $o .= '</div>';

            return $o;
    
    }
    
    //
    //  * Shortcode to display the featured courses 
    //  *
    //  * @return html markup
    //
    public function FeaturedCoursesShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                'posts_per_page'    => '3',
            ), $atts));
            
            // do not mess with the original query
            $original_query_args = self::$_query_args;
            
            // create a temporary query
            self::$_query_args = array(
                'post_type'         => 'course',
                'posts_per_page'    => $posts_per_page,  
                'tax_query'         => array(
                        array(
                            'taxonomy' => 'featured_course', 
                            'field' => 'name',       
                            'terms' => 'Featured',
                            'include_children' => true,       
                            'operator' => 'IN'                
                        ),               
                    ),
            );            
            $data = $this->PostGridQuery();
            
            // restore the original query
            self::$_query_args = $original_query_args;
            
            // generate html from the data
            $post_content = ''; 
            foreach ( $data['posts'] as $post ) {

                // create the html markup for the course post
                $o = '';

                    // item container
                    $o .= '<div class="sf_body_item sfc_body_item">';

                        // item title
                        $o .= '<div class="sfc_body_item_header">';
                            $o .= '<div class="sfc_content_area">'.$post['custom_fields']['content_area'].'</div>';
                            $o .= '<a class="sfc_view_course_button" href="'.$post['custom_fields']['view_course_url'].'" target="_blank">View Course</a>';
                        $o .= '</div>';

                        // item body
                        $o .= '<div class="sfc_body_item_body">';
                        
                                               
                            $o .= '<div class="sfc_prefix_crs_and_new_course">';
                                $o .= '<div class="sfc_prefix_crs">'.$post['custom_fields']['prefix'].$post['custom_fields']['crs'].'</div>';
                                $o .= '<div class="sfc_new_course">';
                                    if ( $post['custom_fields']['new_course'] == 'Yes' ) {
                                        $o .= '<span class="new_text">New!</span>';
                                    }
                                $o .= '</div>';
                            $o .= '</div>';
                            //$o .= '<div class="sfc_prefix_crs">'.$post['custom_fields']['prefix'].$post['custom_fields']['crs'].'</div>';
                            
                            $o .= '<div class="sfc_post_title">'.$post['title'].'</div>';
                            
                            $o .= '<div class="sfc_credits_and_tuition">';
                            
                                $grade_level = $post['custom_fields']['grade_level'];
                                if ( $grade_level ) {
                                    $grade_level_html = '<br>Grades:&nbsp;'.$grade_level;
                                } else {
                                    $grade_level_html = '';
                                }
                                $o .= '<div class="sfc_credits">'.$post['custom_fields']['credits'].'&nbsp;Credit(s)'.$grade_level_html.'</div>';
                                //$o .= '<div class="sfc_credits">'.$post['custom_fields']['credits'].'&nbsp;Credit(s)</div>';
                                
                                $o .= '<div class="sfc_tuition">Tuition:&nbsp;'.$post['custom_fields']['tuition'].'&nbsp;<br><a href="/alaska-staff-development-network-sponsors-information/" target="_blank">(Level 1/Level 2)</a></div>';                            
                            $o .= '</div>';
                            
                            
                        $o .= '</div>';

                    $o .= '</div>';

                $post_content .= $o;

            } // end: foreach

            
            // generate shortcode html
            $o = '';
            
            $o .= '<div class="sf_post_grid sfc_post_grid">';
                $o .= '<div class="sf_body three-column">';
                    $o .= $post_content;
                $o .= '</div>';
            $o .= '</div>';

            
            return $o;  
            
    } // end: FeaturedCoursesShortcode       
    
    
    //
    //  * Hook to extract grade level param from URL and add to query args
    //  * 
    //  * @return void
    //
    public function GradeLevelURLParamHook() {
            if ( self::GetQueryArg('post_type') == 'course' ) {
                $sfc_grade_level = asdnURLParams::GetURLParam( 'sfc_grade_level' );
                if ( $sfc_grade_level ) {
                    // get course_partner param and set query args
                    $tax_query = array(
                        array(
                            'taxonomy' => 'grade_level', 
                            'field' => 'name',       
                            'terms' => urldecode( $sfc_grade_level ),
                            'include_children' => true,       
                            'operator' => 'IN'                
                        )                    
                    );                
                    self::AddQueryArg( 'tax_query', $tax_query, true );                    
                }
            }
    }    
    
    //
    //  * Shortcode to display the course_partner field
    //  *
    //  * @return html markup
    //
    public function GradeLevelFieldShortcode( $atts = array(), $content = null ) {

            // default parameters
            extract(shortcode_atts(array(
                    'label'             => 'Grade Level',
                    'post_type'         => 'course',
                    'class'             => '',                
            ), $atts));
            
            // get a list of active terms
            $terms_args = array(
                    'taxonomy' => 'grade_level',
                    'hide_empty' => false,
            );
            $terms = get_terms( $terms_args );
            
            // determine which grade_level option is currently selected
            $current_grade_level = asdnURLParams::GetURLParam('sfc_grade_level');
            
            $selected_grade_level = '';
            foreach( $terms as $term ) {
                if ( asdnURLParams::GetURLSafeText($current_grade_level) == asdnURLParams::GetURLSafeText($term->name) ) {
                    $selected_grade_level = asdnURLParams::GetURLSafeText( $current_grade_level );
                    break;
                }
            }            
            
            // drop down mark-up
            $grade_level_dropdown = '<select id="sfc_grade_level_field" '.self::GetDropDownFilterScript('sfc_grade_level_field').' >';

                // option blank
                $grade_level_dropdown .= '<option '; 
                $url_all_removed = asdnURLParams::RemoveParamFromURL( 'sfc_grade_level', asdnURLParams::GetCurrentURL() );
                $grade_level_dropdown .= ' value="'.$url_all_removed.'" ';
                $grade_level_dropdown .= ( $selected_grade_level == '' ) ? ' selected ' : '';
                $grade_level_dropdown .= ' >All</option>';                 

                foreach( $terms as $term ) {

                    // create dropdown code 
                    $grade_level_dropdown .= '<option '; 
                    $grade_level_dropdown .= ' value="'.self::AddParamRemovePageFromCurrentURL( 'sfc_grade_level', $term->name ).'" ';
                    $grade_level_dropdown .= (  $selected_grade_level == asdnURLParams::GetURLSafeText( $term->name )  ) ? ' selected ' : '';
                    $grade_level_dropdown .= ' >'.$term->name.'</option>';                    
                    
                }
                
            $grade_level_dropdown .= '</select>'; 
            
            // render widget
            $o = '<div class="sf_sidebar_widget">';
            
                $o .= '<div class="sfc_grade_level sf_dropdown">';
                    $o .= '<label>'.$label.'</label>';
                    $o .= $grade_level_dropdown;
                $o .= '</div>';

            $o .= '</div>'; 

            return $o;
    
    }     
    
} // end class 



