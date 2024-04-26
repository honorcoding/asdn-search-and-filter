<?php

/* 
 * 
 * HANDLES CUSTOM FIELDS FOR COURSE CUSTOM POST TYPE
 * (NOTE: RELIES ON ADVANCED CUSTOM FIELDS PLUGIN)
 * 
 */

//===================================================
//=       DYNAMICALLY POPULATE SELECT FIELDS        =
//===================================================

// ***  COURSE PARTNERS (aka COURSE PROVIDERS) - CUSTOM FIELD ***
function acf_load_course_partner_field_choices( $field ) {
    
    // reset choices
    $field['choices'] = array();
    
    // get the terms that will be used as choices
    $terms_args = array(
            'taxonomy' => 'course_partner',
            'hide_empty' => false,
    );
    $terms = get_terms( $terms_args );

    // extract only the term names into an array
    $choices = array();
    foreach( $terms as $term ) {
        $choices[] = $term->name; 
    }
            
    // remove any unwanted white space
    $choices = array_map('trim', $choices);

    
    // loop through array and add to field 'choices'
    if( is_array($choices) ) {
        
        foreach( $choices as $choice ) {
            
            $field['choices'][ $choice ] = $choice;
            
        }
        
    }
    

    // return the field
    return $field;
    
}
add_filter('acf/load_field/name=course_partner', 'acf_load_course_partner_field_choices');

// ***  CREDITS - CUSTOM FIELD         *** 
function acf_load_credits_field_choices( $field ) {
    
    // reset choices
    $field['choices'] = array();
    
    // get the terms that will be used as choices
    $terms_args = array(
            'taxonomy' => 'credits',
            'hide_empty' => false,
    );
    $terms = get_terms( $terms_args );

    // extract only the term names into an array
    $choices = array();
    foreach( $terms as $term ) {
        $choices[] = $term->name; 
    }
            
    // remove any unwanted white space
    $choices = array_map('trim', $choices);

    
    // loop through array and add to field 'choices'
    if( is_array($choices) ) {
        
        foreach( $choices as $choice ) {
            
            $field['choices'][ $choice ] = $choice;
            
        }
        
    }
    

    // return the field
    return $field;
    
}
add_filter('acf/load_field/name=credits', 'acf_load_credits_field_choices');

// ***  COURSE TYPES (aka COURSE TIME FRAMES) - CUSTOM FIELD    ***
function acf_load_course_type_field_choices( $field ) {
    
    // reset choices
    $field['choices'] = array();
    
    // get the terms that will be used as choices
    $terms_args = array(
            'taxonomy' => 'course_type',
            'hide_empty' => false,
    );
    $terms = get_terms( $terms_args );

    // extract only the term names into an array
    $choices = array();
    foreach( $terms as $term ) {
        $choices[] = $term->name; 
    }
            
    // remove any unwanted white space
    $choices = array_map('trim', $choices);

    
    // loop through array and add to field 'choices'
    if( is_array($choices) ) {
        
        foreach( $choices as $choice ) {
            
            $field['choices'][ $choice ] = $choice;
            
        }
        
    }
    

    // return the field
    return $field;
    
}
add_filter('acf/load_field/name=course_type', 'acf_load_course_type_field_choices');

// ***  CONTENT AREAS - CUSTOM FIELD   *** 
function acf_load_content_area_field_choices( $field ) {
    
    // reset choices
    $field['choices'] = array();
    
    // get the terms that will be used as choices
    $terms_args = array(
            'taxonomy' => 'content_area',
            'hide_empty' => false,
    );
    $terms = get_terms( $terms_args );

    // extract only the term names into an array
    $choices = array();
    foreach( $terms as $term ) {
        $choices[] = $term->name; 
    }
            
    // remove any unwanted white space
    $choices = array_map('trim', $choices);

    
    // loop through array and add to field 'choices'
    if( is_array($choices) ) {
        
        foreach( $choices as $choice ) {
            
            $field['choices'][ $choice ] = $choice;
            
        }
        
    }
    

    // return the field
    return $field;
    
}
add_filter('acf/load_field/name=content_area', 'acf_load_content_area_field_choices');
