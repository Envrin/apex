<?php
declare(strict_types = 1);

namespace apex\core\htmlfunc;

use apex\DB;
use apex\template;
use apex\registry;
use apex\core\components;

class display_autosuggest extends \apex\abstracts\htmlfunc
{

/**
* Replaces the calling <e:function> tag with the resulting 
* auto-suggest / complete box.
* 
*   @param string $html The contains of the TPL file, if exists, located at /views/htmlfunc/<package>/<alias>.tpl
*   @param array $data The attributes within the calling e:function> tag.
*   @return string The resulting HTML code, which the <e:function> tag within the template is replaced with.
*/
public function process(string $html, array $data = array()):string
{

    // Perform checks
    if (!isset($data['autosuggest'])) { return "<b>ERROR:</b> No autosuggest attribute exists within the e:function tag to display a auto suggest box."; }
    if (!isset($data['name'])) { return "<b>ERROR:</b> No 'name' attribute exists within the e:function tag to display a auto suggest box."; }

    // Get package and alias
    if (!list($package, $parent, $alias) = components::check('autosuggest', $data['autosuggest'])) { 
        return "<b>ERROR:</b> The autosuggest with the alias '$data[autosuggest]' either does not exist, or exists within more than one package and the package was not specifically defined within the HTML tag.";
    }

    // Set variables
    $name = $data['name'];
    $idfield = $data['idfield'] ?? $name . '_id';
    $width = $data['width'] ?? '';
    $placeholder = $data['placeholder'] ?? '';

    // Set HTML
    $html = "<input type=\"hidden\" name=\"$idfield\" value=\"\" id=\"$idfield\" />\n"; 
    $html .= "<input type=\"text\" name=\"$name\" id=\"$name\" ";
    if ($placeholder != '') { $html .= "placeholder=\"$placeholder\" "; }
    if ($width != '') { $html .= "style=\"width: $width;\" "; }
    $html .= "/>\n";

    // Get Javascript
    $js = "\t\t\$( \"#" . $name . "\" ).autocomplete({ \n";
    $js .= "\t\t\tminlength: 2, \n";
    $js .= "\t\t\tsource: \"/ajax/core/search_autosuggest?autosuggest=$data[autosuggest]\", \n";
    $js .= "\t\t\tselect: function(event, ui) { \$(\"#" . $idfield . "\").val(ui.item.data); }\n";
    $js .= "\t\t});\n";

    // Add Javascript to template
    template::add_javascript($js);

    // Return
    return $html;

}

}

