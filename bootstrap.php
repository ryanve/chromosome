<?php
/**
 * @package  ryanve/loci
 */
 
namespace airve;

# dependencies
\trait_exists('\\airve\\Mixin') or require '../mixin/mixin.php'; 
\class_exists('\\airve\\Path')  or require '../path/path.php';

# needed package files
\class_exists('\\airve\\Loci')  or require './loci.php';
require_once './base.php';

# suggestions
\class_exists('\\airve\\Phat')  or Path::inc('../phat/phat.php') and Loci::blast('phat.php');

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));
#end