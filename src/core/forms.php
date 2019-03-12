<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\template;
use apex\ApexException;
use apex\ComponentException;
use apex\FormException;
use apex\core\components;
/**
* Handles various form functionality such as 
* easy server-side validation of form components, 
* obtaining an uploaded file, the value of a checkbox, date interval, etc.
*/
class forms
{

/**
* Validates form fields as needed.  Can either pass any 
* errors to the template engine, or trigger an error displaying the error template.
* 
* if $error_type is 'template', you can check if errors occured via the 
* template:has_errors property.
*
*     @param string $error_type Must be either 'template' or 'error'.
*     @param array $required One dimensional array containg names of form fields that are required, and can not be left blank.
*     @param array $datatypes Associate array specifying which form fields should match which type.
*     @param array $minlength Associate array for any form fields that have a minimum required length.
*     @param array $maxlength Associate array for any form fields that have a maximum required length.
*     @param array $labels Optionaly specify labels for form fields to use within error messages.  Defaults to ucwords() version of field name. 
*/
public static function validate_fields(
    string $error_type = 'template', 
    array $required = array(), 
    array $datatypes = array(), 
    array $minlength = array(), 
    array $maxlength = array(), 
    array $labels = array())
{

    // Debug
    debug::add(4, fmsg("Starting to validate various form fields"), __FILE__, __LINE__);

    // Check required fields
    foreach ($required as $var) { 
        $value = registry::post($var) ?? '';
        if ($value == '') { 
            $label = $labels[$var] ?? ucwords(str_replace("_", " ", $var));

            if ($error_type == 'template') { template::add_message(tr("The form field %s was left blank, and is required", $label), 'error'); }
            else { throw new FormException('field_required', $label); }
        }
    }

    // Check data types
    foreach ($datatypes as $var => $type) { 

        // Set variables
        $errmsg = '';
        $value = registry::post($var) ?? '';
        $label = $labels[$var] ?? ucwords(str_replace("_", " ", $var));

        // Check type
        if ($type == 'alphanum' && !preg_match('/^[a-zA-Z]+[a-zA-Z0-9._]+$/', $value)) {  
            $errmsg = "The form field %s must be alpha-numeric, and can not contain spaces or special characters.";

        } elseif ($type == 'integer' && preg_match("/\D/", $value)) { 
            $errmsg = "The form field %s must be an integer only.";

        } elseif ($type == 'decimal' && !preg_match("/^[0-9]+(\.[0-9]{1,2})?$/", $value)) { 
            $errmsg = "The form field %s can only be a decimal / amount.";

        } elseif ($type == 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) { 
            $errmsg = "The form field %s must be a valid e-mail address.";

        } elseif ($type == 'url' && !filter_var($value, FILTER_VALIDATE_URL)) { 
            $errmsg = "The form field %s must be a valid URL.";
        }

        // Give error if needed
        if ($errmsg != '') { 
            if ($error_type == 'template') { template::add_message(tr($errmsg, $label), 'error'); }
            else { throw new ApexException('error', $errmsg, $label); }
        }
    }

    // Minlength
    foreach ($minlength as $var => $length) { 
        $value = registry::post($var) ?? '';

        if (strlen($value) < $length) { 
            $label = $labels[$var] ?? ucwords(str_replace("_", " ", $var));
            $errmsg = tr("The form field %s must be a minimum of %i characters in length.", $label, $length);

            if ($error_type == 'template') { template::add_message($errmsg, 'error'); }
            else {throw new ApexException('error', $errmsg); }
        }
    }

    // Max lengths
    foreach ($maxlength as $var => $length) { 
        $value = registry::post($var) ?? '';

        // Check
        if (strlen($value) > $langth) { 
            $label = $labels[$var] ?? ucwords(str_replace("_", " ", $var));
            $errmsg = tr("The form field %s can not exceed a maximum of %i characters.", $label, $length);

            if ($error_type == 'template') { template::add_message($errmsg, 'error'); }
            else { throw new ApexException('error', $errmsg); }
        }
    }

    // Debug
    debug::add(4, "Completed validating all various form fields", __FILE__, __LINE__);


}

/**
* Validates a Apex supported 'form' component, using 
* the $form_fields array provided by the component.
* 
*     @param string $form_alias Standard formatted Apex compoent alias of PACKAGE:ALIAS
*     @param string $error_type Must be either 'template' or 'error'.
*     @param array $data Optional array that will be passed to the validate() method of the form component for additional validation.
*/
public static function validate_form(string $form_alias, string $error_type = 'template', array $data = array()):bool 
{

    // Debug
    debug::add(4, fmsg("Starting to validate form component with alias {1}", $form_alias), __FILE__, __LINE__);

    // Check form alias
    if (!list($package, $parent, $alias) = components::check('form', $form_alias)) { 
        throw new ComponentException('not_exists_alias', 'form', $form_alias);
    }

    // Load component
    if (!$form = components::load('form', $alias, $package)) { 
        throw new ComponentException('no_load', 'form', '', $alias, $package);
    }

    // Get fields
    $fields = $form->get_fields($data);

    // Set blank arrays
    $a_required = array();
    $a_datatypes = array();
    $a_minlength = array();
    $a_maxlength = array();
    $a_labels = array();

    // Go through form fields
    foreach ($fields as $name => $vars) { 

        // Set variables
        $required = $vars['required'] ?? 0;
        $type = $vars['datatype'] ?? '';
        $minlength = $vars['minlength'] ?? 0;
        $maxlength = $vars['maxlength'] ?? 0;
        $label = $vars['label'] ?? ucwords(str_replace("_", " ", $name));

        // Add to needed arrays
        if ($required == 1) { $a_required[] = $name; }
        if ($type != '') { $a_datatypes[$name] = $type; }
        if ($minlength > 0) { $a_minlength[$name] = $minlength; }
        if ($maxlength > 0) { $a_maxlength = $maxlength; }
        $a_label = $label;
    }

    // Validate the form
    self::validate_fields($error_type, $a_required, $a_datatypes, $a_minlength, $a_maxlength, $a_labels);

    // Perform any additional form validation
    $form->validate($data);

    // Debug
    debug::add(2, fmsg("Completed validating form component with alias {1}", $form_alias), __FILE__, __LINE__);

    // Return
    $result = template::$has_errors === true ? false : true;
    return $result;

}

/**
* Gets the contents, filename and mime type of an uploaded file.
*
*     @param string $var The form field name of the uploaded file.
*/
public static function get_uploaded_file(string $var) 
{

    // Debug
    debug::add(3, fmsg("Trying to get contents of uploaded file: {1}", $var), __FILE__, __LINE__);

    // Checks
    if (!isset($_FILES[$var])) { return false; }
    if (!isset($_FILES[$var]['tmp_name'])) { return false; }
    if (!is_uploaded_file($_FILES[$var]['tmp_name'])) { return false; }

    // Set variables
    $mime_type = $_FILES[$var]['type'];
    $filename = $_FILES[$var]['name'];
    $contents = fread(fopen($_FILES[$var]['tmp_name'], 'r'), filesize($_FILES[$var]['tmp_name']));

    // Delete tmp file
    @unlink($_FILES[$var]['tmp_name']);

    // Debug
    debug::add(3, fmsg("Returning contents of uploaded file: $var", $var), __FILE__, __LINE__);

    // Return
    return array($filename, $mime_type, $contents);

}

/**
* Get values of a checkbox form field.  Does not give 
* undefined errors if no checkboxes are ticked, and ensures always an array is returned 
* instead of a scalar.
* 
*     @param string $var The form field name of the checkbox field.
*/
public static function get_chk(string $var):array 
{

    // Get values
    if (registry::has_post($var) && is_array(registry::post($var))) { $values = registry::post($var); }
    elseif (registry::has_post($var)) { $values = array(registry::post($var)); }
    else { $values = array(); }

    // Return
    return $values;

}

/**
* Returns the value of the <e:date_interval> tag, which consits of two 
* form fields, the period (days, weeks, months, years), and the length.
* 
*     @param string $name The name of the form field used within the <e:date_interval> tag.
*/
public static function get_date_interval(string $name):string
{

    // Check
    if (!registry::has_post($name . '_period')) { return ''; }
    if (!registry::has_post($name . '_num')) { return ''; }
    if (registry::post($name . '_num') == '') { return ''; }
    if (preg_match("/\D/", (string) registry::post($name . '_num'))) { return ''; }
    if (registry::post($name . '_period') == '') { return ''; }

    // Return
    $interval = registry::post($name . '_period') . registry::post($name . '_num');
    return $interval;

}

}

