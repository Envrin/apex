<?php
declare(strict_types = 1);

namespace apex\core\htmlfunc;

use \apex\DB;
use apex\core\lib\template;
use apex\core\lib\registry;
use apex\core\components;

class display_form extends \apex\core\lib\abstracts\htmlfunc
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

    // Get package / alias
    if (!list($package, $parent, $alias) = components::check('form', $data['form'])) { 
        return "<b>ERROR:</b> The form with the alias '$data[form]' either does not exist, or no package was specified and belongs to more than one package.";
    }

    // Set variables
    $width = $data['width'] ?? '';

    // Load component
    if (!$form = components::load('form', $alias, $package, '', $data)) { 
        return "<B>ERROR:</b> Unable to load component of type 'form' with alias $alias from package $package";
    }

    // Get allow post values
    if (isset($data['allow_post_values'])) { $allow_post_values = $data['allow_post_values']; }
    elseif (isset($form::$allow_post_values)) { $allow_post_values = $form::$allow_post_values; }
    else { $allow_post_values = 1; }

    // Get form fields
    $form_fields = $form->get_fields($data);

    // Get values, if needed
    $values = array();
    if (isset($data['record_id']) && $data['record_id'] != '' && method_exists($form, 'get_record')) { 
        $values = $form->get_record($data['record_id']);
    }

    // Start TPL code
    $tpl_code = "<e:form_table";
    if ($width != '') { $tpl_code .= ' style="width: ' . $width . ';"'; }
    $tpl_code .= ">\n";

    // Go through form fields
    foreach ($form_fields as $name => $vars) { 

        // Get value
    if (isset($values[$name])) { $value = $values[$name]; }
        elseif (registry::has_post($name) && $allow_post_values == 1 && $name != 'submit') { $value = registry::post($name); }
        elseif (isset($vars['value'])) { $value = $vars['value']; }
        else { $value = ''; }

        // Get TPL code
        $field_tpl = "<e:ft_" . $vars['field'] . ' name="' . $name . '"';
        if ($vars['field'] != 'textarea') { $field_tpl .= ' value="' . $value . '"'; }
        foreach ($vars as $fkey => $fvalue) { 
            if ($fkey == 'field' || $fkey == 'value' || ($vars['field'] == 'custom' && $fkey == 'contents')) { continue; }
            $field_tpl .= ' ' . $fkey . '="' . $fvalue . '"';
        }
        $field_tpl .= ">";
        if ($vars['field'] == 'custom') { $field_tpl .= $vars['contents'] . "</e:ft_custom>"; }
        if ($vars['field'] == 'textarea') { $field_tpl .= $value . "</e:ft_textarea>"; }
        // Add to TPL code
        $tpl_code .= "\t$field_tpl\n";
    }

    // Return
    $tpl_code .= "</e:form_table>\n\n";
    return $tpl_code;

}

}

