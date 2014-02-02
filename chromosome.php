<?php
namespace chromosome;
use \slash\Path;
require_once 'bootstrap.php';

if ( ! \function_exists(__NAMESPACE__ . '\\chromosome')) {
    function chromosome($data = null) {
        return \func_num_args() ? new Chromosome($data) : Chromosome::context();
    }
}

if ( ! \class_exists(__NAMESPACE__ . '\\Chromosome')) {
    class Chromosome {

        use \traits\Aware;
        use \traits\Data;
        use \traits\Mixin;

        protected $renderee;
        public $dir;
        protected $data = [];
        protected static $handlers = [];
        
        public function __construct($data = null) {
            static::context($this);
            if (null !== $data) {
                Path::isPath($data) && ($this->dir = \dirname(
                    $data = \is_file($data) ? $data : Path::join($data, static::option('basename:json'))
                )) and $data = \is_file($data) ? Path::getJson($data) : ['type' => 'dir'];
                $data and $this->data($data);
                static::trigger('normalize');
            }
        }
        
        public function __destruct() {
            $this->data = $this->renderee = null;
        }
        
        public function __toString() {
            $arr = \get_object_vars($this);
            $arr['option'] = $this->option();
            return \json_encode($arr, JSON_PRETTY_PRINT);
        }
        
        protected static function emitter() {
            static $emitter;
            return $emitter ?: $emitter = new Emitter;
        }
        
        public static function on() {
            return \call_user_func_array([static::emitter(), 'on'], func_get_args());
        }

        public static function off() {
            return \call_user_func_array([static::emitter(), 'off'], func_get_args());
        }
        
        public static function emit() {
            return \call_user_func_array([static::emitter(), 'emit'], func_get_args());
        }
        
        public static function trigger() {
            return \call_user_func_array([static::emitter(), 'trigger'], func_get_args());
        }
        
        public static function blast($name, $scope = null) {
            static::trigger($name, $scope);
            static::off($name);
        }
        
        public static function revalue($trav, $value = null, $match = null) {
            foreach ($trav as $k => &$v)
                !$match || \preg_match($match, $k) and $v = $value;
            return $trav;
        }
        
        public static function option() {
            static $bound;
            return \call_user_func_array($bound ?: $bound = [static::instantiate(), 'data'], \func_get_args());
        }
        
        public function feed($key = null) {
            $this->renderee = $key;
            return $this;
        }
        
        public static function esc($item) {
            $item and $item = static::result($item);
            return ($item = (string) $item) ? \htmlentities($item, ENT_QUOTES, null, false) : $item;
        }
        
        protected static function isAssoc($arr) {
            foreach ($arr as $i => $v)
                if ( ! \is_int($i)) return true;
            return false;
        }
        
        protected static function isDeep($arr) {
            foreach ($arr as $v)
                if (null !== $v && ! \is_scalar($v)) return true;
            return false;
        }
        
        public static function toString($item) {
            $item and $item = static::result($item);
            if (null === $item || \is_scalar($item) || \is_callable([$item, '__toString']))
                return (string) $item;
            $item = \is_object($item) ? \get_object_vars($item) : (array) $item;
            if (static::isAssoc($item) || static::isDeep($item))
                return \json_encode($item);
            return \implode(' ', $item);
        }
        
        public static function toArray($item) {
            $item and $item = static::result($item);
            return \is_object($item) ? \get_object_vars($item) : \array_filter(\array_unique(
                \is_string($item) ? \preg_split('#\s+#', $item) : (array) $item
            ), 'strlen');
        }
        
        public static function template($markup, $fields) {
            $fields = \array_slice(\func_get_args(), 1);
            if ( ! \is_string($markup))
                return false;
            foreach ($fields as $group) {
                foreach ($group as $k => $v) {
                    $markup = ($k = static::surrogate($k)) ? \str_replace(
                        $k, static::toString($v), $markup
                    ) : $markup;
                }
            }
            #return \preg_replace('#{{.+}}#', '', $markup);
            return $markup;
        }
        
        protected static function surrogate($name) {
            return null === $name ? null : '{{' . $name . '}}';
        }

        public function render($view = null, $data = null) {
            $class = __CLASS__;
            $data and $data = static::result($data);
            
            if (null === $data)
                $data = $this->data($this->renderee);
            elseif (\is_string($data))
                $data = Path::getJson(Path::join($data, 'basename:json'));

            if ($isMap = \is_object($data))
                $data = $data instanceof $class ? $data->data($data->renderee) : \get_object_vars($data);
            else $isMap = empty($data) || static::isAssoc($data);
            
            $data = $data ?: [];
            if ($isMap) {
                $view = static::template(static::view($view, $data['type']), $data, static::option());
                return ($filter = static::option('filter:output')) ? \call_user_func($filter, $view) : $view;
            }

            return \array_reduce($data, function($html, $item) use ($view, $class) {
                null === $item or $html .= (new $class($item))->render($view);
                return $html;
            }, '');
        }
        
        /**
         * @param  string|array  $views
         * @param  string|array  $types
         * @return string|bool
         */
        public static function view($views = null, $types = null) {
            $prefix = 'view:';
            $dir = Path::rslash(static::option('path:views'));
            $views = null === $views ? [] : (\is_array($views) ? \array_values($views) : [$views]);
            $types = null === $types ? [] : (\is_array($types) ? \array_values($types) : (
                \is_object($types) ? static::toArray($types) : \array_slice(func_get_args(), 1) 
            ));

            # 1st: views suffixed with each type
            # 2nd: explicitly specified views
            # 3rd: explicitly specified types
            # 4th: default view option
            foreach (\array_reverse($views) as $view)
                \is_scalar($view) and $views = \array_merge(Path::affix($types, "$view-"), $views);
            $views = \array_merge($views, $types, [static::option('view:default')]);
            $types = null;

            foreach ($views as $view) {
                if (\is_scalar($view)) {
                    $view = \ltrim($view, Path::slashes);
                    if (\is_callable($op = static::option($prefix . $view))) {
                        static::trigger($prefix);
                        static::trigger($prefix . $view);
                        return (string) $op();
                    }
                    if (\is_file($file = $dir . Path::ext($view, '.php'))) {
                        static::trigger($prefix);
                        static::trigger($prefix . $view);
                        return (string) Path::loadFile($file);
                    }
                } elseif ($view) {
                    static::trigger($prefix);
                    return (string) $view();
                }
            }
            return false;
        }
    }#class
}

# Fire and flush event handlers.
Chromosome::blast(\basename(__FILE__));