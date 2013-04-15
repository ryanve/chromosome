<?php
/**
 * @package  ryanve/loci
 */
 
namespace airve;

# dependencies
\trait_exists('\\airve\\Mixin') or require \dirname(__DIR__) . '/mixin/mixin.php'; 
\class_exists('\\airve\\Path')  or require \dirname(__DIR__) . '/path/path.php';

# needed package files
\class_exists('\\airve\\Loci')  or require __DIR__ . '/loci.php';
require_once __DIR__ . '/base.php';

# suggestions
\class_exists('\\airve\\Phat')  or Path::inc(\dirname(__DIR__) . '/phat/phat.php') and Loci::blast('phat.php');

# optional customizations
Path::inc(Path::rslash(Loci::option('path:views')) . 'config.php') and Loci::blast('config.php');

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));
#end