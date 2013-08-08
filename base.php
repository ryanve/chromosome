<?php
use \chromosome\Loci;
use \airve\Path;
require_once 'bootstrap.php';

# Default options:
Loci::option('path:items', Path::root('_items'));
Loci::option('path:views', Path::root('_views'));
Loci::option('basename:json', 'item.json');
Loci::option('basename:html', 'item.html');
Loci::option('basename:css', 'item.css');
Loci::option('keys:imports', ['item.html']);
Loci::option('keys:ssv', ['type', 'class']);
Loci::option('uri:home', Path::toUrl(
    Loci::option('path:root', Path::root(null))
));

Loci::on('bootstrap.php', function() {
    if (\is_file($data = Path::root('chromosome.json')) and $data = Path::getJson($data)) {
        $load = [];
        $root = Path::method('root');
        foreach ($data as $k => $v)
            Loci::option($k, 'path:' === \substr($k, 0, 5) ? (\is_array($v) ? \array_map($root, $v) : Path:root($v)) : $v);
        foreach ((array) Loci::option('path:files') as $file)
            $file && \is_file($file) and include_once($file);
    }
});

\array_reduce(['uri:home', 'path:root'], function($void, $key) {
    Loci::mixin(\substr(\strrchr($key, ':'), 1), function($relative = '') use ($key) {
        $base = Loci::option($key);
        return \strlen($relative) ? Path::join($base, $relative) : $base;
    });
}, null);

Loci::mixin('meta', function($name, $content = null) {
    return $name && \is_string($content) ? "<meta name='$name' content='$content'>" . "\n" : '';  
});

Loci::on('normalize', function() { 
    $ctxt = Loci::context();
    $time = $ctxt->data('pubdate');
    $ctxt->data('pubyear', \strtok($time, '-'));
    if ($ctxt->dir) {
        $time = Path::mtime($ctxt->dir);
        $ctxt->data('mtime', $time);
        $ctxt->data('moddate', \date('Y-m-d', $time));
        $ctxt->data('modyear', \date('Y', $time));
    }
});

Loci::on('normalize', function() {
    $ctxt = Loci::context();
    $title = $ctxt->data('title');
    $title or $title = $ctxt->data('title', '');
    $ctxt->data('titlebar', $ctxt->data('titlebar') ?: $title);
});

Loci::on('normalize', function() {
    Loci::context()->data(function($data) {
        $ctxt = Loci::context();
        $data['slug'] = empty($ctxt->dir) ? (
            isset($data['slug']) ? $data['slug'] : ''
        ) : \basename($ctxt->dir);

        $keys = Loci::option('keys:ssv') ?: [];
        foreach ($keys as $n)
            $data[$n] = empty($data[$n]) ? [] : Loci::toArray($data[$n]);
        
        if (\in_array('class', $keys)) {
            $data['class'][] = $ctxt->data('pubdate') === $ctxt->data('moddate') ? 'unrevised' : 'revised';
            if (\is_scalar($data['slug']) && \strlen($data['slug']))
                $data['class'][] = 'slug-' . $data['slug'];
            if (\in_array('type', $keys))
                foreach ($data['type'] as $type)
                    $data['class'][] = 'type-' . $type;
        }
        
        foreach ($keys as $n)
            !empty($data[$n]) && \is_array($data[$n]) and $data[$n] = \array_unique($data[$n]);
        return $data;
    });
});

Loci::on('phat.php', function() {
    \is_callable('\\airve\\Phat::rebreak') and Loci::option('filter:output', '\\airve\\Phat::rebreak');
});

Loci::option('uri.current', (
    Path::isHttps() ? 'https' : 'http') . '://' . 
    $_SERVER['SERVER_NAME'] . Path::lslash($_SERVER['REQUEST_URI'])
);

Loci::on('normalize', function() { 
    $ctxt = Loci::context();
    $url = Path::toUrl(Path::normalize(\str_replace([
        Loci::option('path:items')
      , Loci::option('path:taxos')
      , Loci::option('path:root')
    ], '', $ctxt->dir)));
    $ctxt->data('url', $url);
    Loci::option('uri.url', $url);
    Loci::option('uri.canonical', $url);
});

Loci::on('normalize', function() {
    $ctx = Loci::context();
    $dir = $ctx->dir;
    $dir and $ctx->data(\array_reduce(Loci::option('keys:imports') ?: [], function($r, $k) use ($dir) {
        \is_file($path = Path::join($dir, $k)) and $r[$k] = Path::loadFile($path);
        return $r;
    }, []));
});

Loci::option('view:default', function() {
    $dir = Path::rslash(Loci::option('path:views'));
    $view = Path::rslash(Loci::option('path:views')) . 'default.php';
    return \is_readable($view) ? Path::loadFile($view) : '<pre><code>' . \json_encode(
        Loci::context()->data(), JSON_PRETTY_PRINT
    ) . '</pre></code>';
});

Loci::on('view:default', function() {
    if (\is_array($arr = Loci::context()->data('class'))) {
        \in_array('view-default', $arr) or $arr[] = 'view-default';
        Loci::context()->data('class', $class);
    }
});

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));

#end