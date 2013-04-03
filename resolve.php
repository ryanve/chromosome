<?php 
namespace airve;

trait Resolve {

    public function __call($name, $params) {
        return static::resolve($name, $params, $this);
    }
    
    public static function __callStatic($name, $params) {
        return static::resolve($name, $params);
    }
    
    public function __toString() {
        return \json_encode(\get_object_vars($this), JSON_PRETTY_PRINT);
    }

    public static function toString($item) {
        $item and $item = static::result($item);
        if (null === $item || \is_scalar($item) || \is_callable([$item, '__toString']))
            return (string) $item;
        return \json_encode($item = \is_object($item) ? \get_object_vars($item) : (array) $item);
    }
    
    public static function resolve($name, $params = [], $scope = null) {
        if ($fn = static::mixin($name))
            return static::apply($fn, $scope, $params);
        if ('_e' === \substr($name, -2))
            echo static::toString(\call_user_func_array(static::method(\substr($name, 0, -2), $scope), $params));
        else \trigger_error(\get_called_class() . " method '$name' is not callable.");
    }
    
    public static function mixin($name, $fn = 0, $chain = 0) {
        static $mixins;
        $mixins = $mixins ?: [[], []];
        $name and $name = static::result($name);
        if (\is_scalar($name)) {
            $chain = (int) $chain;
            $fn and $mixins[$chain][$name] = $fn;
            return empty($mixins[$chain][$name]) 
                ? null : $mixins[$chain][$name];
        }
        $chain = (int) $fn;
        if ($name)
            foreach ($name as $k => $v)
                $v and $mixins[$chain][$k] = $v;
        return $copy = $mixins[$chain];
    }
    
    public static function apply(callable $fn, object $scope = null, array $params = null) {
        null !== $scope && $fn instanceof \Closure and $fn = \Closure::bind($fn, $scope, \get_class($scope));
        return \call_user_func_array($fn, $params ?: []);
    }
    
    public static function call(callable $fn, object $scope = null) {
        return static::apply($fn, $scope, \array_slice(\func_get_args(), 2));
    }
    
    public static function curry($fn) {
        $curries = \func_get_args();
        \array_shift($curries);
        return function() use ($fn, $curries) {
            return \call_user_func_array($fn, \array_merge($curries, \func_get_args()));
        };
    }
    
    public static function method($name, $scope = null) {
        return array(null === $scope ? \get_called_class() : $scope, $name);
    }
    
    public static function methods($object = null) {
        $result = [];
        null === $object and $object = \get_called_class();
        foreach (\get_class_methods($object) as $m)
            $result[$m] = array($object, $m);
        return $result;
    }
    
    public static function result($fn) {
        return $fn instanceof \Closure ? \call_user_func_array(
            $fn, \array_slice(\func_get_args(), 1)
        ) : $fn;
    }
}