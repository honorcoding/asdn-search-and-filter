<?php

// get post meta
function asdn_get_meta( $post_id, $meta_key ) {        
        return get_post_meta( $post_id, $meta_key, true );    
}

// update post meta 
function asdn_update_meta( $post_id, $meta_key, $new_meta_value ) {
    
        $success = false;
    
        $old_meta_value = get_post_meta( $post_id, $meta_key, true );
        if ( $old_meta_value ) {
            
                // if already exists, then update
                $results = update_post_meta( $post_id, $meta_key, $new_meta_value, $old_meta_value );
                if ( $results ) {
                    $success = true;
                }
                
        } else {
            
                // if not already exists, then create 
                $results = add_post_meta( $post_id, $meta_key, $new_meta_value, true );
                if ( $results ) {
                    $success = true;
                }
                
        }
        
        return $success;

}
