<?php
declare(strict_types = 1);

namespace apex\core\form;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\template;

class admin 
{ 

    public $allow_post_values = 1;

/**
* Defines the form fields included within the HTML form.
* 
*   @param array $data An array of all attributes specified within the e:function tag that called the form. 
*   @return array Keys of the array are the names of the form fields.
*       Values of the array are arrays that specify the attributes 
*       of the form field.  Refer to documentation for details.
*/
public function get_fields(array $data = array()):array
{

    // Set form fields
    $form_fields = array( 
        'sep1' => array('field' => 'seperator', 'label' => 'Login Credentials'), 
        'username' => array('field' => 'textbox', 'label' => 'Username', 'required' => 1, 'datatype' => 'alphanum'), 
        'password' => array('field' => 'textbox', 'type' => 'password', 'label' => 'Desired Password', 'required' => 1), 
        'confirm-password' => array('field' => 'textbox', 'type' => 'password', 'label' => 'Confirm Password', 'placeholder' => 'Confirm Password', 'required' => 1, 'equalto' => '#input_password'), 
        'full_name' => array('field' => 'textbox', 'label' => 'Full Name', 'placeholder' => 'Full Name', 'required' => 1), 
        'email' => array('field' => 'textbox', 'label' => 'E-Mail Address', 'required' => 1, 'datatype' => 'email'),  
        'phone' => array('field' => 'phone'), 
        'sep2' => array('field' => 'seperator', 'label' => 'Additional'), 
        'require_2fa' => array('field' => 'select', 'label' => 'Require E-Mail 2FA?', 'value' => 0, 'data_source' => 'hash:core:2fa_options'), 
        'require_2fa_phone' => array('field' => 'select', 'label' => 'Require Phone 2FA?', 'value' => 0, 'data_source' => 'hash:core:2fa_options'), 
        'language' => array('field' => 'select', 'selected' => 'en', 'required' => 1, 'data_source' => 'stdlist:language:1'), 
        'timezone' => array('field' => 'select', 'selected' => 'PST', 'required' => 1, 'data_source' => 'stdlist:timezone')
    );

    // Security questions
    if (registry::config('core:num_security_questions') > 0) {
        $form_fields['sep3'] = array('field' => 'seperator', 'label' => 'Secondary Security Questions');

        for ($x=1; $x <= registry::config('core:num_security_questions'); $x++) { 
            $form_fields['question' . $x] = array('field' => 'select', 'data_source' => 'hash:core:secondary_security_questions', 'label' => 'Question ' . $x, 'required' => 1);
            $form_fields['answer' . $x] = array('field' => 'textbox', 'label' => 'Answer ' . $x, 'required' => 1);
        }
    }


    // Add submit button
    $record_id = $data['record_id'] ?? 0;
    if ($record_id > 0) {
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'update', 'label' => 'Update Administrator');
    } else { 
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'create', 'label' => 'Create New Administrator');
    }

    // Return
    return $form_fields;

}

/**
* Method is called if a 'record_id' attribute exists within the 
* e:function tag that calls the form.  Will retrieve the values from the 
* database to populate the form fields with.
*
*   @param string $record_id The value of the 'record_id' attribute from the e:function tag.
*   @return array An array of key-value pairs containg the values of the form fields.
*/
public function get_record(string $record_id):array
{ 

    // Get row
    if (!$row = DB::get_idrow('admin', $record_id)) { 
        $row = array(); 
    }
    $row['password'] = '';

    // Return
    return $row;

}

/**
* Allows for additional validation of the submitted form.  
* The standard server-side validation checks are carried out, automatically as 
* designated in the $form_fields defined for this form.  However, this 
* allows additional validation if warranted.
*
*     @param array $data Any array of data passed to the registry::validate_form() method.  Used 
*         to validate based on existing records / rows (eg. duplocate username check, but don't include the current user).
*/
public function validate(array $data = array()) 
{

    // Create admin checks
    if (registry::$action == 'create') { 

        // Check passwords confirm
        if (registry::post('password') != registry::post('confirm-password')) { 
            template::add_message("Passwords do not match.  Please try again.", 'error');
        }

        // Check if username exists
        if ($id = DB::get_field("SELECT id FROM admin WHERE username = %s", registry::post('username'))) { 
            template::add_message(tr("The username already exists for an administrator, %s", registry::post('username')), 'error');
        }
    }


}

}
