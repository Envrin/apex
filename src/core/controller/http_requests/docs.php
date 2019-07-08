<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\app;
use apex\services\template;
use michelf\markdown;
use Michelf\MarkdownExtra;


/**
 * Handles the documentation HTTP requests -- the /docs/ directory.  Parses 
 * the necessary .md files via the php-markdown package, and displays it to 
 * the web browser. 
 */
class docs
{



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

    // Get MD template
    $md_code = file_get_contents(SITE_PATH . '/docs/' . $md_file);

    // Replace <api: ...> tags
    preg_match_all("/<api:(.*?)>(.*?)<\/api>/", $md_code, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) { 

        // Check for proper method name
        if (!preg_match("/^(.+?)\(/", $match[2], $method_match)) { 
            $md_code = str_replace($match[0], $match[2], $md_code);
            continue;
        }
        $method_name = preg_match("/^(\w+?)\s(.*)$/", $method_match[1], $tmp_match) ? $tmp_match[2] : $method_match[1];
        if (preg_match("/^(.+?)\:\:(.+)/", $method_name, $tmp_match)) { $method_name = $tmp_match[2]; }

        // Get URL
        $url = "https://apex-platform.org/api/classes/apex." . $match[1] . ".html#method_" . $method_name;
        $alink = "<a href=\"$url\" target=\"_blank\">$match[2]</a>";
        $md_code = str_replace($match[0], $alink, $md_code);
    }

    // Apply markdown formatting
    $page_contents = MarkdownExtra::defaultTransform($md_code);

    // Get HTML
    $theme_dir = SITE_PATH . '/views/themes/' . app::_config('core:theme_public') . '/sections';
    $html = file_get_contents("$theme_dir/header.tpl");
    $html .= "<<PAGE_CONTENTS>>";
    $html .= file_get_contents("$theme_dir/footer.tpl");

    // Parse HTML
    template::load_base_variables();
    $html = template::parse_html($html);

    // Display
    echo str_replace("<<PAGE_CONTENTS>>", $page_contents, $html);
    exit(0);

}


}

