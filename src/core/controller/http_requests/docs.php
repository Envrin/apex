<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\app;
use apex\svc\view;
use michelf\markdown;
use Michelf\MarkdownExtra;


/**
 * Handles the documentation HTTP requests -- the /docs/ directory.  Parses 
 * the necessary .md files via the php-markdown package, and displays it to 
 * the web browser. 
 */
class docs
{

    private $api_classes = [
        app::class => 'app'
    ];

    private $replacements = [];


/**
 * View page within documentation 
 *
 * Displays the .md documentation files found within the /docs/ directory. 
 * Filters them through the php-markdown package developed my michelf. 
 */
public function process()
{ 

    // Get URI
    $md_file = preg_replace("/\.md$/", "", implode("/", app::get_uri_segments()));
    if ($md_file == '') { $md_file = 'index'; }
    $md_file .= '.md';

    // Check if .md file exists
    if (!file_exists(SITE_PATH . '/docs/' . $md_file)) { 
        echo "No .md file exists here.";
        exit;
    }

    // Go through API classes
    foreach ($this->api_classes as $class => $api_name) { 
        $obj = app::get_instance();
        $methods = get_class_methods($obj);
        foreach ($methods as $method) { 
            $this->replacements[$method] = [$api_name];
        }
    }

    // Get MD template
    $lines = file(SITE_PATH . '/docs/' . $md_file);
    $md_code = '';
    $in_code = false;

    // Go through lines
    foreach ($lines as $line) { 

        // Check if in code
        if (preg_match("/^~~~/", $line)) { 
            $in_code = $in_code === true ? false : true;
            $md_code .= $line;
            continue; 
        } elseif ($in_code === true) { 
            $md_code .= $line;
            continue;
        }

        // Remove <api> tags
        $line = preg_replace("/<api(.+?)>/", "", $line);
        $line = str_replace('</api>', '', $line);

        // Go through replacements
        foreach ($this->replacements as $key => $dest) { 
            preg_match_all("/$key\((.*?)\)/", $line, $tag_match, PREG_SET_ORDER);
            foreach ($tag_match as $match) { 

                // Get method name
                $method = $dest[1] ?? $key;
                if (preg_match("/^(\w+?)\:\:(.+)/", $method, $tmp_match)) { $method = $tmp_match[2]; }

                // Replace
                $url = "https://apex-platform.org/api/classes/apex." . $dest[0] . ".html#method_" . $method;
                $a_code = "<a href=\"$url\" target=\"_blank\">$match[0]</a>";
                $line = str_replace($match[0], $a_code, $line);
            }
        }

        // Add line to md code
        $md_code .= $line;
    }

    // Replace <api: ...> tags
    preg_match_all("/<api:(.*?)>(.*?)<\/api>/", $md_code, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) { 
        $md_code = str_replace($match[0], '', $md_code); continue; 
    }



    // Apply markdown formatting
    $page_contents = MarkdownExtra::defaultTransform($md_code);

    // Get HTML
    $theme_dir = SITE_PATH . '/views/themes/' . app::_config('core:theme_public') . '/sections';
    $html = file_get_contents("$theme_dir/header.tpl");
    $html .= "<<PAGE_CONTENTS>>";
    $html .= file_get_contents("$theme_dir/footer.tpl");

    // Parse HTML
    view::load_base_variables();
    $html = view::parse_html($html);

    // Display
    echo str_replace("<<PAGE_CONTENTS>>", $page_contents, $html);
    exit(0);

}


}

