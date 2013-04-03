<?php
/**
 * @package  ryanve/loci
 */
 
namespace airve;

# Load dependencies and needed package files.
\trait_exists('\\airve\\Resolve') or require './resolve.php'; 
\class_exists('\\airve\\Path')    or require '../path/path.php';
\class_exists('\\airve\\Loci')    or require './loci.php';
require_once './base.php';

# Load suggestions if found:
\class_exists('\\airve\\Phat') || Path::inc('../phat/phat.php') and Loci::blast('phat.php');

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));
#end