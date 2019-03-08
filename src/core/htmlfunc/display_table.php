<?php
declare(strict_types = 1);

namespace apex\core\htmlfunc;

use apex\DB;
use apex\template; 
use apex\registry;
use apex\core\components;
use apex\core\tables;

class display_table extends \apex\abstracts\htmlfunc
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
    if (!isset($data['table'])) { return "<b>ERROR:</b> No 'table' attribute exists within the e:function tag to display a data table."; }

    // Get package / alias
    if (!list($package, $parent, $alias) = components::check('table', $data['table'])) { 
        return "<b>ERROR:</b> The table '$data[table]' either does not exist, or no package was specified and it exists in more than one package.";
    }

    // Load component
    if (!$table = components::load('table', $alias, $package, '', $data)) { 
        return "<B>ERROR:</b> The table component '$data[table] does not exist.";
    }

    // Set variables
    $id = $data['id'] ?? 'tbl_' . str_replace(":", "_", $data['table']);
    $has_search = $table->has_search ?? false;
    $form_field = $table->form_field ?? 'none';
    $form_name = $table->form_name ?? $alias;
    $form_value = $table->form_value ?? 'id';

    // Get AJAX data
    $ajaxdata_vars = $data;
    $ajaxdata_vars['id'] = $id;
    unset($ajaxdata_vars['alias']);
    $ajaxdata = http_build_query($ajaxdata_vars);

    // Execute get_attributes method, if exists
    if (method_exists($table, 'get_attributes')) { 
        $table->get_attributes($data);
    }

    // Get table details
    $details = tables::get_details($table, $id);

    // Get total columns
    $total_columns = count($table->columns);
    if ($form_field == 'radio' || $form_field == 'checkbox') { $total_columns++; }

    // Start data table
    $tpl_code = "<e:data_table id=\"$id\"><thead>\n";

    // Add search bar to TPL code, if needed
    if ($has_search === true) { 
        $tpl_code .= "<tr>\n\t<td colspan=\"$total_columns\" align=\"right\">\n"; 
        $tpl_code .= "\t\t<e:table_search_bar table=\"$data[table]\" id=\"$id\" ajaxdata=\"$ajaxdata\">\n"; 
        $tpl_code .= "\t</td>\n</tr>";
    }

    // Add header column for radio / checkbox
    $tpl_code .= "<tr>\n";
    if ($form_field == 'checkbox') { 
        $tpl_code .= "\t<th><input type=\"checkbox\" name=\"check_all\" value=\"1\" onclick=\"tbl_check_all(this, '$id');\"></th>\n";
        if (!preg_match("/\[\]$/", $form_name)) { $form_name .= '[]'; }
    } elseif ($form_field == 'radio') { 
        $tpl_code .= "\t<th>&nbsp;</th>\n";
    }

    // Add header columns
    foreach ($table->columns as $alias => $name) { 
        if (is_array($table->sortable) && in_array($alias, $table->sortable)) { 
            $sort_asc = "<a href=\"javascript:ajax_send('core/sort_table', '" . $ajaxdata . "&sort_col=" . $alias . "&sort_dir=asc', 'none');\" border=\"0\"><i class=\"fa fa-sort-asc\"></i></a> ";
            $sort_desc = " <a href=\"javascript:ajax_send('core/sort_table', '" . $ajaxdata . "&sort_col=" . $alias . "&sort_dir=desc', 'none');\" border=\"0\"><i class=\"fa fa-sort-desc\"></i></a>";
        } else { list($sort_asc, $sort_desc) = array('', ''); }

        $tpl_code .= "\t<th>" . $sort_asc . $name . $sort_desc . "</th>\n";
    }
    $tpl_code .= "</tr></thead><tbody id=\"" . $id . "_tbody\">\n\n";

    // Go through table rows
    foreach ($details['rows'] as $row) { 
        $tpl_code .= "<tr> `\n";

        // Add form field, if needed
        if ($form_field == 'radio' || $form_field == 'checkbox') { 
            $tpl_code .= "\t<td align=\"center\"><input type=\"$form_field\" name=\"" . $form_name . "\" value=\"" . $row[$form_value] . "\"></td>";
        }

        // Go through columns
        foreach ($table->columns as $alias => $name) { 
            $value = $row[$alias] ?? '';
            $tpl_code .= "\t<td>$value</td>\n";
        }
        $tpl_code .= "</tr>";
    }

    // Finish table
    $tpl_code .= "</tbody><tfoot><tr><td colspan=\"$total_columns\" align=\"right\">\n";

    // Delete button
    if (isset($table->delete_button) && $table->delete_button != '') { 
        $tpl_code .= "\t<a href=\"javascript:ajax_confirm('Are you sure you want to delete the checked records?', 'core/delete_rows', '$ajaxdata', '$form_name');\" class=\"btn btn-primary btn-md\" style=\"float: left;\">$table->delete_button</a>\n\n";
    }

    // Add pagination links
    if ($details['has_pages'] === true) { 
        $tpl_code .= "\t<e:pagination start=\"$details[start]\" page=\"$details[page]\" start_page=\"$details[start_page]\" end_page=\"$details[end_page]\" total=\"$details[total]\" rows_per_page=\"$details[rows_per_page]\" total_pages=\"$details[total_pages]\" id=\"$id\" ajaxdata=\"$ajaxdata\">\n\n";
    }
    $tpl_code .= "</tr></tfoot></e:data_table>\n\n";

    // Return
    return template::parse_html($tpl_code);

}
}

