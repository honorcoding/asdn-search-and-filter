<?php

/* 
 * Columns and Rows Shortcodes
 */

// ----------------------------------------------------------
// ROWS & COLUMNS WIDGET 
// ----------------------------------------------------------

// USE:
// Note: width can be a fraction (2/5), a percent (40%) or a px value (125px or simply 125)
//
// [row]
//     [column width="2/5"][/column]
//     [column width="3/5"][/column]
// [/row]
//
function hc_row_widget( $atts = array(), $content = null ) {
    
	// default parameters
	extract(shortcode_atts(array(
            'class'     => '',
	), $atts));
        
        // prepare output variable
        $o = '';
        
            $o .= '<div class="hc-row '.$class.' ">';
            // include the other shortcodes for flip-box front and back
            $o .= do_shortcode( trim($content) );
            $o .= '</div>';
                
        // return output
        return $o;
        
}        
add_shortcode('row', 'hc_row_widget');
function hc_column_widget( $atts = array(), $content = null ) {
    
	// default parameters
	extract(shortcode_atts(array(
            'width'     => '',
            'class'     => '',
	), $atts));
        
        
        // calculate style from width
        if (  strpos($width, '/') !== false  ) {
            
                // handle fraction
                $params = explode('/', $width);    
                $ratio = ( (int)$params[0] / (int)$params[1] ) * 100;
                $width = (int)$ratio . '%';
                $style = ' style="flex-basis: '.$width.';" ';
            
        } else if (  strpos($width, '%') !== false  ) {           
            
                // handle percent
                $params = explode('%', $width);
                $width = (int)$params[0] . '%';
                $style = ' style="flex-basis: '.$width.';" ';
            
        } else if (  strpos($width, 'px') !== false  ) {
            
                // handle px
                $params = explode('px', $width);
                $width = (int)$params[0] . 'px';
                $style = ' style="max-width: '.$width.';" ';
            
        } else if (  is_numeric($width)  ) {
            
                // handle simple number 
                $width = (int)$width . 'px';
                $style = ' style="max-width: '.$width.';" ';
            
        } else {
            
                // handle gobbledegook 
                $style= '';
            
        }
        
        // prepare output variable
        $o = '';
        
            $o .= '<div class="hc-column '.$class.' " '.$style.' >';
            
                    // include the content
                    $o .= do_shortcode( trim($content) );
              
            $o .= '</div>';

        // return output
        return $o;
        
}        
add_shortcode('column', 'hc_column_widget');
