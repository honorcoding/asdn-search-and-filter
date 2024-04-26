<?php
/**
 * CUSTOM POST TYPES 
 */

function cptui_register_my_cpts() {

	/**
	 * Post Type: Courses.
	 */

	$labels = [
		"name" => esc_html__( "Courses", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Course", "custom-post-type-ui" ),
	];

	$args = [
		"label" => esc_html__( "Courses", "custom-post-type-ui" ),
		"labels" => $labels,
		"description" => "Online Course",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "course", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions" ],
		"show_in_graphql" => false,
	];

	register_post_type( "course", $args );

	/**
	 * Post Type: Video Series.
	 */

	$labels = [
		"name" => esc_html__( "Video Series", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Video Series", "custom-post-type-ui" ),
	];

	$args = [
		"label" => esc_html__( "Video Series", "custom-post-type-ui" ),
		"labels" => $labels,
		"description" => "Online Video Series",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "video_series", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions" ],
		"show_in_graphql" => false,
	];

	register_post_type( "video_series", $args );
}

add_action( 'init', 'cptui_register_my_cpts' );



/**
 * CUSTOM POST TYPE - TAXONOMIES 
 */

function cptui_register_my_taxes() {

	/**
	 * Taxonomy: Course Providers.
	 */

	$labels = [
		"name" => esc_html__( "Course Providers", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Course Provider", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Course Providers", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'course_partner', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "course_partner",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "course_partner", [ "course" ], $args );

	/**
	 * Taxonomy: Credits.
	 */

	$labels = [
		"name" => esc_html__( "Credits", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Credits", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Credits", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'credits', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "credits",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "credits", [ "course" ], $args );

	/**
	 * Taxonomy: Course Time Frames.
	 */

	$labels = [
		"name" => esc_html__( "Course Time Frames", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Course Time Frame", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Course Time Frames", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'course_type', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "course_type",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "course_type", [ "course" ], $args );

	/**
	 * Taxonomy: Content Areas.
	 */

	$labels = [
		"name" => esc_html__( "Content Areas", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Content Area", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Content Areas", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'content_area', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "content_area",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "content_area", [ "course" ], $args );

	/**
	 * Taxonomy: Featured Courses.
	 */

	$labels = [
		"name" => esc_html__( "Featured Courses", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Featured Course", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Featured Courses", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'featured_course', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "featured_course",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "featured_course", [ "course" ], $args );

	/**
	 * Taxonomy: Grade Levels.
	 */

	$labels = [
		"name" => esc_html__( "Grade Levels", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Grade Level", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Grade Levels", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'grade_level', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "grade_level",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "grade_level", [ "course" ], $args );

	/**
	 * Taxonomy: Materials
	 */

	$labels = [
		"name" => esc_html__( "Materials", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Materials", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Materials", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'materials', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "materials",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "materials", [ "course" ], $args );

	/**
	 * Taxonomy: Presenters.
	 */

	$labels = [
		"name" => esc_html__( "Presenters", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Presenter", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Presenters", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'video_series_presenter', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "video_series_presenter",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "video_series_presenter", [ "video_series" ], $args );

	/**
	 * Taxonomy: Topics.
	 */

	$labels = [
		"name" => esc_html__( "Topics", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Topic", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Topics", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'video_series_topic', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => true,
		"rest_base" => "video_series_topic",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "video_series_topic", [ "video_series" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes' );