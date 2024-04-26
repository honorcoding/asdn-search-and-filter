<?php
 
/**
 * Allows for efficient manipulation of URL Parameters
 *
 * @class asdnURLParams
 */
class asdnURLParams {
    
    static protected $_auto_target;
    
    //
    //  * Set a page anchor to target on URL calls
    //  * e.g. /mypage/#anchor
    //  *
    //  * @return void
    //
    static public function AutoTarget( $anchor ) {
        
            // set the page anchor to auto-target
            self::$_auto_target = $anchor;
            
    }    
    
    //
    //  * Get the page anchor to target on URL calls
    //  * e.g. /mypage/#anchor
    //  *
    //  * @return void
    //
    static public function GetAutoTarget() {
        
            // set the page anchor to auto-target
            if ( isset( self::$_auto_target ) && self::$_auto_target != '' ) {
                return self::$_auto_target;
            } else {
                return null;
            }            
            
    }    
    
    //
    //  * Set a page anchor to target on URL calls
    //  * e.g. /mypage/#anchor
    //  *
    //  * @return void
    //
    static public function ClearAutoTarget() {
        
            // set the page anchor to auto-target
            self::$_auto_target = '';
            
    }    
    
    //
    //  * Register a specific URL param with Wordpress
    //  *
    //  * @return void
    //
    static public function RegisterURLParam( $param ) {
        
            // register custom url param with Wordpress
            global $wp; 
            $wp->add_query_var( $param );  
            
    }
    
    //
    //  * Get a specific param from the current URL
    //  *
    //  * @return string (or null)
    //
    static public function GetURLParam( $param ) {
        
            // if param exists, then return param value, else return null
            $value = get_query_var( $param ) != '' ? get_query_var( $param ) : null;
            if ( !$value ) {
                $value = isset( $_GET[$param] ) ? $_GET[$param] : null;
            }
            
            return $value;
            
    }
    
    //
    //  * Add a specific param to the current URL (or replace if already exists)
    //  *
    //  * @return url as string
    //
    static public function AddParamToCurrentURL( $param, $value, $preserve = true, $replace = true ) {
            // get current url (with all params)
            $current_url = self::GetCurrentURL();                        
            return self::AddParamToURL( $current_url, $param, $value, $preserve, $replace );
    }
    
    
    //
    //  * Add a specific param to any URL (or replace if already exists)
    //  * Preserves the rest of the current URL as-is (if $preserve is true)
    //  *
    //  *   $param = url parameter to add or replace
    //  *   $value = url value affiliated with the parameter
    //  *   $preserve = [true/false] - choose whether or not to preserve the other URL params
    //  *   $replace = [true/false] - choose whether or not to replace the value if it already exists
    //  *                           - (has no effect if $preserve == false)
    //  *
    //  *   Examples:
    //  *       self::AddParamToCurrentURL('sf_sort','ByNameUp');     
    //  *           BEFORE: https://fermag.com/?sf_page=2
    //  *           AFTER: https://fermag.com/?sf_page=2&sf_sort=ByNameUp
    //  *                                                               
    //  *       self::AddParamToCurrentURL('sf_search','test',true,false);     
    //  *           BEFORE: https://fermag.com/?sf_page=2
    //  *           AFTER: https://fermag.com/?sf_search=test     // note: got rid of other URL params
    //  *
    //  * @return url as string
    //
    static public function AddParamToURL( $url, $param, $value, $preserve = true, $replace = true ) {
                
            // parse the current url into its respective parts
            $parsed_url = parse_url( $url );
            
            // parse the URL params into an array and modify as needed
            $queries = array();
            if ( $preserve === true ) {
                
                // get the full list of queries from the URL
                $queries = array();
                if ( isset( $parsed_url['query'] ) ) {
                    parse_str( $parsed_url['query'], $queries ); // parse queries
                }

                if ( $queries ) {
                    // queries exist. 
                    if ( isset( $queries[$param] ) ) {
                        // if the param already exists
                        if ( $replace === true ) {
                            // if replace is allowed, then replace it
                            $queries[$param] = $value;
                        }
                    } else {
                        // no param in existing query. add the param.
                        $queries[$param] = $value;
                    }
                } else {
                    // no queries exist, add the param
                    $queries[$param] = $value;
                }
                
            } else {
                
                // do not preserve the other URL params (aka queries)
                $queries[$param] = $value;
                
            }
            
            // re-combine the url params into string
            $queries_converted_to_string = array();
            foreach( $queries as $key => $value ) {
                $queries_converted_to_string[] = $key . '=' . self::GetURLSafeText( $value );
            }
            $new_query = implode( '&', $queries_converted_to_string );
            $parsed_url['query'] = $new_query;
                     
            
            // restore the url (with the new params) and return
            $new_url = self::UnparseURL( $parsed_url );
            return $new_url;
            
    }
    
    //
    //  * Returns a revised URL with a specific param removed
    //  *
    //  * @return url as string
    //
    static public function RemoveParamFromURL( $param, $url ) {
        
            // parse the current url into its respective parts
            $parsed_url = parse_url( $url );            

            // parse the list of queries from the URL
            $queries = $new_query = array();
            if ( isset($parsed_url['query']) ) {
                parse_str( $parsed_url['query'], $queries ); 
            }
            if ( $queries ) {
                // queries exist. 
                if ( isset( $queries[$param] ) ) {
                    // if the param exists, remove it from the list of URL params
                    unset( $queries[$param] );
                }
            } 
            
            if ( $queries ) {   // if params exist (after removing the requested one), 
                                // then add those params back to the url
                
                // re-combine the url params into string
                $queries_converted_to_string = array();
                foreach( $queries as $key => $value ) {
                    $queries_converted_to_string[] = $key . '=' . self::GetURLSafeText( $value );
                }
                $new_query = implode( '&', $queries_converted_to_string );
                $parsed_url['query'] = $new_query;
                        
            } else {            // if no params exist, then just get rid of the params entirely
                
                unset( $parsed_url['query'] );
                
            }
         
            // restore the url (with the new params) and return
            $new_url = self::UnparseURL( $parsed_url );
            return $new_url;
                    
    }
    
    //
    //  * Clears all params from a URL
    //  *
    //  * @return url as string
    //
    static public function RemoveAllParamsFromURL( $url ) {
        
            // parse the current url into its respective parts
            $parsed_url = parse_url( $url );
                        
            // delete all query params
            unset ( $parsed_url['query'] );
                
            // restore the url (with the new params) and return
            $new_url = self::UnparseURL( $parsed_url );
            return $new_url;
                    
    }
   
    //
    //  * Clears all params from the current URL
    //  *
    //  * @return url as string
    //
    static public function RemoveAllParamsFromCurrentURL() {
            return self::RemoveAllParamsFromURL( self::GetCurrentURL() );
    }
    

    //
    //  * Returns a URL-safe version of text (e.g. category names with ampersands, etc)
    //  *
    //  * @return string
    //
    static public function GetURLSafeText( $text ) {
            $text = str_replace( '&amp;', '&', $text );
            $text = urlencode( $text );
            return $text;
    }

    //
    //  * Returns the current URL for this page with all its URL params
    //  *
    //  * @return url as string
    //
    static public function GetCurrentURL() {
                
            // get current url (with all params)
            //global $wp;
            //$current_url = home_url(add_query_arg($_GET,$wp->request));
            // - OR -
            //$current_url = $_SERVER['REQUEST_URI'];
            // - OR -
            $current_url = home_url() . $_SERVER['REQUEST_URI'];
            return $current_url;
            
    }
    
    //
    //  * Returns a PHP-Parsed URL to its Un-Parsed format
    //  *
    //  * @return url as string
    //
    static public function UnparseURL($parsed_url) {
        
            $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
            $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
            $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
            $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
            $pass     = ($user || $pass) ? "$pass@" : '';
            $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';            
            if ( isset(self::$_auto_target) && self::$_auto_target != '' ) {
                $fragment = '#' . self::$_auto_target;
            } else {
                $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';                
            }
            $new_url = "$scheme$user$pass$host$port$path$query$fragment";
            return $new_url;
            
    }           
    
} // end: class
