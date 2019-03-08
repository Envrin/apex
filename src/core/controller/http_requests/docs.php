<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use michelf\markdown;
use Michelf\MarkdownExtra;

class docs extends \apex\core\controller\http_requests
{

/**
* Displays the .md documentation files found 
* within the /docs/ directory.  Filters them through the 
* php-markdown package developed my michelf.
*/
public function process()
{

    // Get URI
    $md_file = preg_replace("/\.md$/", "", implode("/", registry::$uri));
    if ($md_file == '') { $md_file = 'index'; }
    $md_file .= '.md';

    // Check if .md file exists
    if (!file_exists(SITE_PATH . '/docs/' . $md_file)) { 
        echo "No .md file exists here.";
        exit;
    }

    // Get MD template
    $md_code = file_get_contents(SITE_PATH . '/docs/' . $md_file);
    $html = MarkdownExtra::defaultTransform($md_code);

    // Display
    echo $html;
    exit(0);

}

}

