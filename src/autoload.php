<?php
declare(strict_types = 1);

// Register autoload function
spl_autoload_register('autoload_class');

/**
* Autoloader
*/function autoload_class(string $class)
{

    // Format class
    $elements = explode("\\", $class);
    if ($elements[0] == 'apex') { array_shift($elements); }
    $file = implode('/', $elements) . '.php';

    // Define base dirs
    $base_dirs = array(
        SITE_PATH . '/src', 
        SITE_PATH . '/lib', 
        SITE_PATH . '/lib/exceptions', 
        SITE_PATH . '/lib/third_party'
    );

    // Check base dirs
    foreach ($base_dirs as $dir) { 
        if (!file_exists($dir . '/' . $file)) { continue; }

        require_once($dir . '/' . $file);
        return;
    }

}

