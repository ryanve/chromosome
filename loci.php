<?php
namespace chromosome;
use \slash\Path;
require_once 'bootstrap.php';

if ( ! \function_exists(__NAMESPACE__ . '\\loci')) {
    function loci($data = null) {
        return \func_num_args() ? new Loci($data) : Loci::context();
    }
}

if ( ! \class_exists(__NAMESPACE__ . '\\Loci')) {
    class Loci extends Chromosome {}
}

# Fire and flush event handlers.
Loci::blast(\basename(__FILE__));