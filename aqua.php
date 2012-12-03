<?php 
namespace aqua;

/**
 * aqua.php   Aqua is an experimental PHP MVC framework where
 *            where content data is stored in JSON files. It
 *            is designed to be extremely light and flexible.
 * @link      http://github.com/ryanve/aqua
 * @author    Ryan Van Etten <@ryanve>
 * @version   0.x
 * @license   MIT
 */

\file_exists( 'custom.php' ) and include_once( 'custom.php' );

# CORE FUNCTIONS 

/**
 * Convert a function name or class name from a namespace into a 
 * fully-qualified name. In other words, prefix it with the namespace.
 * @param   string   A local function name or class name.
 * @param   string=  The namespace. Defaults to the current namespace.
 */
if ( ! \function_exists( __NAMESPACE__ . '\\ns' ) ) {
    function ns ( $name, $ns = null ) {
        $ns or $ns = __NAMESPACE__;
        return $ns . '\\' . \ltrim( $name, '\\' );
    }
}

/**
 * Check if a function name or class name exists in the current namespace.
 * @param   string   $name
 * @param   string=  $what
 * @return  bool
 */
if ( ! \function_exists( __NAMESPACE__ . '\\exists' ) ) {
    function exists ( $name, $what = 'function' ) {
        return \call_user_func( $what . '_exists', ns($name) );
    }
}

if ( ! exists( '\\lslash' ) ) {
    function lslash ( $s, $chars = '/' ) {
        return $chars . \ltrim( $s, $chars );
    }
}

if ( ! exists( '\\rslash' ) ) {
    function rslash ( $s, $chars = '/' ) {
        return \rtrim( $s, $chars ) . $chars;
    }
}

/**
 * Join paths or URI parts using a single front slash as the glue.
 * @return  string
 */
if ( ! exists( '\\slash_join' ) ) {
    function slash_join () {// ($s1, $s2, ...)
        return \array_reduce( \func_get_args(), function ( $result, $curr ) {
            return $result ? \rtrim( $result, '/' ) . '/' . \ltrim( $curr, '/' ) : $curr;
        }, '');
    }
}

/**
 * Convert a path into a URI
 * @return string
 */
if ( ! exists( '\\to_uri' ) ) {
    function to_uri ( $path, $scheme = 'http' ) {
        $uri = $scheme  ? $scheme . '://' : '//';
        $uri .= $_SERVER['SERVER_NAME'] . '/';
        $uri .= \ltrim( \str_replace( $_SERVER['DOCUMENT_ROOT'], '', $path ), '/' );
        return $uri;
    }
}

if ( ! exists( '\\json_update' ) ) {
    function json_update ( $path, $callback ) {

        $data = \file_exists( $path ) ? \file_get_contents( $path ) : '';
        $data = empty($data) ? array() : \json_decode( $data, true );
        $params = \array_slice( func_get_args(), 2 );
        \array_unshift( $params, $data );
        $result = \call_user_func_array( $callback, $params );

        if ( empty($data) || $result !== $data )
        {
            $data = \json_encode( \is_object($result) || \is_array($result) ? $result : $data );
            \file_put_contents( $path, $data, LOCK_EX );
        }
        return $result; 
    }
}

if ( ! exists( '\\locate_file' ) ) {
    function locate_file () {# $haystack_path, $needle1, $needle2 ...
        $filenames = func_get_args();
        $dir = \array_shift( $filenames );
        foreach ( $filenames as $filename ) {
            $filename = slash_join($dir, $filename); # convert to path
            if ( \file_exists($filename) )
                return $filename; # full path to file
        }
    }
}

if ( ! exists( '\\load_html' ) ) {
    function load_html ( $file ) {
        \ob_start(); 
        include_once ($file);
        $html = \ob_get_contents();
        \ob_end_clean();
        return $html;
    }
}

if ( ! exists( '\\load_json' ) ) {
    function load_json ( $file, $assoc = false ) {
        if ( empty($file) || ! \is_readable($file) || \is_dir($file) )
            return false;
        return \json_decode( \file_get_contents( $file ), (bool) $assoc );
    }
}

if ( ! exists( '\\insert_data' ) ) {
    function insert_data ( $html, $data, $prefix = '' ) {
        if ( $html && $data )
            foreach ( $data as $k => $v )
                $html = \str_replace( '{{' . $prefix . $k . '}}', $v, $html );
        return $html;
    }
}

/**
 * sanitize()                        Sanitize a string, with options.
 *
 * @param   string|mixed    $str     is the string you want to sanitize
 * @param   string          $space   is the whitespace replacement
 * @param   callback        $filter  is an optional callback to apply to $str
 * @return  string
 */
if ( ! exists( '\\sanitize' ) ) {
    function sanitize ( $str, $space = '-', $filter = 'mb_strtolower' ) {

        if ( ! $str || ! is_string($str) || !( $str = \trim($str) ) )
            return \is_string($str) ? $str : '';

        if ( $filter && \is_callable($filter) )
            $str = \call_user_func($filter, $str);

        if ( \is_string($space) ) # replace inner whitespace chars w/ $space
            $str = \preg_replace('/[ \s\t\n\r\0\x0B]+/', $space, $str);

        # remove entities, then octets, then anything not alphanumeric|underscore|space|dash
        return \preg_replace('/&.+?;|%([a-fA-F0-9][a-fA-F0-9])|[^\w\s-]/', '', $str);
    }
}

/**
 * @param   object|array   $r   receiver
 * @param   object|array   $s   supplier
 */
if ( ! exists( '\\aug' ) ) {
    function aug ( &$r, $s ) {

        if ( $r && \is_object($r) )
            foreach ( $s as $k => $v )
                $r->{ $k } = $v;

        elseif ( \is_array($r) )
            foreach ( $s as $k => $v )
                $r[ $k ] = $v;

        return $r;
    }
}

/**
 * Check if all of B's keys are present in A
 * @param    object|array  $a
 * @param    object|array  $b
 * @return   boolean
 * @example  has_all( $a, $b )
 * @example  has_all( $a, $k1, $k2, ... )
 */
if ( ! exists( '\\has_all' ) ) {
    function has_all ( $a, $b = null ) {

        if ( \is_array($b) || \is_object($b) )
            $b = \array_keys( (array) $b );
        else $b = \array_slice( \func_get_args(), 1 );
        
        $a = (array) $a;
        foreach ( $b as $key )
            if ( ! isset( $a[$key] ) )
                return false;

        return true;
    }
}

if ( ! exists( 'params' ) ) {
    function params ( $params = null ) {
        $output = array();
        null === $params and $params = $_SERVER['QUERY_STRING'];
        \is_string( $params ) and \parse_str( $params, $output );
        return (object) $output; # convert to object
    }
}

if ( ! exists( 'hasher' ) ) {
    function hasher () {
        # return a new data() method tied to a fresh hash
        return function ( $key = null, $value = null ) {

            static $hash;  # php.net/manual/en/language.variables.scope.php
            isset( $hash ) or $hash = array();
            
            if ( \func_num_args() > 1 )
                return $hash[ $key ] = $value; # set

            if ( \is_scalar($key) ) 
                return $hash[ $key ];          # get
                
            if ( null === $key )
                return $hash;                  # get all
                
            foreach ( $key as $k => $v )       # set multi
                $hash[ $k ] = $v;
            return $hash; 
        };
    }
}


/**
 * Get or set arbitrary data.
 */
if ( ! exists( 'data' ) ) {
    function data ( $key = null, $value = null ) {
        static $data;  # php.net/manual/en/language.variables.scope.php
        isset( $data ) or $data = hasher();
        return \call_user_func_array( $data, func_get_args() );
    }
}

/**
 * Get or set paths.
 */
if ( ! exists( 'paths' ) ) {
    function paths ( $key = null, $value = null ) {
        static $data;  # php.net/manual/en/language.variables.scope.php
        isset( $data ) or $data = hasher();
        return \call_user_func_array( $data, func_get_args() );
    }
}

/**
 * Get or set URIs.
 */
if ( ! exists( 'uris' ) ) {
    function uris ( $key = null, $value = null ) {
        static $data;  # php.net/manual/en/language.variables.scope.php
        isset( $data ) or $data = hasher();
        return \call_user_func_array( $data, func_get_args() );
    }
}

if ( ! exists( 'run' ) ) {
    function run ( $params = null ) {

        static $ran;
        if ( true === $params )
            if ( true === $ran ) return;
            else $params = null;
        $ran = true;

        $params = params( $params );
        if ( ! isset( $params->file ) )
            return;
            
        $paths = (object) paths();
        $uris  = (object) uris();

        # queries should be like: `file=2012/headline/index.json`
        $params->path = rslash( \dirname( $params->file ) ); # relative
        $params->file = slash_join( $paths->root, $params->file );
        
        if ( ! \is_readable($params->file) )
            return;


        
        # canonical url to current content
        $uris->url = slash_join( $uris->root, $params->path );

        $type  = null;
        $name  = null;
        $year  = null;
        $temp  = null;
        $feed  = array();
        $html  = '';
        
        $data = load_json( $params->file );

        if ( \is_array($data) )
            $data = (object) $data;
        elseif ( ! \is_object($data) )
            return;

        $update = $data->moddate === 'true';

        if ( $update || $data->url !== $uris->url || ! has_all( $data, 'url', 'moddate', 'title', 'class' ) ) {
            $data = json_update($params->file, function ( $o, $url ) {
                $slug = \basename( \rtrim($url, '/') );
                $defaults = array(
                    'url' => $url
                  , 'moddate' => \date( 'Y-m-d' )
                  , 'title' => \str_replace( array('-', '_'), ' ', $slug )
                  , 'class' => ''
                );
                return aug( $defaults, $o );
            }, $uris->url );
        }

        if ( $update && $data->tags && \is_dir($paths->tag) ) {

            \is_string( $data->tags ) and $data->tags = \explode( ',', $data->tags );
            $data->tags = \array_map( 'trim', \array_filter( $data->tags, 'is_string') );
            $data->tags = \array_unique( \array_filter( $data->tags, 'strlen' ) );
            
            foreach ( $data->tags as $tag ) {

                $tag = sanitize($tag);
                if ( empty($tag) )
                    continue;
                $temp = slash_join($paths->tag, $tag);

                if ( \is_dir($temp) || \mkdir($temp) ) {
                    $temp = slash_join( $temp, $params->file );
                    $data = json_update($temp, function ( $o, $tag, $uris, $paths, $params ) {
                        $o->type = 'tag';
                        $o->moddate = date('Y-m-d');
                        $o->url = slash_join( $uri->tag, $tag );
                        unset( $o->links );
                        unset( $o->order );
                        \is_array( $o->order ) or $o->order = array();
                        \array_push( $o->order, $params->self );
                        $o->order = \array_unique( $o->order );
                        return $o;
                    }, $tag, $uris, $paths, $params);
                }
            }
        }
        
        # store the data to the hash for access from views
        data( $data );

        if ( isset( $data->order ) ) {

            $html    = load_html( locate_file($paths->views, "archive-{$data->type}.php", 'archive.php') );
            $article = load_html( locate_file($paths->views, "excerpt-{$data->type}.php", 'excerpt.php') );
            $feed = '';
            $f = null;

            foreach ( $data->order as $i => $u ) {
                if ( $u ) {
                    $f = slash_join( \dirname($params->file), $u, \basename($params->file) );
                    $u = load_json( \file_exists($f) ? $f : slash_join($paths->root, $u, $params->file) );
                    $u and $feed .= insert_data($article, $u);
                }
            }
            
            $html = insert_data( $html, array('feed' => $feed) );
            $html = insert_data( $html, $data );
            $html = insert_data( $html, $uris, 'uri.' );

        } else {
            $html = load_html( locate_file( $paths->views, "singular-{$data->type}.php", 'singular.php' ) );
            $html = insert_data( $html, $data );
            $html = insert_data( $html, $uris, 'uri.' );
        }

        echo $html;

    }
}

# DEFAULT PATHS / URIS
\call_user_func(function () {

    $paths = (object) paths();
    $uris  = (object) uris();

    $paths->root  or $paths->root  = \dirname( __DIR__ );
    $paths->tag   or $paths->tag   = slash_join( $paths->root, 'tag' );
    $paths->views or $paths->views = slash_join( \is_dir('views') ? __DIR__ : $paths->root, 'views' );

    $uris->home or $uris->home = slash_join( 'http://' . $_SERVER['SERVER_NAME'] );
    $uris->root or $uris->root = rslash( to_uri( $paths->root ) );
    $uris->tag  or $uris->tag  = rslash( to_uri( $paths->tag ) );
    
    paths( $paths );
    uris( $uris );
    paths( \basename(__FILE__), __FILE__ );

});


# INITIALIZE
run(true);

#end