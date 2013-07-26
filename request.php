<?php
use \chromosome\Loci;
use \airve\Path;
require_once 'bootstrap.php';

\call_user_func(function($query) {
    $params = [];
    \parse_str($query, $params);    
    if (empty($params['request']))
        return false;
    $params['dir'] = isset($params['from']) ? Path::join(
        $params['from'], $params['request']
    ) : $params['request'];
    $inst = Loci::instantiate(Path::root($params['dir']));
    $inst->data($params, 'query:');
    Loci::trigger('query:');
    $inst->render_e();
}, $_SERVER['QUERY_STRING']);

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));

#end