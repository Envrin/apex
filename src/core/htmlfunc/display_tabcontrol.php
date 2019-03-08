<?php
declare(strict_types = 1);

namespace apex\core\htmlfunc;

use apex\DB;
use apex\template;
use apex\registry;
use apex\core\components;

class display_tabcontrol extends \apex\abstracts\htmlfunc
{

/**
* Replaces the calling <e:function> tag with the resulting 
* string of this function.
* 
*   @param string $html The contents of the TPL file, if exists, located at /views/htmlfunc/<package>/<alias>.tpl
*   @param array $data The attributes within the calling e:function> tag.
*   @return string The resulting HTML code, which the <e:function> tag within the template is replaced with.
*/
public function process(string $html, array $data = array()):string 
{

    // Perform checks
    if (!isset($data['tabcontrol'])) { 
        return "<b>ERROR:</b> No 'tabcontrol' attribute was defined in the 'function' tag."; 
    }

    // Get package/  alias
    if (!list($package, $parent, $alias) = components::check('tabcontrol', $data['tabcontrol'])) { 
        return "<b>ERROR:<?b> The tab control '$data[tabcontrol]' either does not exist, or more than one with the same alias exist and you did not specify the package to use.";
    }

    // Load tab control
    if (!$tabcontrol = components::load('tabcontrol', $alias, $package, '', $data)) { 
        return "<b>ERROR: </b> Unable to load the tab control '$alias' from package '$package''.  Component does not exist.";
    }

    // Process tab control, if needed
    if (method_exists($tabcontrol, 'process')) { 
        $tabcontrol->process($data);
    }

    // Get tab pages
    $tab_pages = $this->get_tab_pages($tabcontrol->tabpages, $alias, $package);
$tab_dir = SITE_PATH . '/src/' . $package . '/tabcontrol/' . $alias;

    // Go through tab pages
    $tab_html = "<e:tab_control>\n";
    foreach ($tab_pages as $tab_page => $tab_name) {

        // Check if tpl file exists
        $tpl_file = SITE_PATH . '/views/tabpage/' . $package . '/' . $alias . '/' . $tab_page . '.tpl'; 
        if (!file_exists($tpl_file)) { continue; }

        // Get HTML
        $page_html = file_get_contents($tpl_file);

        // Load PHP, if needed
        $php_file = $tab_dir . '/' . $tab_page . '.php';
        if (file_exists($php_file)) { 
            require_once($php_file);

            $class_name = 'tabpage_' . $package . '_' . $alias . '_' . $tab_page;
            $class_name = "\\apex\\" . $package . "\\tabcontrol\\" . $alias . "\\" . $tab_page;
            $page_client = new $class_name();

            // Process HTML
            if (method_exists($page_client, 'process')) { 
                $page_client->process($data);
            }
        }

        /// Add to tab html
        $tab_name = tr($tab_name);
        $tab_html .= "\t<e:tab_page name=\"$tab_name\">\n\n$page_html\n\t</e:tab_page>\n\n";
    }

    // Return
    $tab_html .= "</e:tab_control>\n";
    return template::parse_html($tab_html);

}

/**
* Get tab pages.  Goes through all additional tab pages added by 
* other packages, and positions them correctly.
*/
protected function get_tab_pages(array $tab_pages, string $parent, string $package)
{

    // Set variables
    $pages = array_keys($tab_pages);
    $tab_dir = SITE_PATH . '/src/' . $package . '/tabcontrol/' . $parent;

    // Go through extra pages
    $extra_pages = DB::get_column("SELECT alias FROM internal_components WHERE type = 'tabpage' AND package = %s AND parent = %s ORDER BY order_num", $package, $parent);
    foreach ($extra_pages as $alias) {  

        // Try to load
        $php_file = $tab_dir . '/' . $alias . '.php';
        if (!file_exists($php_file)) { 
            $pages[] = $alias;
            $page_names[$alias] = ucwords(str_replace("_", " ", $alias));
            continue;
        }

        // Load file
        $class_name = "\\apex\\" . $package . "\\tabcontrol\\" . $parent . "\\" . $alias;
        require_once($php_file);
        $page = new $class_name();

// Set variables
    $position = $page->position ?? 'bottom';
        $tab_pages[$alias] = $page->name ?? ucwords(str_replace("_", " ", $alias));

        // Check before / after position
        if (preg_match("/^(before|after) (.+)$/i", $position, $match)) { 

            if ($num = array_search($match[2], $pages)) { 
                if ($match[1] == 'after') { $num++; }
                array_splce($pages, $num, 0, $alias);
            } else { 
                $position = 'bottom';
            }

        }

        // Top / bottom position
        if ($position == 'top') { 
            array_shift($pages, $alias);
        } else { 
            array_push($pages, $alias);
        }

    }

    // Get new pages
    $new_pages = array();
    foreach ($pages as $alias) { 
        $new_pages[$alias] = $tab_pages[$alias];
    }

    // Return
    return $new_pages;

}
}

