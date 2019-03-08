<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\core\hashes;
use apex\core\date;

class html_tags 
{

/**
* Construct
*/
public function __construct()
{
    $this->theme_dir = SITE_PATH . '/themes/' . registry::$theme;
}

/**
* Is replaced with the standard success / error / warning messages on the top of a page contents alertying the 
* user of a successful action being completed, user submission error, etc.
*/
public function user_message(array $messages):string 
{

    $user_message = '';
    $msg_types = array('success','info','error');
    foreach ($msg_types as $type) { 
        if (!isset($messages[$type])) { continue; }
        $css_type = $type == 'error' ? 'danger' : $type;

        // Get icon
        if ($type == 'info') { $icon = 'info'; }
        elseif ($type == 'error') { $icon = 'ban'; }
        else { $icon = 'check'; }

        // Create HTML
        $user_message .= '<div class="callout callout-' . $css_type . ' text-center"><p><i class="icon fa fa-' . $icon . '"></i> ';
        foreach ($messages[$type] as $msg) { 
            if ($msg == '') { continue; }
            $user_message .= "$msg<br />";
        }
        $user_message .= "</p></div>";
    }

    
    // rETURN
        return $user_message;

}

/**
* Page title.  Checks the database first for a defined title, if none exists, checks the TPL code for <h1> tags, and 
* otherwise just uses the site name configuration variable.
*/
public function page_title(array $attr, string $text):string 
{

    // Chwck if textonly
    if (isset($attr['textonly']) && $attr['textonly'] == 1) { return $text; }
    // Format
    return '<h1>' . $text . '</h1>';

}

/**
* Replaced with a standard <form> tab, and unless attributes are defined to the contrary, 
* the action points to the current template being displayed, with a method of POST.
*/ 
public function form($attr, $text = '') 
{

        // Get form action
    if (isset($attr['action'])) { $action = $attr['action']; }
    elseif (registry::$panel == 'public') { $action = registry::$route; }
    else { $action = registry::$panel . '/' . registry::$route; }

    // Set variables
    $action = '/' . trim($action, '/');
    $method = $attr['method'] ?? 'POST';
    $enctype = $attr['enctype'] ?? 'application/x-www-form';
    $class = $attr['class'] ?? 'form-inline';
    $id = $attr['id'] ?? 'frm_main';
    if (isset($attr['file_upload']) && $attr['file_upload'] == 1) { $enctype = 'multipart/form-data'; }

    // Set HTML
    $html = "<form action=\"$action\" method=\"$method\" enctype=\"$enctype\" class=\"$class\" id=\"$id\" data-parsley-validate=\"\">";
    return $html;

}

/**
* Form table.
*/
public function form_table(array $attr, string $text):string 
{

    // Get HTML
    $html = "<table class=\"form_table\"";
    if (isset($attr['width'])) {$html .= " style=\"width: " . $attr['width'] . ";\""; }
    if (isset($attr['align'])) { $html .= " align=\"$attr[align]\""; }
    $html .= ">" . $text . "</table>";

    // Return
    return $html;

}

/**
* Seperator.  Used to separate different groups of form fields.
*/
public function ft_seperator(array $attr, string $text = ''):string 
{

    $html = "<tr><td colspan=\"2\" style=\"padding: 5px 25px;\"><h5>" . tr($attr['label']) . "</h5></td></tr>\n";
    return $html; 

}

/**
* Input textbox form field.
*/
public function ft_textbox(array $attr, string $text = ''):string 
{

// Perform checks
    if (!isset($attr['name'])) { return "<v>ERROR:</b>:  No 'name' attribute within the ft_textbox field."; }

    // Set variables
    $name = $attr['name'];
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $name));	
    $label = tr($label);

    // SGet HTML
    $html = "\n\t<tr><td><label for=\"$name\">" . $label . ":</label></td>\n\t<td>";
    $html .= '<div class="form-group">';

    // Add form field
    $html .= $this->textbox($attr, $text);
    $html .= "</div></td>\n</tr>";	//

    // Return
    return $html;

}

/**
* Amount textbox form field
*/
public function ft_amount(array $attr, string $text = ''):string 
{

// Perform checks
    if (!isset($attr['name'])) { return "<v>ERROR:</b>:  No 'name' attribute within the ft_textbox field."; }

    // Set variables
    $name = $attr['name'];
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $name));	
    $label = tr($label);

    // SGet HTML
    $html = "\n\t<tr><td><label for=\"$name\">" . $label . ":</label></td>\n\t<td>";
    $html .= '<div class="form-group">';

    // Add form field
    $html .= $this->amount($attr, $text);
    $html .= "</div></td>\n</tr>";	//

    // Return
    return $html;

}



/**
* Phone number.  Contains small select list for the country code, then textbox for the actual phone number.
*/
public function ft_phone(array $attr, string $text = ''):string 
{

// Perform checks
    if (!isset($attr['name'])) { return "<v>ERROR:</b>:  No 'name' attribute within the ft_phone field."; }

    // Set variables
    $name = $attr['name'];
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $name));	
    $label = tr($label);

    // SGet HTML
    $html = "\n\t<tr><td><label for=\"$name\">" . $label . ":</label></td>\n\t<td>";
    $html .= '<div class="form-group">';

    // Add form field
    $html .= $this->phone($attr, $text);
    $html .= "</div></td>\n</tr>";	//

    // Return
    return $html;

}

/**
* Textarea
*/
public function ft_textarea(array $attr, string $text = ''):string 
{

// Perform checks
    if (!isset($attr['name'])) { return "<v>ERROR:</b>:  No 'name' attribute within the ft_textbox field."; }

    // Set variables
    $name = $attr['name'];
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $name));	
    $label = tr($label);

    // SGet HTML
    $html = "\n\t<tr><td><label for=\"$name\">" . $label . ":</label></td>\n\t<td>";
    $html .= '<div class="form-group">';

    // Add form field
    $html .= $this->textarea($attr, $text);
    $html .= "></div></td>\n</tr>";	//

    // Return
    return $html;

}

/**
* Date.  Lists three select lists for month, day, and year.
*/
public function ft_date(array $attr, string $text = ''):string 
{


    // Checks
    if (!isset($attr['name'])) { return "<b>ERROR:</b> No 'name' attribute exists within the 'date' tag."; }

    // Set variables
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $attr['name']));
    $label = tr($label);

    // Set HTML
    $html = "<tr><td><label for=\"$attr[name]\">" . $label . ":</label></td><td>";
    $html .= $this->date($attr, $text);
    $html .= "</td></tr>";

    // Return
    return $html;

}

/**
* Date interval.  One small textbox for an integer, and a small select list for the interval (days, weeks, months, years)
*/
public function ft_date_interval(array $attr, string $text = ''):string 
{

    // Checks
    if (!isset($attr['name'])) { return "<b>ERROR:</b> No 'name' attribute exists within the 'date_interval' tag."; }

    // Set variables
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $attr['name']));
    $label = tr($label);

    // Set HTML
    $html = "<tr><td><label for=\"$attr[name]\">" . $label . ":</label></td><td>";
    $html .= $this->date_interval($attr, $text);
    $html .= "</td></tr>";

    // Return
    return $html;

}

/**
* ft_select
*/
public function ft_select(array $attr, string $text = ''):string
{

    // Checks
    if (!isset($attr['name'])) { return "<b>ERROR:</b> No 'name' attribute exists within the 'select' tag."; }
    //if (!isset($attr['data_source'])) { return "<b>ERROR:</b> No 'data_source' attribute exists within the 'select_data' tag."; }

    // Set variables
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $attr['name']));
    $label = tr($label);

    // Set HTML
    $html = "<tr><td><label for=\"$attr[name]\">" . $label . ":</label></td><td>";
    $html .= $this->select($attr, $text);
    $html .= "</td></tr>";

    // Return
    return $html;

}

/**
* ft_boolean
*/
public function ft_boolean(array $attr, string $text = ''):string 
{

    // Perform checks
    if (!isset($attr['name'])) { return "The 'ft_boolean' tag does not contain a 'name' attribute."; }

    // Set variables
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $attr['name']));
    $label = tr($label);

    // SGet HTML
    $html = "\n\t<tr><td><label for=\"$attr[name]\">" . $label . ":</label></td>\n\t<td>";
    $html .= '<div class="form-group">';
    // Add form field
$html .= $this->boolean($attr, $text);
    $html .= "></div></td>\n</tr>";	//

    // Return
return $html;

}

/**
* ft_custom
*/
public function ft_custom(array $attr, string $text = ''):string 
{

    // Perform checks
    if ((!isset($attr['name'])) && (!isset($attr['label']))) { return "<b>ERROR:</b> No 'name' or 'label' attribute was defined with the e:ft_custom tab."; }

    // Set variables
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $attr['name']));
    $name = $attr['name'] ?? strtolower(str_replace(" ", "_", $label));
    if (isset($attr['contents'])) { $text = $attr['contents']; }
    $label = tr($label);

    // Set HTML
    $html = "<tr><td><label for=\"$name\">" . $label . ":</label></td><td>";
    $html .= $text;
    $html .= "</td></tr>";

    // Return
    return $html;

}

/**
* ft_blank
*/
public function ft_blank(array $attr, string $text = ''):string 
{

    // Set html
    $contents = $attr['contents'] ?? $text;
    $html = "<tr><td colspan=\"2\">$contents</td></tr>";

    // Return
    return $html;


}


/**
* ft_submit
*/
public function ft_submit(array $attr, string $text = ''):string
{

    // Set variables
    $value = $attr['value'] ?? 'submit';
    $label = $attr['label'] ?? 'Submit Query';
    $size = $attr['size'] ?? 'lg';
    $has_reset = $attr['has_reset'] ?? 0;
    $align = $attr['align'] ?? 'center'; 
    $label = tr($label);

// Set HTML
$html = "<tr>\n\t<td colspan=\"2\" align=\"$align\">";
    $html .= "<button type=\"submit\" name=\"submit\" value=\"$value\" class=\"btn btn-primary btn-$size\">$label</button>";
    if ($has_reset == 1) { $html .= " <button type=\"reset\">Reset Form</button>"; }
    $html .= "</td>\n</tr>";


    // Return
    return $html;

}

/**
* submit
*/
public function submit(array $attr, string $text = ''):string 
{

    // Set variables
    $name = $attr['name'] ?? 'submit';
    $value = $attr['value'] ?? 'submit';
    $label = $attr['label'] ?? 'Submit Form';
    $label = tr($label);

    // Get HTML
    $html = "<button type=\"submit\" name=\"$name\" value=\"$value\" class=\"btn btn-primary btn-md\">$label</button>";
    return $html;

}

/**
* boolean
*/
public function boolean(array $attr, string $text = ''):string
{

    // Perform checks
    if (!isset($attr['name'])) { return "The 'ft_boolean' tag does not contain a 'name' attribute."; }

    // Set variables
    $value = $attr['value'] ?? 0;
    $chk_yes = $value == 1 ? 'checked="checked"' : '';
    $chk_no = $value == 0 ? 'checked="checked"' : '';

    // Get HTML
    $html = "<input type=\"radio\" name=\"$attr[name]\" class=\"form-control\" value=\"1\" $chk_yes> Yes ";
    $html .= "<input type=\"radio\" name=\"$attr[name]\" class=\"form-control\" value=\"0\" $chk_no> No ";

    // Return
return $html;

}

/**
* select
*/
public function select(array $attr, string $text = ''):string 
{

    // Checks
    if (!isset($attr['name'])) { return "<b>ERROR:</b> No 'name' attribute exists within the 'select' tag."; }
    //if (!isset($attr['data_source'])) { return "<b>ERROR:</b> No 'data_source' attribute exists within the 'select_data' tag."; }

    // Set variables
    $class = $attr['class'] ?? 'form-control';
    $width = $attr['width'] ?? '';
    $value = $attr['value'] ?? '';
    $required = $attr['required'] ?? 0;
    $onchange = $attr['onchange'] ?? '';
    $package = $attr['package'] ?? '';

    // Start HTML
    $html= "<select name=\"$attr[name]\" class=\"$class\"";
    if ($width != '') { $html .= " style=\"width: " . $width . ";\""; }
    if ($onchange != '') { $html .= " onchange=\"$onchange\""; }
    $html .= ">";
    if ($required == 0) { $html .= "<option value=\"\">------------</option>"; }

    // Add select options
    if (isset($attr['data_source'])) { 
        $html .= hashes::parse_data_source($attr['data_source'], $value, 'select', $package);
    } else { $html .= $text; }
    $html .= "</select>";

    // Return
    return $html;

}

/**
* textbox
*/
public function textbox(array $attr, string $text = ''):string
{

// Perform checks
    if (!isset($attr['name'])) { return "<v>ERROR:</b>:  No 'name' attribute within the ft_textbox field."; }

    // Set variables
    $name = $attr['name'];
    $type = $attr['type'] ?? 'text';	
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $name));
    $placeholder = $attr['placeholder'] ?? '';
    $class = $attr['class'] ?? 'form-control';
    $value = $attr['value'] ?? '';
    $width = $attr['width'] ?? '';
    $id = $attr['id'] ?? 'input_' . $name;
    $onfocus = $attr['onfocus'] ?? '';
    $onblur = $attr['onblur'] ?? '';
    $onkeyup = $attr['onkeyup'] ?? '';
    if ($placeholder != '') { $placeholder = tr($placeholder); }

    // Validation variables
    $required = $attr['required'] ?? 0;
    $datatype = $attr['datatype'] ?? '';
    $minlength = $attr['minlength'] ?? 0;
    $maxlength = $attr['maxlength'] ?? 0;
    $range = $attr['range'] ?? '';
    $equalto = $attr['equalto'] ?? '';
 

    // Get HTML
    $html = "<input type=\"$type\" name=\"$name\" class=\"$class\" id=\"$id\"";
    if ($value != '') { $html .= " value =\"$value\""; }
    if ($placeholder != '') { $html .= " placeholder=\"$placeholder\""; }
    if ($width != '') { $html .= " style=\"width: $width; float: left;\""; }
    if ($onfocus != '') { $html .= " onfocus=\"$onfocus\""; }
    if ($onblur != '') { $html .= " onblur=\"$onblur\""; }
    if ($onkeyup != '') { $html .= " onkeyup=\"$onkeyup\""; }

    // Add validation attributes
    if ($required == 1) { $html .= " data-parsley-required=\"true\""; }
    if ($datatype != '') { $html .= " data-parsley-type=\"$datatype\""; }
    if ($minlength > 0) { $html .= " data-parsley-minlength=\"$minlength\""; }
    if ($maxlength > 0) { $html .= " data-parsley-maxlength=\"$maxlength\""; }
    if ($range != '') { $html .= " data-parsley-range=\"$range\""; }
    if ($equalto != '') { $html .= " data-parsley-equalto=\"$equalto\""; }

    // Return
    $html .= ">";
    return $html;

}

/**
* Amount text box
*/
public function amount(array $attr, string $text):string
{

    // Get base currency info
    $curdata = registry::get_currency(registry::config('transaction:base_currency'));

    // Set attributes
    $attr['width'] = '70px';
    $attr['datatype'] = 'decimal';

    // Get HTML
    $html = $curdata['symbol'] . " ";
    $html .= $this->textbox($attr, $text);

    // Return
    return $html;

} 

/**
* phone
*/
public function phone(array $attr, string $text = ''):string 
{

    // Perform checks
    if (!isset($attr['name'])) { return "<b>ERROR:</b> The 'phone' tag does not have a 'name' attribute."; }

    // Set variables
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $attr['name']));
    $value = $attr['value'] ?? '';

    // Check value
    if (preg_match("/\+(\d+?)\s(\d+)$/", $value, $match)) { 
        $country = $match[1];
        $phone = $match[2];
    } else { $country = ''; $phone = ''; }

    // Set HTML
    $name = $attr['name'];
    $html = "+ <input type=\"text\" name=\"" . $name . "_country\" value=\"$country\" class=\"form-control\" style=\"width: 30px;\"> ";
    $html .= "<input type=\"text\" name=\"$name\" value=\"$phone\" class=\"form-control\" style=\"width: 200px;\">";

    // Return
    return $html;



}


/**
* textarea
*/
public function textarea(array $attr, string $text = ''):string 
{

    // Perform checks
    if (!isset($attr['name'])) { return "<v>ERROR:</b>:  No 'name' attribute within the ft_textarea field."; }

    // Set variables
    $name = $attr['name'];
    $label = $attr['label'] ?? ucwords(str_replace("_", " ", $name));
    $placeholder = $attr['placeholder'] ?? '';
    $class = $attr['class'] ?? 'form-control';
    $value = $attr['value'] ?? '';
    $id = $attr['id'] ?? 'input_' . $name;
    if ($placeholder != '') { $placeholder = tr($placeholder); }

    // Get size
    if (isset($attr['size']) && preg_match("/^(.+?),(.+)/", $attr['size'], $match)) { 
        $width = $match[1];
        $height = $match[2];
    } else { $width = ''; $height = ''; }

    // SGet HTML
    $html = "<textarea  name=\"$name\" class=\"$class\" id=\"$id\"";
    if ($placeholder != '') { $html .= " placeholder=\"$placeholder\""; }
    if ($width != '' && $height != '') { $html .= " style=\"width: $width; height: $height;;\""; }
    $html .= ">$value</textarea>";

    // Return
    return $html;

}


/**
* button
*/
public function button(array $attr, string $text = ''):string 
{

    // Set variables
    $href = $attr['href'] ?? '';
    $label = $attr['label'] ?? 'Submit Query';
    $size = $attr['size'] ?? 'lg';
    $label = tr($label);

    // Set HTML
    $html = "<a href=\"$href\" class=\"btn btn-prinary btn-$size\">$label</a>";
    return $html;

}

/**
* Box / panel
*/
public function box(array $attr, string $text = ''):string 
{

    // Check for component
    if (file_exists($this->theme_dir . '/components/box.tpl')) { 
        $html = file_get_contents($this->theme_dir . '/components/box.tpl');
        $html = str_replace("~contents~", $text, $html);

    // Default box
    } else { 
        $html = "<div class=\"panel panel-default\"><div class=\"panel-body\">\n$text\n</div></div>\n";
    }

    // Return
    return $html;

}

/**
* Box / panel header
*/
public function box_header(array $attr, string $text = ''):string 
{

    // Check for component
    if (file_exists($this->theme_dir . '/components/box_header.tpl')) { 
        $html = file_get_contents($this->theme_dir . '/components/box_header.tpl');
        if (isset($attr['title']) && $attr['title'] != '') { 
            $html = str_replace("~title~", $attr['title'], $html);
        }
        $html = str_replace("~contents~", $text, $html);

    // Default
    } else { 
        $html = '<span style="border-bottom: 1px solid #333333; margin-bottom: 8px;">';
        if (isset($attr['title']) && $attr['title'] != '') { 
            $html .= "\t\t<h3>" . tr($attr['title']) . "</h3>\n"; 
        }
        $html .= $text . "\n</span>\n";
    }

    // Return
    return $html;

}
/**
* Data table
*/
public function data_table(array $attr, string $text = ''):string 
{

    // Set variables
    $class = $attr['class'] ?? 'table table-bordered table-striped table-hover';
    $id = $attr['id'] ?? 'data_table';

    // Set HTML
    $html = "<table class=\"$class\" id=\"$id\">\n";
    $html .= $text . "\n";
    $html .= "</table>\n";

    // Return
    return $html;

}

/**
* Table search bar
*/
public function table_search_bar(array $attr, string $text = ''):string 
{

    // Set variables
    $search_id = 'search_' . $attr['id'];
$ajaxdata = $attr['ajaxdata'] ?? '';

    // Set HTML
    $html = "<div class=\"tbl_search_bar\">\n";
    $html .= "\t<i class=\"fa fa-search\"></i> \n";
    $html .= "\t<input type=\"text\" name=\"$search_id\" placeholder=\"" . tr('Search...') . "\" class=\"form-control\" style=\"width: 210px;\"> \n";
    $html .= "\t<a href=\"javascript:ajax_send('core/search_table', '$ajaxdata', '$search_id');\" class=\"btn btn-primary btn-md\">Search</a>\n";
    $html .= "</div>\n\n";

    // Return
    return $html;



}

/**
* Pagination links
*/
public function pagination(array $attr, string $text = ''):string 
{

    // Return if no wors
    if ($attr['total'] == 0) { return ''; }

    // Set variables
    $id = $attr['id'] ?? 'main';
    $page = $attr['page'];
    $total = $attr['total'];
    $rows_per_page = $attr['rows_per_page'];
    $total_pages = ceil($total / $rows_per_page);
    $start = ($page - 1) * $rows_per_page;

    // Get start / end pages
    $pages_left = ceil($total - ($page * $rows_per_page) / $rows_per_page);
    $start_page = ($pages_left > 7 && $page > 7) ? ($page - 7) : 1;
    $end_page = ($pages_left > 7) ? ($page + 15) - $page : $total_pages;

    // Get the href
    if (isset($attr['href']) && $attr['href'] == 'route') { 
        $nav_func = "<a href=\"/" . registry::$route . "?page=~page~\">";
    } else { 
        $ajaxdata = isset($attr['ajaxdata']) ? $attr['ajaxdata'] . '&page=~page~' : 'page=~page~';
        $href = "<a href=\"javascript:ajax_send('core/navigate_table', '$ajaxdata', 'none');\">";
    }

    // Return, if not enough rows
    if ($rows_per_page >= $total) { 
        return ''; 
    }

    // Start HTML
    $html = '<span id="reslbl_' . $id . '" style="vertical-align: middle; font-size: 8pt; margin-right: 7px;"><b>' . ($start + 1) . ' - ' . ($page * $rows_per_page) . '</b> of <b>' . $total . '</b></span>';
    $html .= "<ul class=\"pagination\" id =\"pgn_" . $id . "\">";

    // First page
    $display = $start_page > 1 ? '' : 'none';
    $html .= "<li style=\"display: " . $display . ";\">" . str_replace("~page~", '1', $nav_func) . "&laquo;</a></li>";

    // Previous page
    $display = $page > 1 ? '' : 'none';
    $html .= "<li style=\"display: " . $display . ";\">" . str_replace("~page~", ($page - 1), $nav_func) . "&lt;</a></li>";

    // Go through pages
    $x=1;
    for ($page_num = $start_page; $page_num <= $end_page; $page_num++) {  
        if ($page_num > $total_pages) { break; }

        if ($page_num == $page) { 
            $html .= '<li class="active"><a>' . $page_num . '</a></li>';
        } else {
            $html .= "<li>" . str_replace('~page~', $page_num, $nav_func) . $page_num . "</a></li>"; 
        }
    $x++; }

    // Next page
    $display = $total_pages > $page ? '' : 'none';
    $html .= "<li style=\"display: " . $display . ";\">" . str_replace("~page~", ($page + 1), $nav_func) . "&gt;</a></li>";

    // Last page
    $display = $total_pages > $end_page ? '' : 'none';
    $html .= "<li style=\"display: " . $display . ";\">" . str_replace("~page~", $end_page, $nav_func) . "&raquo;</a></li>";

    // Return
    $html .= '</ul>';
    return $html;

}


/**
* Tab control
*/
public function tab_control($attr, $text)
{

    // Initialize
    $active = $attr['active'] ?? 'active';
    $tab_num = 1;
    $tab_html = '';
    $nav_html = '';

    // Check for components file
    if (file_exists($this->theme_dir . '/components/tab_control.tpl')) { 
        $tag_html = file_get_contents($this->theme_dir . '/components/tab_control.tpl');
    } else { $tag_html = ''; }

    // Get tags
    $tabcontrol_tag = preg_match("/<tab_control>(.*?)<\/tab_control>/si", $tag_html, $match) ? $match[1] : '<div class="nav-tabs-custom"><ul class="nav nav-tabs">~nav_items~</ul><div class="tab-content">~tab_pages~</div></div>';
    $navitem_tag = preg_match("/<nav_item>(.*?)<\/nav_item>/si", $tag_html, $match) ? $match[1] : '<li class="~active~"><a href="#tab~tab_num~" data-toggle="tab">~name~</a></li>';
    $page_tag = preg_match("/<tab_page>(.*?)<\/tab_page>/si", $tag_html, $match) ? $match[1] : '<div class="tab-pane ~active~" id="tab~tab_num~">~contents~</div>';


    // Go through tab pages
    preg_match_all("/<e:tab_page(.*?)>(.*?)<\/e:tab_page>/si", $text, $tab_match, PREG_SET_ORDER);
    foreach ($tab_match as $tab) { 

        // Get name
        $name = preg_match("/name=\"(.+?)\"/", $tab[1], $name_match) ? $name_match[1] : 'Unknown Tab';

        // Add nav item
        $navitem = $navitem_tag;
        $navitem = str_replace("~tab_num~", $tab_num, $navitem);
        $navitem = str_replace("~active~", $active, $navitem);
        $navitem = str_replace("~name~", tr($name), $navitem);
        $nav_html .= $navitem;

        // Add tab page contents
        $page = $page_tag;
        $page = str_replace("~tab_num~", $tab_num, $page);
        $page = str_replace("~active~", $active, $page);
        $page = str_replace("~contents~", $tab[2], $page);
        $tab_html .= $page;

        // Update vars
        $tab_num++; $active = ''; 
    }

    // Finish HTML
    $html = $tabcontrol_tag;
    $html = str_replace("~nav_items~", $nav_html, $html);
    $html = str_replace("~tab_pages~", $tab_html, $html);

    // Return
    return $html;

}


/**
* Boxlist
*/
public function boxlist(array $attr, string $text = ''):string 
{

    // Start html
    list($package, $alias) = explode(":", $attr['alias'], 2);
    $html = "<ul class=\"boxlist\">\n";

    // Go through list items
    $result = DB::query("SELECT * FROM internal_boxlists WHERE package = %s AND alias = %s ORDER BY order_num", $package, $alias);
    while ($row = DB::fetch_assoc($result)) { 
        $url = '/' . trim($row['href'], '/');
        $html .= "\t<li><p><a href=\"$url\"><b>" . tr($row['title']) . "</b><br />" . tr($row['description']) . "</p></li>\n";
    }

    // Return
    $html .= "</ul>\n";
    return $html;

}


/**
* date
*/
public function date(array $attr, string $text = ''):string 
{

    // Initialize
    global $config;
    if (!isset($attr['name'])) { return "<b>ERROR:</b> No 'name' attribute within the e:date tab."; }

    // Set variables
    $name = $attr['name'];
    $required = $attr['required'] ?? 0;
    $start_year = $attr['start_year'] ?? $config['start_year'];
    $end_year = $attr['end_year'] ?? (date('Y') + 3);
    $value = $attr['value'] ?? '';
    if ($required == 1 && $value == '') { $value = date('Y-m-d'); }

    // Parse value
    if (preg_match("/(\d\d\d\d)-(\d\d)-(\d\d)/", $value, $match)) { 
        list($year, $month, $day) = explode("-", $value);
    } else { list($year, $month, $day) = array(0, 0, 0); }

    // Month HTML
    $html = "<select name=\"" . $name . "_month\">";
    if ($required == 0) { $html .= "<option value=\"0\">----------</option>"; }
    for ($x = 1; $x <= 12; $x++) {
        $chk = $x == $month ? 'selected="selected"' : '';
        $html .= "<option value=\"$x\" $chk>" . tr(date('F', mktime(0, 0, 0, $x, 1, 2000)));
    }
    $html .= "</select> ";

    // Day options
    $html .= "<select name=\"" . $name . "_day\">";
    if ($required == 0) { $html .= "<option value=\"0\">----</option>"; }
    for ($x = 1; $x <= 31; $x++) { 
        $chk = $x == $day ? 'selected="selected"' : '';
        $html .= "<option value=\"$x\" $chk>$x</option>";
    }
    $html .= "</select>, ";

    // Year options
    $html .= "<select name=\"" . $name . "_year\">";
    if ($required == 0) { $html .= "<option value=\"0\">-----</option>"; }
    for ($x = $start_year; $x <= $end_year; $x++) {
        $chk = $x == $year ? 'selected="selected"' : '';
        $html .= "<option value=\"$x\" $chk>$x</option>";
    }
    $html .= "</select>";

    // Return
    return $html;

}

/**
* date interval
*/
public function date_interval(array $attr, string $text = ''):string 
{

    // Checks
    if (!isset($attr['name'])) { return "<b>ERROR:</b> The 'date_interval' tag does not have a 'name' attribute."; }

    // Set variables
    $name = $attr['name'];
    $add_time = $attr['add_time'] ?? 0;
    $value = $attr['value'] ?? '';

    // Get value
    if (preg_match("/^(\w)(\d+)$/", $value, $match)) { 
        $period = $match[1]; $num = $match[2];
    } else { $period = ''; $num = ''; }

    // Get periods
    $periods = $add_time == 1 ? array('I' => tr('Minutes'), 'H' => tr('Hours')) : array();
    $periods['D'] = tr('Days');
    $periods['W'] = tr('Weeks');
    $periods['M'] = tr('Months');
    $periods['Y'] = tr('Years');

    // Get HTML
    $html = "<input type=\"text\" name=\"" . $name . "_num\" class=\"form-control\" value=\"$num\" style=\"width: 30px;\"> ";
    $html .= "<select name=\"" . $name . "_period\" class=\"form-control\" style=\"width: 80px;\">";
    foreach ($periods as $abbr => $name) { 
        $chk = $abbr == $period ? 'selected="selected"' : '';
        $html .= "<option value=\"$abbr\" $chk>$name</option>";
    }
    $html .= "</select>";

    // Return
    return $html; 

}

/**
* Placeholder
*/
public function placeholder($attr, $text)
{

    // Check alias
    if (!isset($attr['alias'])) { return ''; }
    
    // Check redis
    $key = registry::$panel . '/' . registry::$route . ':' . $attr['alias'];
    if (!$value = registry::$redis->hget('cms:placeholders', $key)) { 
        $value = '';
    }

    // Return
    return $value;

}

/**
* Fooflw ewXpatcha
*/
public function recaptcha($attr, $text)
{

    // Check if enabled
    if (registry::config('core:recaptcha_site_key') == '') { return ''; }

    $html = "<div class=\"g-recaptcha\" data-sitekey=\"" . registry::config('core:recaptcha_site_key') . "\"></div>\n";

    // Return
    return $html;

}

/**
* Dropdown list of all unread notifications
*/
public function dropdown_alerts($attr, $text) 
{

    // Get HTML of dropdown item
    $comp_file = SITE_PATH . '/themes/' . registry::$theme . '/components/dropdown_alert.tpl';
    if (file_exists($comp_file)) { 
        $tag_html = file_get_contents($comp_file);
    } else { $tag_html = '<li><a href="~url~"><p>~message~<br /><i style="font-size: small">~time~</i><br /></p></a></li>'; }

    // Set variables
    $recipient = (registry::$panel == 'admin' ? 'admin:' : 'user:') . registry::$userid;
    $redis_key = 'alerts:' . $recipient;
    registry::$redis->ltrim($redis_key, 0, 9);

    // Go through alerts
    $html = '';
    $rows = registry::$redis->lrange($redis_key, 0, -1);
    foreach ($rows as $data) { 
        $row = json_decode($data, true);
        $tmp_html = $tag_html;

        // Merge variables
        $tmp_html = str_replace("~url~", $row['url'], $tmp_html);
        $tmp_html = str_replace("~message~", $row['message'], $tmp_html);
        $tmp_html = str_replace("~time~", date::last_seen($row['time']), $tmp_html);
        $html .= $tmp_html;
    }

    // Return
    return $html;

}

/**
* Dropdown list of messages
*/
public function dropdown_messages($attr, $text)
{

    // Get HTML of dropdown item
    $comp_file = SITE_PATH . '/themes/' . registry::$theme . '/components/dropdown_message.tpl';
    if (file_exists($comp_file)) { 
        $tag_html = file_get_contents($comp_file);
    } else { $tag_html = '<li><a href="~url~"><p><b>~from~</b><br />~message~<br /><i style="font-size: small">~time~</i><br /></p></a></li>'; }

    // Set variables
    $recipient = (registry::$panel == 'admin' ? 'admin:' : 'user:') . registry::$userid;
    $redis_key = 'messages:' . $recipient;
    registry::$redis->ltrim($redis_key, 0, 9);

    // Go through alerts
    $html = '';
    $rows = registry::$redis->lrange($redis_key, 0, -1);
    foreach ($rows as $data) { 
        $row = json_decode($data, true);
        $tmp_html = $tag_html;

        // Merge variables
        $tmp_html = str_replace("~from~", $row['from'], $tmp_html);
        $tmp_html = str_replace("~url~", $row['url'], $tmp_html);
        $tmp_html = str_replace("~message~", $row['message'], $tmp_html);
        $tmp_html = str_replace("~time~", date::last_seen($row['time']), $tmp_html);
        $html .= $tmp_html;
    }

    // Return
    return $html;


}

}

