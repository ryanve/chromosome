<?php 
namespace loci;

/**
 * loci.php   A PHP template engine that generates views
 *            based on content data stored in JSON files.
 * @link      http://github.com/ryanve/loci
 * @author    Ryan Van Etten <@ryanve>
 * @version   0.x
 * @license   MIT
 */

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
 * Call a namespaced function by name. ( Params can be supplied via extra args. )
 * @param   string    $fname
 */
if ( ! exists( 'call' ) ) {
    function call ( $fname ) {
        $params = func_get_args();
        return \call_user_func_array( ns( \array_shift($params) ), $params );
    }
}

/**
 * Call a namespaced function by name. ( Params can be supplied via array. )
 * @param   string    $fname
 * @param   array     $params
 */
if ( ! exists( 'apply' ) ) {
    function apply ( $fname, $params = array() ) {
        return \call_user_func_array( ns( $fname ), $params );
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
    function mtime ( $path, $format = null ) {
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
    function locate_file ( $dir, $file, $types = null ) {# OR $dir, $needle1, $needle2 ...

        if ( \is_array( $types ) ) {
            $file = slash_join( $dir, $file );
            $ext = '#(\.[a-z0-9]+)$#i';
            foreach ( $types as $n )
                if ( \file_exists( $n = \preg_replace( $ext, "-$n$1", $file ) ) )
                    return $n;
            return \file_exists($file) ? $file : null;

        } else {
            $filenames = \func_get_args();
            $dir = \array_shift( $filenames );
            foreach ( $filenames as $n )
                if ( $n && \file_exists( $n = slash_join($dir, $n) ) )
                    return $n;
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
        include ($file);
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
        if ( ! $file || ! \is_readable($file) || \is_dir($file) )
            return;
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

# make it easy to print arrays to string
if ( ! exists( 'ssv', 'class' ) ) {
    class ssv {
        function __construct ( $ssv = null ) {
            $ssv = \is_string( $ssv ) ? \preg_split( '#\s+#', $ssv ) : $ssv;
            foreach ( (array) $ssv as $k => $v )
                \is_scalar($v) && \strlen($v = \trim($v)) and $this->{$k} = $v;
        }
        function __toString () {
            return \implode( ' ', (array) $this );
        }
    }
}

/**
 *
 *
 */
if ( ! exists( 'ssv' ) ) {
    function ssv ( $ssv = null ) {
        return new ssv( $ssv );
    }
}

if ( ! exists( 'shift' ) ) {
    function shift ( $arr, $delim = ' ' ) {
        if ( \is_scalar($arr) )
            foreach ( (array) $delim as $i => $d )
                $arr = \explode( $d, $i > 0 ? $arr[0] : $arr );
        $arr = (array) $arr;
        return $arr[0];
    }
}

if ( ! exists( 'pop' ) ) {
    function pop ( $arr, $delim = ' ' ) {
        if ( \is_scalar($arr) )
            foreach ( (array) $delim as $i => $d )
                $arr = \explode( $d, $i > 0 ? \array_pop($arr) : $arr );
        $arr = (array) $arr;
        return \array_pop($arr);
    }
}

if ( ! exists( 'each' ) ) {
    function each ( &$o, $fn ) {
        foreach ( $o as $k => $v )
            if ( \call_user_func( $fn, $v, $k, $o ) === false )
                break;
        return $o;
    }
}

/**
 * Get a copy of `$o` with all its values set to `$value`.
 * @param   array|object  $o
 * @param   mixed         $value
 * @return  array|object
 */
if ( ! exists( 'revalue' ) ) {
    function revalue ( $o, $value = null ) {
        foreach ( $o as &$v )
            $v = $value;
        return $o;
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
        return \call_user_func_array( $data, \func_get_args() );
    }
}

/**
 * Get or set URIs.
 */
if ( ! exists( 'uris' ) ) {
    function uris ( $key = null, $value = null ) {
        static $data;  # php.net/manual/en/language.variables.scope.php
        isset( $data ) or $data = hasher();
        return \call_user_func_array( $data, \func_get_args() );
    }
}

/**
 * Get or set options.
 */
if ( ! exists( 'options' ) ) {
    function options ( $key = null, $value = null ) {
        static $data;  # php.net/manual/en/language.variables.scope.php
        isset( $data ) or $data = hasher();
        return \call_user_func_array( $data, \func_get_args() );
    }
}

if ( ! exists( 'is_assoc' ) ) {
    function is_assoc ( $item ) {
        return !! $item && \is_array($item) && ! \ctype_digit( \implode( '', \array_keys($item) ) );
    }
}

/**
 * 
 */
if ( ! exists( 'is_type' ) ) {
    function is_type ( $type ) {
        return \in_array( $type, (array) data('type') );
    }
}

/**
 * 
 */
if ( ! exists( 'is_plural' ) ) {
    function is_plural ( $data = null ) {
        null === $data or $data = data();
        $data = (array) $data;
        return null !== $data['order'];
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
        $params = 2 > $n ? array() : $callback;

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

/**
 * 
 */
if ( ! exists( 'process' ) ) {
    function process ( $key, $callback = null, $op = null ) {
    
        static $hash;
        static $all;
        isset( $hash ) or $hash = array();
        isset( $all )  or $all = array();
        
        if ( !( $num = \func_num_args() ) )
            return;

        if ( ! \is_scalar($key) ) {
            if ( null === $key ) {
                $callback and ( 0 === $op ? \array_unshift( $all, $callback ) : ( $all[] = $callback ) );
            } elseif ( $num > 1 ) {
                foreach ( $key as $v )
                    process( $v, $callback, $op );
            } else { 
                ( $is_ob = \is_object($key) ) and $key = (array) $key;
                foreach ( \array_keys($hash) as $k )
                    foreach ( (array) $hash[ $k ] as $fn ) # fire
                        $fn and $key[ $k ] = \call_user_func_array( $fn, array( $key[ $k ], $k, (object) $key ) );
                foreach ( $all as $fn )
                    $fn and $key = \array_merge( $key, (array) \call_user_func( $fn, (object) $key ) );
                $is_ob and $key = (object) $key;
            }
            return $key;
        }

        $hash[ $key ] or $hash[ $key ] = array();

        if ( false === $callback ) # remove all
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

if ( ! exists( 'render' ) ) {
    function render ( $view = null, $data = null ) {
    
        $request;
        $html = '';
        if ( ! \is_string($view) || ! \strlen($view) )
            return $html;
        $orig = (object) data(); # current instance
        
        if ( \is_scalar($data) )
            \is_bool($data) or $data = load_json( rslash( paths('root') ) . ( $request = rslash($data) ) .  'index.json' );
        elseif ( \func_num_args() < 2 )
            $data = $orig;

        if ( $data && ! \is_scalar($data) ) {
            if ( \is_array($data) && ! is_assoc($data) ) {
                while ( $data )
                    $html .= render( $view, \array_shift($data) );
            } else {
                data( revalue( $orig ) ); # nullify hash values
                $data = (object) data( normalize( $data ) ); # normalize + update current instance
                if ( $request ) {
                    $data->request = data( 'request', $request );
                    $data->url = data( 'url', slash_join( uris('root'), $request ) );
                }
                $html = load_html( locate_file( paths('views'), $view, (array) $data->type ) );
                $html = insert_data( $html, $data );
                $html = insert_data( $html, uris(), 'uri.' );
                data( revalue( $data ) );  # nullify hash values
                data( $orig ); # return the hash to its orig state
            }
        }
        
        return $html;
    }
}

    

if ( ! exists( 'run' ) ) {
    function run ( $file = null ) {
    
        static $ran;
    
        if ( null === $file )
            return true === $ran;

        if ( ! \is_string( $file ) ) {
            $file = (object) $file;
            return run( $file->file );
        }
        
        $ran = true;

        $paths = (object) paths();
        $uris  = (object) uris();

        # queries should be like: `file=2012/headline/index.json`
        $request = rslash( \dirname( $file ) ); # relative
        $file = slash_join( $paths->root, $file );
        
        if ( ! \is_readable($file) )
            return;
        
        # add to paths() for use outside this func
        # $paths->file = paths( 'file', $file );

        $type  = null;
        $name  = null;
        $year  = null;
        $temp  = null;
        $feed  = array();
        $html  = '';

        $data = load_json( $file );

        if ( \is_array($data) )
            $data = (object) $data;
        elseif ( ! \is_object($data) )
            return;
            
        # canonical url to current content
        # $data->url = uris( 'url', slash_join( $uris->root, $request ) );
        $data->url = slash_join( $uris->root, $request );
        $data->request = $request;
        
        $data->modunix = mtime( \dirname($file) );
        $data->moddate = date( 'Y-m-d', $data->modunix );

        # store the data to the hash for use from views and hooks
        $data = data( $data );
        
        # echo markup
        render_e( 'type.php', $data );

    }
}

if ( ! exists( 'classes' ) ) {
    function classes ( $classes = null, $file = null ) {
        $classes = (array) ssv( $classes );
        $i = count( $classes );
        foreach ( ssv( data('type') ) as $n )
            $classes[ $i++ ] = 'type-' . $n;
        foreach ( ssv( data('class') ) as $n )
            $classes[ $i++ ] = $n;
        #if ( $file ) {
        #    $file = \basename( $file, '.php' );
        #    $file and $classes[] = $file;
        #}
        # $slug = basename( data('url') );
        # $slug and $classes[] = 'slug-' . $slug;
        return data( 'class', ssv( \array_unique( $classes ) ) );
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

# ECHOERS 

/**
 * @param   string   $str
 */
if ( ! exists( 'e' ) ) {
    function e ( $str ) {
        echo $str;
    }
}

if ( ! exists( 'esc_e' ) ) {
    function esc_e () {
        echo apply( 'esc', \func_get_args() );
    }
}

if ( ! exists( 'data_e' ) ) {
    function data_e () {
        echo apply( 'data', \func_get_args() );
    }
}

if ( ! exists( 'render_e' ) ) {
    function render_e () {
        echo apply( 'render', \func_get_args() );
    }
}

if ( ! exists( 'meta_e' ) ) {
    function meta_e ( $name, $content ) {
        echo apply( 'meta', \func_get_args() );
    }
}

if ( ! exists( 'uris_e' ) ) {
    function uris_e () {
        echo apply( 'uris', \func_get_args() );
    }
}

if ( ! exists( 'ssv_e' ) ) {
    function ssv_e () {
        echo apply( 'ssv', \func_get_args() );
    }
}

if ( ! exists( 'classes_e' ) ) {
    function classes_e () {
        echo apply( 'classes', \func_get_args() );
    }
}

if ( ! exists( 'shift_e' ) ) {
    function shift_e () {
        echo apply( 'shift', \func_get_args() );
    }
}

if ( ! exists( 'pop_e' ) ) {
    function pop_e () {
        echo apply( 'pop', \func_get_args() );
    }
}

# ACTIONS

if ( ! exists( 'normalize' ) ) {
    function normalize ( $context = null ) {
        if ( $use_current = ! \func_num_args() )
            $context = data();
        else $context or $context = array();
        $context = process( $context );
        $use_current and data( $context ); # update the current data hash
        return $context;
    }
}

process( array( 'js', 'css', 'tags', 'class', 'type' ), function ( $v ) {
    return ssv( \array_unique( (array) ssv($v) ) );
});

process( 'title', function ( $v, $k, $o ) {
    $o = (object) $o;
    return ( $v = esc($v) ) ? $v : \basename( \rtrim( $o->url, '/' ) );
});

process( 'slug', function ( $v, $k = null, $o = null ) {
    $o = (object) $o;
    return \basename( \rtrim( $o->url, '/' ) );
});

process( null, function ( $o ) {
    $o = (array) $o;
    echo classes();
    foreach ( array('pub', 'mod') as $n ) {
        $datetime = $o[$n . 'date'];
        $n .= 'year';
        if ( $datetime && ! $o[$n] ) {
            $o[$n] = \array_shift( \explode( '-', (string) $datetime ) );
            $o[$n] > 0 or $o[$n] = '';
        }
    }
    return $o;
});

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

# 
\file_exists( 'loci-custom.php' ) and include_once( 'loci-custom.php' );

# INITIALIZE
run( params() );

#end