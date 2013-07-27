<?php
use \chromosome\Loci;
use \airve\Path;

# dependencies
\trait_exists('\\traits\\Mixin') or require \dirname(__DIR__) . '/traits/mixin.php'; 
\trait_exists('\\traits\\Reflect') or require \dirname(__DIR__) . '/traits/reflect.php'; 
\class_exists('\\airve\\Path') or require \dirname(__DIR__) . '/path/path.php';

# needed package files
\class_exists('\\chromosome\\Loci') or require __DIR__ . '/loci.php';
require_once __DIR__ . '/base.php';

# suggestions
\class_exists('\\airve\\Phat') or Path::inc(\dirname(__DIR__) . '/phat/phat.php') and Loci::blast('phat.php');

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));

#end