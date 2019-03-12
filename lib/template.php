<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\rpc;
use apex\core\components;
use apex\core\admin;
use core\users\user;

/**
* Handles the parsing of all .tpl template files located 
* within the /views/ directory.  For more information on templates, please 
* refer to the developer documentation.
*/
class template 
{

    // Public variables
    public static $has_errors = false;

    // Input variables
    protected static $vars = array();
    public static $user_messages = array();
    protected static $js_code = '';
    public static $page_title = '';

    // Template variables
    protected static $tpl_code = '';
    protected static $theme_dir = '';
    protected static $theme_uri = '';

    // HTML tag objects
    protected static $html_tags;
    protected static $theme_client;


/**
* Initializes the template engine, and sets the appropriate 
* route based on URI from registry.
*/
public static function initialize(string $template_path = '') 
{

    // Set variables
    self::$theme_dir = SITE_PATH . '/themes/' . registry::$theme;
    self::$theme_uri = '/themes/' . registry::$theme;
    self::$html_tags = new html_tags();

        // Load theme class
    if (file_exists(self::$theme_dir .'/theme.php')) { 
        require_once(self::$theme_dir . '/theme.php');

        // Load class
        $class_name = 'theme_' . registry::$theme;
        self::$theme_client = new $class_name();
    } else { self::$theme_client = null; }

}

/**
* Fully parses a template including all aspects from 
* theme layouts, special tags, and more.
*     @return string THe resulting HTML code.
*/
public static function parse():string 
{

    // Initialize
    self::initialize();

    // Debug
    debug::add(1, fmsg("Begin parsing template, /{1}/{2}", registry::$panel, registry::$route), __FILE__, __LINE__, 'info');

    // Execute any necessary RPC calls
    if (registry::$route != '504') { 

        // Set vars
        $vars = array(
            'panel' => registry::$panel, 
            'route' => registry::$route, 
            'userid' => registry::$userid
        );

        $rpc = new rpc();
        $response = $rpc->send('core.template.parse', json_encode($vars));

        foreach ($response as $package => $vars) { 
            foreach ($vars as $key => $value) { 
                self::assign($key, $value);
            }
        }
    }

    // Get tpl file
    $tpl_file = SITE_PATH . '/views/tpl/' . registry::$panel . '/' . registry::$route . '.tpl';
    if (file_exists($tpl_file)) { 
        self::$tpl_code = file_get_contents($tpl_file);
    } elseif (file_exists(SITE_PATH . '/views/tpl/' . registry::$panel . '/404.tpl')) {  
        self::$tpl_code = file_get_contents(SITE_PATH . '/views/tpl/' . registry::$panel . '/404.tpl');
    } else { 
        return "We're sorry, but no TPL file exists for the location " . registry::$route . " within the panel " . registry::$panel . ", and no 404 template was found.";
    }

    // debug
    debug::add(4, fmsg("Acquired TPL code for template, /{1}/{2}", registry::$panel, registry::$route), __FILE__, __LINE__);

    // Load base variables
    self::load_base_variables();
    Debug::add(5, fmsg("Loaded base template variables"), __FILE__, __LINE__);

    // Add layout
    self::add_layout();
    debug::add(5, fmsg("Added layout to template, theme: {1}, URI: /{2}/{3}", registry::$theme, registry::$panel, registry::$route), __FILE__, __LINE__);

    // Process theme components
    self::process_theme_components();
    debug::add(5, fmsg("Completed processing all theme components for template"), __FILE__, __LINE__);

    // Parse PHP code, if needed
    $php_file = SITE_PATH . '/views/php/' . registry::$panel . '/' . registry::$route . '.php';
    if (file_exists($php_file)) { 
        require($php_file); 
        debug::add(4, fmsg("Loaded template PHP file at, {1}", $php_file), __FILE__, __LINE__);
    }

    // Merge variables
    self::$tpl_code = self::merge_vars(self::$tpl_code);
    debug::add(5, fmsg("Merged template variables into TPL code."), __FILE__, __LINE__);

    // Parse HTML
    $html = self::parse_html(self::$tpl_code);
    debug::add(4, fmsg("Successfully parsed HTML for template, , /{1}/{2}", registry::$panel, registry::$route), __FILE__, __LINE__);

    // Replace Javascript, if needed
    if (self::$js_code != '') {
        $html = str_replace("</body>", "\t<script type=\"text/javascript\">\n" . self::$js_code . "\n\t</script>\n\n</body>", $html);
    }
    debug::add(5, fmsg("Added Javascript to template as needed"), __FILE__, __LINE__);

    // Merge vars
    $html = self::merge_vars($html);
    debug::add(5, fmsg("Merged template variables"), __FILE__, __LINE__);

    // Add system Javascript / HTML
    $html = self::add_system_javascript($html);

    // Debug
    debug::add(1, fmsg("Successfully parsed template and returning resulting HTML, /{1}/{2}", registry::$panel, registry::$route), __FILE__, __LINE__);

    // Return
    return $html;

}

/**
* Parses TPL code, and transforms it into HTML code.  This is 
* used for the body of the TPL file, but also other things such as the 
* resulting TPL code from HTML functions and tab controls.
*     @param string $tpl_code The TPL code to parse.
*     @return string The resulting HTML code.
*/
public static function parse_html(string $html):string 
{ 

    // Merge vars
    $html = self::merge_vars($html);

    // User message
    $html = str_ireplace("<e:user_message>", self::$html_tags->user_message(self::$user_messages), $html);
    debug::add(5, fmsg("Processed template user message"), __FILE__, __LINE__);

    // Process IF tags
    $html = self::process_if_tags($html);
    debug::add(5, "Processed template IF tags", __FILE__, __LINE__);

    // Process sections
    $html = self::process_sections($html);
    debug::add(5, fmsg("Processed template section tags."), __FILE__, __LINE__);

    // Process HTML functions
    $html = self::process_function_tags($html);
    debug::add(5, fmsg("Processes template HTML function tags"), __FILE__, __LINE__);

    // Process page title
    $html = self::process_page_title($html);
    debug::add(5, fmsg("Processed template page title"), __FILE__, __LINE__);

    // Process nav menus
    $html = self::process_nav_menu($html);
    debug::add(5, fmsg("Processes template nav menus"), __FILE__, __LINE__);

    // Process e: tags
    preg_match_all("/<e:(.+?)>/si", $html, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) {
        $tag = $match[1];

        // Parse attributes
        $attr = array();
        if (preg_match("/(.+?)\s(.+)$/", $tag, $attr_match)) { 
            $tag = $attr_match[1];
            $attr = self::parse_attr($attr_match[2]);
        }

        // Check for closing tag
        //$chk_match = str_replace("/", "\\/", $match[0]);
        $chk_match = strtr($match[0], array("/" => "\\/", "'" => "\\'", "\"" => "\\\"", "\$" => "\\\$"));
        if (preg_match("/$chk_match(.*?)<\/e\:$tag>/si", $html, $html_match)) { 

            $text = $html_match[1];
            $match[0] = $html_match[0];
        } else { $text = ''; }

        // Replace HTML tag
        $html = str_replace($match[0], self::get_html_tag($tag, $attr, $text), $html);
    }
    debug::add(5, fmsg("Processed template special e: tags"), __FILE__, __LINE__);

    // Replace special characters
    $html = str_replace(array('~op~','~cp~'), array('(', ')'), $html);

    // Debug
    debug::add(4, fmsg("Successfully finished parsing TPL code of template."), __FILE__, __LINE__);

    // Return
    return $html;

}

/**
* Overlays a TPL file with the appropriate layout, depending on the 
* template being displayed, and which theme is being used.
*/
protected static function add_layout() 
{

    // Check cms_layouts table for layout
    $key = registry::$panel . '/' . registry::$route;
    if (registry::$redis->hexists('config:db_master', 'dbname') && $value = registry::$redis->hget('cms:layouts', $key)) { 
        $layout = $value;
    } else { $layout = 'default'; }

    // Debug
    debug::add(5, fmsg("Determined template layout, {1}", $layout), __FILE__, __LINE__);

    // Check if layout exists
    $layout_file = self::$theme_dir . '/layouts/' . $layout . '.tpl';
    if (file_exists($layout_file)) { 
        $layout_html = file_get_contents($layout_file);
    } elseif ($layout != 'default' && file_exists(self::$theme_dir . '/layouts/default.tpl')) { 
        $layout_html = file_get_contents(self::$theme_dir . '/layouts/default.tpl');
        debug::add(3, fmsg("Template layout file does not exist, {1}, reverting to default layout", $layout), __FILE__, __LINE__, 'warning');
    } else { 
        debug::add(1, fmsg("No layout file exists for template, and no default layout.  Returning with no layout"), __FILE__, __LINE__, 'warning');
        return; 
    }

    // Replace page contents
    self::$tpl_code = str_replace("<e:page_contents>", self::$tpl_code, $layout_html);
}

/**
* Gets the page title.  Checks the cms_templates mySQL table, 
* and otherwise looks for a <h1>...</h1> tags in the TPL code, 
* and if non exist, default to the $config['site_name'] variable.
*/
protected static function get_page_title() 
{

    //Get page title
    $title = '';
    $key = registry::$panel . '/' . registry::$route;
    if ($value = registry::$redis->hget('cms:titles', $key)) { 
        $title = $value;
    } elseif (preg_match("/<h1>(.+?)<\/h1>/i", self::$tpl_code, $match)) { 
        $title = $match[1];
        self::$tpl_code = str_replace($match[0], '', self::$tpl_code);
    } elseif (registry::$http_controller != 'admin') { 
        $title = registry::config('core:site_name');
    }

    // Debug
    debug::add(5, fmsg("Retrived template page title, {1}", $title), __FILE__, __LINE__);

/// Return
    return $title;

}

/**
* Processes the page title, and adds it into the correct 
* places within the template with the proper formatting.
*/
protected static function process_page_title(string $html):string 
{

    // Go through e:page_title tags
    preg_match_all("/<e:page_title(.*?)>/si", $html, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) { 

        $attr = self::parse_attr($match[1]);
        $html = str_replace($match[0], self::get_html_tag('page_title', $attr, self::$page_title), $html);
    }

    // Return
    return $html;

}

/**
* Parses the <e:nav_menu> as necessary, and replaces with the appropriate 
* HTML code.  Please refer to documentation for full details.
*/
protected static function process_nav_menu(string $html):string 
{

    // Initial checks
    //if (!preg_match("/<e:nav_menu(.*?)>/i", $html, $match)) { return $html; }

    // Get nav menu HTML
    if (file_exists(self::$theme_dir . '/components/nav_menu.tpl')) { 
        $tag_html = file_get_contents(self::$theme_dir . '/components/nav_menu.tpl');
    } else { 
        $tag_html = ''; 
        debug::add(4, fmsg("No /sections/nav_menu.tpl file exists for theme {1}, reverting to default.", registry::$theme), __FILE__, __LINE__, 'notice');
    }

    // Parse tag HTML
    $tag_header = preg_match("/<header>(.*?)<\/header>/si", $tag_html, $match) ? $match[1] : '<li class="header">~name~</li>';
    $tag_parent = preg_match("/<parent>(.*?)<\/parent>/si", $tag_html, $match) ? $match[1] : '<li><a href="~url~"><span>~icon~~name~</span></a><ul>~submenus~</ul></li>';
    $tag_menu = preg_match("/<menu>(.*?)<\/menu>/si", $tag_html, $match) ? $match[1] : '<li><a href="~url~">~icon~~name~</a></li>';

    // Go through menus
    $menu_html = '';
    if (!$data = registry::$redis->hget('cms:menus', registry::$panel)) { 
        $rows = array('__main' => array());
    } else { 
        $rows = json_decode($data, true);
    }
    foreach ($rows['__main'] as $row) { 

        // Skip, if needed
        if (registry::$panel == 'public') { 
            if ($row['require_login'] == 1 && registry::$userid == 0) { continue; }
        if ($row['require_nologin'] == 1 && registry::$userid > 0) { continue; }
        }

        // Get HTML to use
        if ($row['link_type'] == 'header') { $temp_html = $tag_header; }
        elseif ($row['link_type'] == 'parent') { $temp_html = $tag_parent; }
        else { $temp_html = $tag_menu; }

        // Get child menus
        $submenus = '';
        $crows = isset($rows[$row['alias']]) ? $rows[$row['alias']] : array(); 
        foreach ($crows as $crow) {

            // Skip, if needed
            if (registry::$panel == 'public') { 
                if ($crow['require_login'] == 1 && registry::$userid == 0) { continue; }
                if ($crow['require_nologin'] == 1 && registry::$userid > 0) { continue; }
            }



            $submenus .= self::process_menu_row($tag_menu, $crow);
        }

        // Add to menu HTML
        $menu_html .= self::process_menu_row($temp_html, $row, $submenus);
    }

    // Replace HTML
    $html = str_replace("<e:nav_menu>", $menu_html, $html);

    // Return
    return $html;


}

/**
* Protected function that processes a single row from the 'cms_menus' 
* table, and returns the appropriate HTML for that single menu item.
*/
protected static function process_menu_row(string $html, array $row, string $submenus = ''):string 
{

    // Get URL
    if ($row['link_type'] == 'parent') { $url = '#'; }
    elseif ($row['link_type'] == 'external') { $url = $row['url']; }
    elseif ($row['link_type'] == 'internal') {
        $url = '';
        if (registry::$panel != 'public') { $url .= '/' . registry::$panel; }
        if ($row['parent'] != '') { $url .= '/' . $row['parent']; }
        $url .= '/' . $row['alias'];
    } else { $url = ''; } 

    // Merge HTML
    $icon = $row['icon'] == '' ? '' : '<i class=\""' . $row['icon'] . '"\"></i>';
    $html = str_replace("~url~", $url, $html);
    $html = str_replace("~icon~", $icon, $html);
    $html = str_replace("~name~", tr($row['display_name']), $html);
    $html = str_replace("~submenus~", $submenus, $html);

    // Return
    return $html;

}

/**
* Processes all the <e:function> tags within the TPL code, 
* and replaces them with the appropriate HTML code.
*/
protected static function process_function_tags(string $html):string 
{

    // Go through function tags
    preg_match_all("/<e:function (.*?)>/si", $html, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) {

        // Parse attributes
        $attr = self::parse_attr($match[1]);
        if (!isset($attr['alias'])) { 
            $html = str_replace($match[0], "<b>ERROR:</b. No 'alias' attribute exists within the 'function' tag, which is required.", $html);
            debug::add(3, fmsg("Template encountered a e:function tag without an 'alias' attribute"), __FILE__, __LINE__, 'notice');
            continue;
        }

        // Get package and alias
        if (!list($package, $parent, $alias) = components::check('htmlfunc', $attr['alias'])) { 
            $html = str_replace($match[0], "The HTML function '$attr[alias]' either does not exists, or exists in more than one package and no specific package was defined.", $html);
            debug::add(1, fmsg("Template contains invalid e:function tag, the HTML function does not exist, package: {1}, alias: {2}", $package, $alias), __FILE__, __LINE__, 'notice');
            continue;
        }

        // Load component
        if (!$client = components::load('htmlfunc', $alias, $package, '', $attr)) { 
            $html = str_replace($match[0], "<b>ERROR:</b> Unable to load html function with alias '$alias' from package '$package'", $html);
            debug::add(1, fmsg("Parsing e:function tag within TPL code resulted in 'htmlfunc' component that could not be loaded, package: {1}, alias: {2}", $package, $alias), __FILE__, __LINE__, 'error'); 
            continue;
        }

        // Get temp HTML
        $func_tpl_file = SITE_PATH . '/views/htmlfunc/' . $package . '/' . $alias . '.tpl';
        $temp_html = file_exists($func_tpl_file) ? file_get_contents($func_tpl_file) : '';

        // Replace HTML
        $html = str_replace($match[0], self::parse_html($client->process($temp_html, $attr)), $html);
        debug::add(5, fmsg("Successfully processed e:function tag within TPL code, package: {1}, alias: {2}", $package, $alias), __FILE__, __LINE__);
    }

    // Return
    return $html;

}

/**
* Parses all the <e:if> tags within the TPL code, and 
* returns the appropriate HTML.
*/
protected static function process_if_tags(string $html):string 
{

    // Go through all IF tags
    preg_match_all("/<e:if (.*?)>(.*?)<\/e:if>/si", $html, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) {

        // Check for <eLelse> tag
        if (preg_match("/^(.*?)<e:else>(.*)$/si", $match[2], $else_match)) { 
            $if_html = $else_match[1];
            $else_html = $else_match[2];
        } else { 
            $if_html = $match[2];
            $else_html = '';
        }

        // Check condition
        debug::add(5, fmsg("Template, checking IF condition: {1}", $match[1]), __FILE__, __LINE__);
        $replace_html = eval( "return " . $match[1] . ";" ) === true ? $if_html : $else_html;
        $html = str_replace($match[0], $replace_html, $html);
    }

    // Return
    return $html;

}

/**
* Processes all the <e:section> tags found within the TPL code, 
* which loop over an array copying the HTML in between the 
* tags for each set of data.
*/
protected static function process_sections(string $html):string 
{

    // Go through sections
    preg_match_all("/<e:section(.*?)>(.*?)<\/e:section>/si", $html, $tag_match, PREG_SET_ORDER);
    foreach ($tag_match as $match) { 

        // Parse attributes
        $attr = self::parse_attr($match[1]);

        // Check if variable exists
        if (!isset(self::$vars[$attr['name']])) { 
            $html = str_replace($match[0], "", $html);
            debug::add(2, fmsg("Template encountered a e:ection tag without a 'name' attribute.  Could not parse in template, /{1}/{2}", registry::$panel, registry::$route), __FILE__, __LINE__, 'error');
            continue;
        }

        // Debug
        debug::add(5, fmsg("Processing template e:section tag with name '{1}'", $attr['name']), __FILE__, __LINE__);

        // Get replacement HTML
        $replace_html = '';
        foreach (self::$vars[$attr['name']] as $vars) { 
            $temp_html = $match[2];

            // Replace
            foreach ($vars as $key => $value) { 
                $key = $attr['name'] . '.' . $key;
                $temp_html = str_ireplace("~$key~", $value, $temp_html);
            }
            $replace_html .= $temp_html;
        }

        // Replace in HTML
        $html = str_replace($match[0], $replace_html, $html);
    }


    // Return
return $html;

}

/**
* Parses all the <e:theme> tags within the TPL code, 
* and replaces them with the correct contents for the section within 
* the THEME_DIR/sections/ directory.
*/
protected static function process_theme_components() 
{

    // Get Javascript code
    preg_match_all("/<script(.*?)>(.*?)<\/script>/si", self::$tpl_code, $js_match, PREG_SET_ORDER);
    foreach ($js_match as $match) { 
        self::$js_code .= "\n" . $match[2] . "\n";
        self::$tpl_code = str_replace($match[0], "", self::$tpl_code);
    }

    // Go through theme components
    while (preg_match("/<e:theme(.*?)>/si", self::$tpl_code)) {

        preg_match_all("/<e:theme(.*?)>/si", self::$tpl_code, $theme_match, PREG_SET_ORDER);
        foreach ($theme_match as $match) {

            // Parse attributes
            $attr = self::parse_attr($match[1]);

            // Section file
            if (isset($attr['section']) && $attr['section'] != '') { 
                $temp_html = file_exists(self::$theme_dir . '/sections/' . $attr['section']) ? file_get_contents(self::$theme_dir . '/sections/' . $attr['section']) : "<b>ERROR: Theme section file does not exist, $attr[section].</b>";

            } else {
                $temp_html = "<b>ERROR: Invalid theme tag.  No valid attributes found.</b>";

            }
            self::$tpl_code = str_replace($match[0], $temp_html, self::$tpl_code);

        }

    }
}

/**
* Assigns the base variables that are available to all templates, such as the URI 
* to the theme directrory, the ID# of the auhenticated user, and so on.
*/
protected static function load_base_variables() 
{

// Set base variables
    self::assign('theme_uri', self::$theme_uri); 
    self::assign('theme_dir', self::$theme_uri);
    self::assign('rabbitmq_host', RABBITMQ_HOST);
    self::assign('current_year', date('Y'));
    self::assign('config', registry::getall_config());
    self::assign('userid', registry::$userid);

    // Get page title
    self::$page_title = self::get_page_title();

    // Get unread alerts / messages
    if (registry::$userid > 0) { 
        $recipient = 'admin:1';
        list($display_unread_alerts, $display_unread_messages) = array('block', 'block');

        // Alerts
        if (!$unread_alerts = registry::$redis->hget('unread:alerts', $recipient)) { 
            $unread_alerts = '0';
            $display_unread_alerts = 'none';
        }

        // Get unread messages
        if (!$unread_messages = registry::$redis->hget('unread:messages', $recipient)) { 
            $unread_messages = 0;
            $display_unread_messages = 'none';
        }

        // Template variables
        self::assign('unread_alerts', $unread_alerts);
        template::assign('unread_messages', $unread_messages);
        self::assign('display_badge_unread_alerts', $display_unread_alerts);
        self::assign('display_badge_unread_messages', $display_unread_messages);

        // Load profile
        
        if (registry::$panel == 'admin') { $client = new \apex\core\admin(registry::$userid); }
        else { $client = new \apex\users\user(registry::$userid); }
        $profile = $client->load();

        // Go through profile
        self::assign('profile', $profile);
    }




}

/**
* Goes through the self::$vars array, which is populated via the 
* self::assign() method, and replaces all occurences of ~key~ 
* with its corresponding value within the TPL code.
* Fully supports arrays with ~arrayname.key~ merge fields in the TPL code.
*/
protected static function merge_vars(string $html):string 
{

    foreach (self::$vars as $key => $value) {

        if (is_array($value)) { 
            foreach ($value as $skey => $svalue) { 
                if (is_array($svalue)) { continue; }
                $html = str_replace('~' . $key . '.' . $skey . '~', $svalue, $html);
            }

        } else { 
            $html = str_ireplace("~$key~", $value, $html);
        }
    }

    // Return
    return $html;


}

/**
* Retrives the correct HTML code for any other special e: tag.
* Generally goes through the /lib/html_tags.php class, unless a 
* specific method exists for this theme.
*/
public static function get_html_tag(string $tag, array $attr, string $text = ''):string 
{

    // Check for theme specific tag
    if (method_exists(self::$theme_client, $tag)) { 
        return $this->theme_client->$tag($attr, $text);
    }

    // Check if tag exists
    if (!method_exists(self::$html_tags, $tag)) { 
        return "<b>ERROR:</b> The special HTML tag '$tag' is invalid.";
    }

    // Return HTML
    $html = self::$html_tags->$tag($attr, $text);
    return $html;

}

/**
* Simply parses the attributes of any given HTML tag, and returns them in an array.
*/
public static function parse_attr(string $string):array 
{

    // Parse string
    $attributes = array();
    preg_match_all("/(\w+?)\=\"(.*?)\"/", $string, $attribute_match, PREG_SET_ORDER);
    foreach ($attribute_match as $match) { 
        $value = str_replace("\"", "", $match[2]);
        $attributes[$match[1]] = $value;
    }

    // Return
    return $attributes;

}

/**
* Assigns a variable which is then later replaced by the corresponding merge 
* field within the TPL code.  Merge fields are surrounded by tilda marks (eg. ~name~).
( Supports arrays as well, and values can be access via merge fields with ~arrayname.variable~
*/
public static function assign(string $name, $value):bool 
{

    // Check for array
    if (is_array($value)) { 
        foreach ($value as $k => $v) { 
            $value[$k] = $v;
        }
        self::$vars[$name] = $value;
    } else { 
        self::$vars[$name] = (string) $value;
    }

    // Debug
    debug::add(5, fmsg("Assigned template variable {1} to {2}", $name, $value), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Adds a user message, which is then displayed at the top 
* of the template.  This is generally either success or error messager due to 
* the previous processing of an action.
*/
public static function add_message(string $message, string $type = 'success'):bool 
{

    // Add message to needed array
    if (!isset(self::$user_messages[$type])) { self::$user_messages[$type] = array(); }
    array_push(self::$user_messages[$type], $message);
    if ($type == 'error') { self::$has_errors = true; }

    // Return
    return true;
}


/**
* Adds Javascript, which is later included just above the 
* </body> tag upon processing.  Rarely used.
*/
public static function add_javascript(string $js) 
{
    self::$js_code .= "\n$js\n";
}

/**
* Add system Javascript
*/
protected static function add_system_javascript($html)
{

    // Check if Javascript disabled
    if (ENABLE_JAVASCRIPT == 0) { 
        return $html;
    }

    // Get WS auth hash
    if (registry::$userid > 0) { 
        $ws_auth = implode(":", array(registry::$panel, registry::$route, registry::$auth_hash));
    } else { 
        $ws_auth = implode(":", array(registry::$panel, registry::$route, 'public', (time() . rand(0, 99999))));
    }

    // Add WebSocket connection to Javascript
    $js = "\t<script type=\"text/javascript\">\n";
    $js .= "\t\tvar ws_conn = new WebSocket('ws://" . RABBITMQ_HOST . ":8194');\n";
    $js .= "\t\tws_conn.onopen = function(e) {\n"; 
    $js .= "\t\t\tws_conn.send(\"ApexAuth: $ws_auth\");\n";
    $js .= "\t\t}\n";
    $js .= "\t\tws_conn.onmessage = function(e) {\n";
    $js .= "\t\t\tajax_response(e.data);\n";
    $js .= "\t\t}\n";
    $js .= "\t</script>\n\n";

    // Set Apex Javascript
    $js .= "\t" . '<script type="text/javascript" src="/plugins/apex.js"></script>' . "\n";
    $js .= "\t" . '<script src="/plugins/parsley.js/parsley.min.js" type="text/javascript"></script>' . "\n";
    $js .= "\t" . '<script src="https://www.google.com/recaptcha/api.js"></script>' . "\n\n";
    $js .= "</head>\n\n";

    // Add to HTML
    $html = str_replace("</head>", $js, $html);
    $html = str_replace("</body>", base64_decode('CjxkaXYgaWQ9ImFwZXhfbW9kYWwiIGNsYXNzPSJtb2RhbCBmYWRlIiByb2xlPSJkaWFsb2ciPjxkaXYgY2xhc3M9Im1vZGFsLWRpYWxvZyI+Cgk8ZGl2IGNsYXNzPSJtb2RhbC1jb250ZW50Ij4KCgkJPGRpdiBjbGFzcz0ibW9kYWwtaGVhZGVyIj4KCQkJPGJ1dHRvbiB0eXBlPSJidXR0b24iIGNsYXNzPSJjbG9zZSIgZGF0YS1kaXNtaXNzPSJtb2RhbCI+JnRpbWVzOzwvYnV0dG9uPgoJCQk8aDQgY2xhc3M9Im1vZGFsLXRpdGxlIiBpZD0iYXBleF9tb2RhbC10aXRsZSI+PC9oND4KCQk8L2Rpdj4KCQk8ZGl2IGNsYXNzPSJtb2RhbC1ib2R5IiBpZD0iYXBleF9tb2RhbC1ib2R5Ij48L2Rpdj4KCQk8ZGl2IGNsYXNzPSJtb2RhbC1mb290ZXIiPgoJCQk8YnV0dG9uIHR5cGU9ImJ1dHRvbiIgY2xhc3M9ImJ0biBidG4tZGVmYXVsdCIgZGF0YS1kaXNtaXNzPSJtb2RhbCI+Q2xvc2U8L2J1dHRvbj4KCQk8L2Rpdj4KCTwvZGl2Pgo8L2Rpdj48L2Rpdj4KCgo=') . "</body>", $html);

    // Return
    return $html;

}

}

