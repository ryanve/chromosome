<?php 
namespace aqua;

/**
 * aqua.php   Aqua is a PHP template engine that generates views
 *            based on content data stored in JSON files.
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

/**
 * @param   string   $str
 */
if ( ! exists( 'e' ) ) {
    function e ( $str ) {
        echo $str;
    }
}

if ( ! exists( 'lslash' ) ) {
    function lslash ( $s, $chars = '/' ) {
        return $chars . \ltrim( $s, $chars );
    }
}

if ( ! exists( 'rslash' ) ) {
    function rslash ( $s, $chars = '/' ) {
        return \rtrim( $s, $chars ) . $chars;
    }
}

/**
 * Join paths or URI parts using a single front slash as the glue.
 * @return  string
 */
if ( ! exists( 'slash_join' ) ) {
    function slash_join () {// ($s1, $s2, ...)
        return \array_reduce( \func_get_args(), function ( $result, $curr ) {
            return $result ? \rtrim( $result, '/' ) . '/' . \ltrim( $curr, '/' ) : $curr;
        }, '');
    }
}

/**
 * mtime()               Get the modified time of a file or a directory. For directories,
 *                       it gets the modified time of the most recently modified file.
 * @param   string       $path     Full path to directory or file.
 * @param   string       $format   Date string for use with date()
 * @return  number|string|null
 */
if ( ! exists( 'mtime' ) ) {
    function mtime ( $path, $format = null  ) {
        $time = null;
        if ( \is_string($path) && \is_readable($path) ) {
            if ( \is_dir($path) ) {
                $path = \rtrim($path, '/');
                foreach ( \scandir($path) as $file ) {
                    if ( '.' !== \substr($file, 0, 1) ) {
                        $temp = mtime($path . '/' . $file);
                        $temp > $time and $time = $temp;
                    }
                }
            } elseif ( \file_exists($path) ) {
                $time = \filemtime($path);
            }
        }
        return $time && $format ? \date($format, $time) : $time;
    }
}

/**
 * Convert a path into a URI
 * @return string
 */
if ( ! exists( 'to_uri' ) ) {
    function to_uri ( $path, $scheme = 'http' ) {
        $uri = $scheme  ? $scheme . '://' : '//';
        $uri .= $_SERVER['SERVER_NAME'] . '/';
        $uri .= \ltrim( \str_replace( $_SERVER['DOCUMENT_ROOT'], '', $path ), '/' );
        return $uri;
    }
}

if ( ! exists( 'json_update' ) ) {
    function json_update ( $path, $callback ) {
        if ( ! \file_exists( $path ) )
            return;
        $data = \file_get_contents( $path );
        $data = $data ? \json_decode( $data, true ) : array();
        $params = \array_slice( \func_get_args(), 2 );
        \array_unshift( $params, $data );
        $result = \is_array( $callback ) ? $callback
            : \call_user_func_array( $callback, $params );
        if ( ! $data || $result !== $data ) {
            \is_object($result) || \is_array($result) or $result = '';
            \file_put_contents( $path, $result ? \json_encode($result) : '', LOCK_EX );
        }
        return $result; 
    }
}

/**
 *
 * @return  string|null
 */
if ( ! exists( 'locate_file' ) ) {
    function locate_file () {# $haystack_path, $needle1, $needle2 ...
        $filenames = \func_get_args();
        $dir = \array_shift( $filenames );
        foreach ( $filenames as $filename ) {
            $filename = slash_join($dir, $filename); # convert to path
            if ( \file_exists($filename) )
                return $filename; # full path to file
        }
    }
}

/**
 * @param   string  $file
 * @return  string
 */
if ( ! exists( 'load_html' ) ) {
    function load_html ( $file ) {
        \ob_start(); 
        include_once ($file);
        $html = \ob_get_contents();
        \ob_end_clean();
        return $html;
    }
}

/**
 * @param   string    $file
 * @param   boolean=  $assoc
 * @return  mixed
 */
if ( ! exists( 'load_json' ) ) {
    function load_json ( $file, $assoc = false ) {
        if ( empty($file) || ! \is_readable($file) || \is_dir($file) )
            return false;
        return \json_decode( \file_get_contents( $file ), (bool) $assoc );
    }
}

if ( ! exists( 'insert_data' ) ) {
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
if ( ! exists( 'sanitize' ) ) {
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
if ( ! exists( 'aug' ) ) {
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
 * @param   object|array   $r     receiver
 * @param   object|array   $defs  defaults
 */
if ( ! exists( 'defaults' ) ) {
    function defaults ( &$r, $defs ) {
        $defs = (array) $defs;
        foreach ( $r as $k => $v )
            $defs[ $k ] = $v;
        return \is_object($r) ? (object) $defs : $defs;
    }
}

/**
 * Escape a string for use in html (such as in html attributes).
 * @param   string|mixed  $value
 * @return  string
 */
if ( ! exists( 'esc' ) ) {
    function esc ( $value ) {
        if ( ! ($value = (string) $value) )
            return $value;
        return \htmlentities( $value, ENT_QUOTES, null, false );
    }
}


/**
 * @param   object|array   $r     receiver
 * @param   object|array   $defs  defaults
 */
if ( ! exists( 'meta' ) ) {
    function meta ( $name, $data = null ) {
        if ( $name ) {
            $data === null and $data = data(); 
            $data = (array) $data;
            $data = esc( $data[$name] );
            if ( $data )
                return '<meta name='. $name .' content=\'' . $data . '\'>' . "\n";
        }
        return '';
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
if ( ! exists( 'has_all' ) ) {
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
        return function ( $key = null, $value = '' ) {

            static $hash;  # php.net/manual/en/language.variables.scope.php
            isset( $hash ) or $hash = array();
                
            if ( \is_scalar($key) )
                return 1 == \func_num_args() ? $hash[ $key ] # get
                    : ( $hash[ $key ] = $value );            # set

            if ( $key )
                foreach ( $key as $k => $v )    # set multi
                    $hash[ $value . $k ] = $v;  # $value becomes opt_prefix

            return $hash; # get all
        };
    }
}


/**
 * Get or set data.
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

/**
 * Add or remove actions.
 */
if ( ! exists( 'action' ) ) {
    function action ( $key = null, $callback = null, $op = null ) {
    
        static $hash;
        isset( $hash ) or $hash = array();

        if ( !($n = \func_num_args()) )
            return $hash;
        if ( ! \is_scalar($key) )
            return;

        $hash[ $key ] or $hash[ $key ] = array();
        $params = 1 == $n ? array() : $callback;

        if ( \is_array($params) )
            foreach ( $hash[ $key ] as $fn ) # fire
                $fn and \call_user_func_array($fn, $params);
        elseif ( false === $callback ) # remove all
            unset( $hash[ $key ] );
        elseif ( 0 === $op )       # set early
            \array_unshift( $hash[ $key ], $callback );
        elseif ( false !== $op )   # set normal
            $hash[ $key ][] = $callback;
        elseif ( $hash[ $key ] ) # op was false => remove
            foreach ( $hash[ $key ] as $i => $fn )
                $fn === $callback and \array_splice( $hash[ $key ], $i, 1 );

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
        
        # add to paths() for access outside this func
        $paths->file = paths( 'file', $params->file );
        
        # canonical url to current content
        $uris->url = uris( 'url', slash_join( $uris->root, $params->path ) );

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

        # store the data to the hash for access from views and hooks
        data( $data );
            
        # run updates
        action( 'update', array($data, $uris, $paths) );
        
        # get updated data
        $data = (object) data();

        if ( isset( $data->order ) ) {

            $html    = load_html( locate_file($paths->views, "archive-{$data->type}.php", 'archive.php') );
            $article = load_html( locate_file($paths->views, "excerpt-{$data->type}.php", 'excerpt.php') );
            $feed = '';
            $f = null;

            foreach ( $data->order as $i => $u ) {
                if ( $u ) {
                    $f = slash_join( \dirname($params->file), $u, \basename($params->file) );
                    \file_exists($f) or $f = slash_join( $paths->root, $u, \basename($params->file) );
                    $u = load_json($f);
                    if ( $u ) $feed .= insert_data( $article, $u );
                    else \array_splice( $data->order , $i, 1 );
                }
            }

            if ( ! $data->moddate || date('Y-m-d') !== $data->moddate ) {
                $data->moddate = date('Y-m-d');
                $data = (object) json_update( $params->file, (array) data( $data ) );
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

if ( ! exists( 'fill_defaults' ) ) {
    function fill_defaults () {

        $data  = (object) data();
        $uris  = (object) uris();
        $paths = (object) paths();
        
        $defaults = array(
            'title' => \str_replace( array('-', '_'), ' ', \basename( \rtrim( $uris->url, '/' ) ) )
          , 'class' => ''
        );
        
        json_update($paths->file, function ( $o ) {
            unset ($o['url']);
            unset ($o['moddate']);
            return $o;
        });
        
        /*
        $needs_update = ! $o['moddate'] || ! $data->url
                      || $data->url !== $uris->url 
                      || ! has_all( $data, $defaults );
                      
        
        $needs_update and data( json_update($paths->file, function ( $o, $defaults ) {
            $o['moddate'] = \date( 'Y-m-d' );
            return defaults( $o, $defaults );
        }, $defaults ) );
        */
        // instead:
        $data->moddate = mtime( \dirname($paths->file), 'Y-m-d' );
        data( defaults($data, $defaults) );

    }
}

if ( ! exists( 'classes' ) ) {
    function classes ( $extra = null ) {
        $classes = data('class');
        \is_string($classes) and $classes = \preg_split( '#\s+#', $classes );
        \is_string($extra)   and $extra   = \preg_split( '#\s+#', $extra );
        $classes = \array_merge( (array) $extra, (array) $classes );
        # ( $year = data('pubyear') ) and $classes[] = 'y' . $year;
        # $slug = basename( data('url') );
        # $slug and $classes[] = 'slug-' . $slug;
        $classes = \array_filter( \array_unique( $classes ), 'strlen' );
        return \implode( ' ', $classes );
    }
}

if ( ! exists( 'classes_e' ) ) {
    function classes_e ( $extra = null ) {
        echo classes( $extra );
    }
}

if ( ! exists( 'normalize_data' ) ) {
    function normalize_data () {

        $data = (array) data(); # get the current data
        
        foreach ( array( 'js', 'css', 'tags', 'class' ) as $n ) {
            if ( null !== $data[$n] ) {
                \is_string( $data[$n] ) and $data[$n] = \preg_split( '#\s+#', $data[$n] ); # ssv
                \is_array( $data[$n] )  and $data[$n] = \array_unique( \array_filter( $data[$n], 'strlen' ) );
            }
        }

        # convert class names to a string 
        isset( $data['class'] ) and $data['class'] = \implode( ' ', $data['class'] );
        
        foreach ( array('pub', 'mod') as $n ) {
            $datetime = $data[$n . 'date'];
            if ( $datetime && ! $data[$n .= 'year'] ) {
                $data[$n] = \array_shift( \explode( '-', (string) $datetime ) );
                $data[$n] > 0 or $data[$n] = '';
            }
        }
        
        data( $data ); # update the current data (no need to update the json)
    }
}

if ( ! exists( 'query' ) ) {
    # [!] testing
    function query ( $path = null, $fn = null ) {

        $root = paths('root');
        
        if ( \func_num_args() < 2 ) {
            $fn = $path;
            $path = '';
        }
        
        $i = 0;

        foreach ( \array_unique( (array) $path ) as $dir )
            if ( $dir ? ! \ctype_punct( $dir ) : is_numeric( $dir ) )
                if ( \is_dir( $file = slash_join( $root, $dir, 'index.json' ) ) )
                    if ( \is_object( $data = load_json($file) ) )
                        if ( false === \call_user_func( $fn, $data, $i++ ) )
                            break;

    }
}

action( 'update', ns( 'fill_defaults' ) );
action( 'update', ns( 'normalize_data' ) );

# DEFAULT PATHS / URIS
\call_user_func(function () {

    $paths = (object) paths();
    $uris  = (object) uris();

    $paths->root  or $paths->root  = \dirname( __DIR__ );
    $paths->tag   or $paths->tag   = slash_join( $paths->root, 'tag' );
    $paths->views or $paths->views = slash_join( \is_dir('views') ? __DIR__ : $paths->root, 'views' );
    $paths->css = slash_join( $_SERVER['DOCUMENT_ROOT'], 'css/' );
    $paths->js  = slash_join( $_SERVER['DOCUMENT_ROOT'], 'js/' );

    $uris->home or $uris->home = slash_join( 'http://' . $_SERVER['SERVER_NAME'] );
    $uris->root or $uris->root = rslash( to_uri( $paths->root ) );
    $uris->tag  or $uris->tag  = rslash( to_uri( $paths->tag ) );
    $paths->css = slash_join( $uris->home, 'css/' );
    $paths->js  = slash_join( $uris->home, 'js/' );
    
    paths( $paths );
    uris( $uris );
    paths( \basename(__FILE__), __FILE__ );

});


# INITIALIZE
run(true);

#end